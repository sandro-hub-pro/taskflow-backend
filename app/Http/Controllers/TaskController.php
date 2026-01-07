<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for a project.
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = $project->tasks()->with(['creator', 'assignees', 'assigner', 'accepter']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assignee
        if ($request->has('assignee_id')) {
            $query->whereHas('assignees', function ($q) use ($request) {
                $q->where('user_id', $request->assignee_id);
            });
        }

        $tasks = $query->latest()->paginate($request->per_page ?? 15);

        // Transform tasks to include calculated progress and acceptance info
        $tasks->getCollection()->transform(function ($task) use ($user) {
            $taskArray = $task->toArray();
            $taskArray['overall_progress'] = $task->calculated_progress;
            $taskArray['is_accepted'] = $task->is_accepted;
            
            // Include user's individual progress if they are an assignee
            $userAssignment = $task->assignees->firstWhere('id', $user->id);
            if ($userAssignment) {
                $taskArray['my_progress'] = $userAssignment->pivot->progress ?? 0;
            }
            
            return $taskArray;
        });

        return response()->json($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        // Only admin or project incharge can create tasks
        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed', 'cancelled'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => ['nullable', 'date'],
            'assignees' => ['sometimes', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        // Validate assignees are project members
        if (!empty($validated['assignees'])) {
            $projectUserIds = $project->users()->pluck('users.id')->toArray();
            foreach ($validated['assignees'] as $assigneeId) {
                if (!in_array($assigneeId, $projectUserIds)) {
                    return response()->json([
                        'message' => 'All assignees must be project members.',
                    ], 422);
                }
            }
        }

        $task = Task::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => $user->id,
            'assigned_by' => !empty($validated['assignees']) ? $user->id : null,
        ]);

        // Attach assignees
        if (!empty($validated['assignees'])) {
            foreach ($validated['assignees'] as $assigneeId) {
                $task->assignees()->attach($assigneeId, ['assigned_by' => $user->id]);
            }
        }

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task->load(['creator', 'assignees', 'assigner']),
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, Project $project, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        $task->load(['creator', 'assignees', 'assigner', 'accepter', 'comments.user', 'project']);

        $response = $task->toArray();
        $response['overall_progress'] = $task->calculated_progress;
        $response['is_accepted'] = $task->is_accepted;
        
        // Include user's individual progress if they are an assignee
        $userAssignment = $task->assignees->firstWhere('id', $user->id);
        if ($userAssignment) {
            $response['my_progress'] = $userAssignment->pivot->progress ?? 0;
        }

        return response()->json($response);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Project $project, Task $task): JsonResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        // Admin or project incharge can update all fields
        $canFullEdit = $user->isAdmin() || $user->isProjectIncharge($project->id);
        
        // Regular users can only update progress and status of their assigned tasks
        $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();

        if (!$canFullEdit && !$isAssignee) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Prevent progress updates on accepted tasks (for non-admin/non-incharge users)
        if ($task->is_accepted && !$canFullEdit) {
            return response()->json([
                'message' => 'This task has been accepted and can no longer be modified.',
            ], 403);
        }

        $rules = [];

        if ($canFullEdit) {
            $rules = [
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed', 'cancelled'])],
                'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
                'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
                'due_date' => ['nullable', 'date'],
            ];
        } else {
            // Assignees can only update progress and status
            $rules = [
                'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed'])],
                'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            ];
        }

        $validated = $request->validate($rules);

        // Handle individual user progress update for assignees
        if (isset($validated['progress']) && $isAssignee) {
            // Update the user's individual progress in the pivot table
            $task->assignees()->updateExistingPivot($user->id, [
                'progress' => $validated['progress'],
            ]);
            
            // Recalculate and store the overall progress
            $task->refresh();
            $overallProgress = $task->calculated_progress;
            $validated['progress'] = $overallProgress;
        }

        // Auto-update progress when status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === 'completed') {
                // When marked complete, set all assignees to 100%
                if ($canFullEdit) {
                    $task->assignees()->each(function ($assignee) use ($task) {
                        $task->assignees()->updateExistingPivot($assignee->id, ['progress' => 100]);
                    });
                }
                $validated['progress'] = 100;
            } elseif ($validated['status'] === 'pending' && !isset($validated['progress'])) {
                $validated['progress'] = 0;
            }
        }

        $task->update($validated);

        $freshTask = $task->fresh()->load(['creator', 'assignees', 'assigner', 'accepter']);
        
        // Add the user's individual progress and overall progress to the response
        $response = $freshTask->toArray();
        $response['overall_progress'] = $freshTask->calculated_progress;
        $response['is_accepted'] = $freshTask->is_accepted;
        if ($isAssignee) {
            $userAssignment = $freshTask->assignees->firstWhere('id', $user->id);
            $response['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
        }

        return response()->json([
            'message' => 'Task updated successfully.',
            'task' => $response,
        ]);
    }

    /**
     * Accept a completed task (only for admin/incharge).
     */
    public function accept(Request $request, Project $project, Task $task): JsonResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        // Only admin or project incharge can accept tasks
        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Task must be completed to be accepted
        if ($task->status !== 'completed') {
            return response()->json([
                'message' => 'Only completed tasks can be accepted.',
            ], 422);
        }

        // Task must not already be accepted
        if ($task->is_accepted) {
            return response()->json([
                'message' => 'This task has already been accepted.',
            ], 422);
        }

        $task->update([
            'accepted_at' => now(),
            'accepted_by' => $user->id,
        ]);

        $freshTask = $task->fresh()->load(['creator', 'assignees', 'assigner', 'accepter']);
        
        $response = $freshTask->toArray();
        $response['overall_progress'] = $freshTask->calculated_progress;
        $response['is_accepted'] = true;

        return response()->json([
            'message' => 'Task accepted successfully.',
            'task' => $response,
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, Project $project, Task $task): JsonResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        // Only admin or project incharge can delete tasks
        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * Assign users to a task.
     */
    public function assignUsers(Request $request, Project $project, Task $task): JsonResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        // Only admin or project incharge can assign users
        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'assignees' => ['required', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        // Validate assignees are project members
        $projectUserIds = $project->users()->pluck('users.id')->toArray();
        foreach ($validated['assignees'] as $assigneeId) {
            if (!in_array($assigneeId, $projectUserIds)) {
                return response()->json([
                    'message' => 'All assignees must be project members.',
                ], 422);
            }
        }

        // Sync assignees
        $syncData = [];
        foreach ($validated['assignees'] as $assigneeId) {
            $syncData[$assigneeId] = ['assigned_by' => $user->id];
        }
        $task->assignees()->sync($syncData);

        $task->update(['assigned_by' => $user->id]);

        return response()->json([
            'message' => 'Users assigned successfully.',
            'task' => $task->fresh()->load(['creator', 'assignees', 'assigner']),
        ]);
    }

    /**
     * Get tasks assigned to the authenticated user.
     */
    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Task::whereHas('assignees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['project', 'creator', 'assignees', 'assigner', 'accepter']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->latest()->paginate($request->per_page ?? 15);

        // Transform tasks to include calculated progress and user's individual progress
        $tasks->getCollection()->transform(function ($task) use ($user) {
            $taskArray = $task->toArray();
            $taskArray['overall_progress'] = $task->calculated_progress;
            $taskArray['is_accepted'] = $task->is_accepted;
            
            $userAssignment = $task->assignees->firstWhere('id', $user->id);
            $taskArray['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
            
            return $taskArray;
        });

        return response()->json($tasks);
    }

    /**
     * Add a comment to a task.
     */
    public function addComment(Request $request, Project $project, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Request $request, Project $project, Task $task, TaskComment $comment): JsonResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task not found in this project.'], 404);
        }

        if ($comment->task_id !== $task->id) {
            return response()->json(['message' => 'Comment not found for this task.'], 404);
        }

        // Only comment owner or admin can delete
        if (!$user->isAdmin() && $comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}

