<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display user's assigned tasks.
     */
    public function myTasks(Request $request): View
    {
        $user = $request->user();

        $query = Task::whereHas('assignees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['project', 'assignees']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter overdue
        if ($request->boolean('overdue')) {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now());
        }

        $tasks = $query->latest()->paginate(15);

        // User's projects for filter
        $projects = $user->projects;

        // Stats
        $baseQuery = Task::whereHas('assignees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        $stats = [
            'total' => $baseQuery->count(),
            'pending' => $baseQuery->clone()->where('status', 'pending')->count(),
            'in_progress' => $baseQuery->clone()->where('status', 'in_progress')->count(),
            'completed' => $baseQuery->clone()->where('status', 'completed')->count(),
            'overdue' => $baseQuery->clone()
                ->where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('tasks.my-tasks', compact('tasks', 'projects', 'stats'));
    }

    /**
     * Show task create form (requires selecting project).
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Get projects where user is admin or incharge
        if ($user->isAdmin()) {
            $projects = Project::all();
        } else {
            $projects = $user->projects()
                ->wherePivot('role', 'incharge')
                ->get();
        }

        return view('tasks.create', compact('projects'));
    }

    /**
     * Show task create form for a specific project.
     */
    public function createForProject(Request $request, Project $project): View
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to create tasks in this project.');
        }

        $project->load('users');

        return view('tasks.create', compact('project'));
    }

    /**
     * Store a new task.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed', 'cancelled'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => ['nullable', 'date'],
            'assignees' => ['sometimes', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to create tasks in this project.');
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

        return redirect()->route('tasks.show', ['project' => $project->id, 'task' => $task->id])
            ->with('success', 'Task created successfully.');
    }

    /**
     * Store a new task for a specific project.
     */
    public function storeForProject(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to create tasks in this project.');
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
                    return back()->withErrors(['assignees' => 'All assignees must be project members.']);
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

        return redirect()->route('tasks.show', ['project' => $project->id, 'task' => $task->id])
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, Project $project, Task $task): View
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            abort(403, 'You are not a member of this project.');
        }

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        $task->load(['creator', 'assignees', 'assigner', 'comments.user', 'project']);

        $canUpdateProgress = $user->isAdmin() || 
                            $user->isProjectIncharge($project->id) || 
                            $task->assignees->contains($user->id);

        return view('tasks.show', compact('project', 'task', 'canUpdateProgress'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Request $request, Project $project, Task $task): View
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to edit this task.');
        }

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        return view('tasks.edit', compact('project', 'task'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Project $project, Task $task): RedirectResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to update this task.');
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed', 'cancelled'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'due_date' => ['nullable', 'date'],
        ]);

        // Auto-update progress when status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === 'completed') {
                $validated['progress'] = 100;
            } elseif ($validated['status'] === 'pending' && !isset($validated['progress'])) {
                $validated['progress'] = 0;
            }
        }

        $task->update($validated);

        return redirect()->route('tasks.show', ['project' => $project->id, 'task' => $task->id])
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Update task progress (for assignees).
     */
    public function updateProgress(Request $request, Project $project, Task $task): RedirectResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        $canFullEdit = $user->isAdmin() || $user->isProjectIncharge($project->id);
        $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();

        if (!$canFullEdit && !$isAssignee) {
            abort(403, 'You are not authorized to update this task.');
        }

        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'under_review', 'completed'])],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
        ]);

        // Auto-update progress when status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === 'completed') {
                $validated['progress'] = 100;
            } elseif ($validated['status'] === 'pending' && !isset($validated['progress'])) {
                $validated['progress'] = 0;
            }
        }

        $task->update($validated);

        return back()->with('success', 'Progress updated successfully.');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, Project $project, Task $task): RedirectResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to delete this task.');
        }

        $task->delete();

        return redirect()->route('projects.show', $project)->with('success', 'Task deleted successfully.');
    }

    /**
     * Show the assignment form.
     */
    public function assign(Request $request, Project $project, Task $task): View
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to assign users to this task.');
        }

        $project->load('users');
        $task->load('assignees');

        return view('tasks.assign', compact('project', 'task'));
    }

    /**
     * Update task assignees.
     */
    public function updateAssignees(Request $request, Project $project, Task $task): RedirectResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        if (!$user->isAdmin() && !$user->isProjectIncharge($project->id)) {
            abort(403, 'You are not authorized to assign users to this task.');
        }

        $validated = $request->validate([
            'assignees' => ['sometimes', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        // Validate assignees are project members
        $projectUserIds = $project->users()->pluck('users.id')->toArray();
        $assignees = $validated['assignees'] ?? [];
        
        foreach ($assignees as $assigneeId) {
            if (!in_array($assigneeId, $projectUserIds)) {
                return back()->withErrors(['assignees' => 'All assignees must be project members.']);
            }
        }

        // Sync assignees
        $syncData = [];
        foreach ($assignees as $assigneeId) {
            $syncData[$assigneeId] = ['assigned_by' => $user->id];
        }
        $task->assignees()->sync($syncData);

        $task->update(['assigned_by' => $user->id]);

        return redirect()->route('tasks.show', ['project' => $project->id, 'task' => $task->id])
            ->with('success', 'Assignees updated successfully.');
    }

    /**
     * Store a task comment.
     */
    public function storeComment(Request $request, Project $project, Task $task): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            abort(403, 'You are not a member of this project.');
        }

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Delete a task comment.
     */
    public function destroyComment(Request $request, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        $user = $request->user();

        if ($task->project_id !== $project->id) {
            abort(404, 'Task not found in this project.');
        }

        if ($comment->task_id !== $task->id) {
            abort(404, 'Comment not found for this task.');
        }

        if (!$user->isAdmin() && $comment->user_id !== $user->id) {
            abort(403, 'You are not authorized to delete this comment.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }
}

