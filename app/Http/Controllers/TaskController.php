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
            $status = $request->status;
            if ($status === 'overdue') {
                // Overdue: due_date < today AND status is not completed/cancelled
                $query->whereNotNull('due_date')
                    ->where('due_date', '<', now()->startOfDay())
                    ->whereNotIn('status', ['completed', 'cancelled']);
            } elseif ($status === 'accepted') {
                // Accepted: accepted_at is not null
                $query->whereNotNull('accepted_at');
            } else {
                $query->where('status', $status);
            }
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

        // Sort by status order: pending, in_progress, under_review, completed, cancelled
        $query->orderByRaw("CASE 
            WHEN status = 'pending' THEN 1 
            WHEN status = 'in_progress' THEN 2 
            WHEN status = 'under_review' THEN 3 
            WHEN status = 'completed' THEN 4 
            WHEN status = 'cancelled' THEN 5 
            ELSE 6 END");

        $tasks = $query->paginate($request->per_page ?? 15);

        // Transform tasks to include calculated progress/status and acceptance info
        $tasks->getCollection()->transform(function ($task) use ($user) {
            $taskArray = $task->toArray();
            $taskArray['overall_progress'] = $task->calculated_progress;
            $taskArray['overall_status'] = $task->calculated_status;
            $taskArray['is_accepted'] = $task->is_accepted;
            
            // Include user's individual progress/status if they are an assignee
            $userAssignment = $task->assignees->firstWhere('id', $user->id);
            if ($userAssignment) {
                $taskArray['my_progress'] = $userAssignment->pivot->progress ?? 0;
                $taskArray['my_status'] = $userAssignment->pivot->status ?? 'pending';
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
        $response['overall_status'] = $task->calculated_status;
        $response['is_accepted'] = $task->is_accepted;
        
        // Include user's individual progress/status if they are an assignee
        $userAssignment = $task->assignees->firstWhere('id', $user->id);
        if ($userAssignment) {
            $response['my_progress'] = $userAssignment->pivot->progress ?? 0;
            $response['my_status'] = $userAssignment->pivot->status ?? 'pending';
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
            // Assignees can update their own progress AND their own status
            $rules = [
                'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
                'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed'])],
            ];
        }

        $validated = $request->validate($rules);

        // Handle individual user updates for assignees (not managers)
        if ($isAssignee && !$canFullEdit) {
            $pivotUpdate = [];
            
            // Update individual progress
            if (isset($validated['progress'])) {
                $pivotUpdate['progress'] = $validated['progress'];
                
                // Get current user's status
                $currentStatus = $task->assignees()->where('user_id', $user->id)->first()?->pivot?->status ?? 'pending';
                
                // Auto-update individual status based on progress
                if ($validated['progress'] >= 100) {
                    $pivotUpdate['status'] = 'completed';
                } elseif ($validated['progress'] > 0) {
                    // Change to in_progress if currently pending OR completed (going back)
                    if ($currentStatus === 'pending' || $currentStatus === 'completed') {
                        $pivotUpdate['status'] = 'in_progress';
                    }
                } elseif ($validated['progress'] == 0) {
                    // Reset to pending if progress is 0
                    $pivotUpdate['status'] = 'pending';
                }
            }
            
            // Update individual status (if explicitly set, override auto-status)
            if (isset($validated['status'])) {
                $pivotUpdate['status'] = $validated['status'];
            }
            
            if (!empty($pivotUpdate)) {
                $task->assignees()->updateExistingPivot($user->id, $pivotUpdate);
            }
            
            // Recalculate overall progress and status from all assignees
            $task->refresh();
            $validated['progress'] = $task->calculated_progress;
            $validated['status'] = $task->calculated_status;
        }

        // Handle manager/admin updates
        if ($canFullEdit) {
            // Handle individual user progress/status update if also an assignee
            if ($isAssignee) {
                $pivotUpdate = [];
                if (isset($validated['progress'])) {
                    $pivotUpdate['progress'] = $validated['progress'];
                    
                    // Get current user's status
                    $currentStatus = $task->assignees()->where('user_id', $user->id)->first()?->pivot?->status ?? 'pending';
                    
                    // Auto-update individual status based on progress
                    if ($validated['progress'] >= 100) {
                        $pivotUpdate['status'] = 'completed';
                    } elseif ($validated['progress'] > 0) {
                        // Change to in_progress if currently pending OR completed (going back)
                        if ($currentStatus === 'pending' || $currentStatus === 'completed') {
                            $pivotUpdate['status'] = 'in_progress';
                        }
                    } elseif ($validated['progress'] == 0) {
                        // Reset to pending if progress is 0
                        $pivotUpdate['status'] = 'pending';
                    }
                }
                if (isset($validated['status'])) {
                    $pivotUpdate['status'] = $validated['status'];
                }
                if (!empty($pivotUpdate)) {
                    $task->assignees()->updateExistingPivot($user->id, $pivotUpdate);
                    $task->refresh();
                    $validated['progress'] = $task->calculated_progress;
                }
            }
            
            // When manager sets task status to completed, set all assignees to completed
            if (isset($validated['status']) && $validated['status'] === 'completed') {
                $task->assignees()->each(function ($assignee) use ($task) {
                    $task->assignees()->updateExistingPivot($assignee->id, [
                        'progress' => 100,
                        'status' => 'completed',
                    ]);
                });
                $validated['progress'] = 100;
            }
        }

        $task->update($validated);

        $freshTask = $task->fresh()->load(['creator', 'assignees', 'assigner', 'accepter']);
        
        // Add the user's individual progress/status and overall progress/status to the response
        $response = $freshTask->toArray();
        $response['overall_progress'] = $freshTask->calculated_progress;
        $response['overall_status'] = $freshTask->calculated_status;
        $response['is_accepted'] = $freshTask->is_accepted;
        if ($isAssignee) {
            $userAssignment = $freshTask->assignees->firstWhere('id', $user->id);
            $response['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
            $response['my_status'] = $userAssignment ? $userAssignment->pivot->status : 'pending';
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
            $status = $request->status;
            if ($status === 'overdue') {
                // Overdue: due_date < today AND status is not completed/cancelled
                $query->whereNotNull('due_date')
                    ->where('due_date', '<', now()->startOfDay())
                    ->whereNotIn('status', ['completed', 'cancelled']);
            } elseif ($status === 'accepted') {
                // Accepted: accepted_at is not null
                $query->whereNotNull('accepted_at');
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Sort by status order: pending, in_progress, under_review, completed, cancelled
        $query->orderByRaw("CASE 
            WHEN status = 'pending' THEN 1 
            WHEN status = 'in_progress' THEN 2 
            WHEN status = 'under_review' THEN 3 
            WHEN status = 'completed' THEN 4 
            WHEN status = 'cancelled' THEN 5 
            ELSE 6 END");

        $tasks = $query->paginate($request->per_page ?? 15);

        // Transform tasks to include calculated progress/status and user's individual progress/status
        $tasks->getCollection()->transform(function ($task) use ($user) {
            $taskArray = $task->toArray();
            $taskArray['overall_progress'] = $task->calculated_progress;
            $taskArray['overall_status'] = $task->calculated_status;
            $taskArray['is_accepted'] = $task->is_accepted;
            
            $userAssignment = $task->assignees->firstWhere('id', $user->id);
            $taskArray['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
            $taskArray['my_status'] = $userAssignment ? $userAssignment->pivot->status : 'pending';
            
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

