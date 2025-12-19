<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Project::with(['creator', 'users']);

        // Admin sees all projects
        if (!$user->isAdmin()) {
            // Others see only their projects
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->withCount('tasks')
            ->latest()
            ->paginate($request->per_page ?? 15);

        // Append progress attribute
        $projects->getCollection()->transform(function ($project) {
            $project->progress = $project->progress;
            $project->completed_tasks_count = $project->completed_tasks_count;
            return $project;
        });

        return response()->json($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['planning', 'active', 'on_hold', 'completed', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'incharges' => ['sometimes', 'array'],
            'incharges.*' => ['exists:users,id'],
            'members' => ['sometimes', 'array'],
            'members.*' => ['exists:users,id'],
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'planning',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'created_by' => $user->id,
        ]);

        // Attach incharges
        if (!empty($validated['incharges'])) {
            foreach ($validated['incharges'] as $inchargeId) {
                $project->users()->attach($inchargeId, ['role' => 'incharge']);
            }
        }

        // Attach members
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $memberId) {
                $project->users()->attach($memberId, ['role' => 'member']);
            }
        }

        return response()->json([
            'message' => 'Project created successfully.',
            'project' => $project->load(['creator', 'users']),
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $project->load([
            'creator',
            'users',
            'tasks' => function ($query) {
                $query->with(['assignees', 'creator'])->latest();
            }
        ]);

        $project->progress = $project->progress;
        $project->completed_tasks_count = $project->completed_tasks_count;
        $project->total_tasks_count = $project->total_tasks_count;

        return response()->json($project);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['planning', 'active', 'on_hold', 'completed', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated successfully.',
            'project' => $project->fresh()->load(['creator', 'users']),
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }

    /**
     * Update project members.
     */
    public function updateMembers(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'incharges' => ['sometimes', 'array'],
            'incharges.*' => ['exists:users,id'],
            'members' => ['sometimes', 'array'],
            'members.*' => ['exists:users,id'],
        ]);

        // Sync incharges and members
        $syncData = [];

        if (isset($validated['incharges'])) {
            foreach ($validated['incharges'] as $inchargeId) {
                $syncData[$inchargeId] = ['role' => 'incharge'];
            }
        }

        if (isset($validated['members'])) {
            foreach ($validated['members'] as $memberId) {
                $syncData[$memberId] = ['role' => 'member'];
            }
        }

        $project->users()->sync($syncData);

        return response()->json([
            'message' => 'Project members updated successfully.',
            'project' => $project->fresh()->load(['creator', 'users']),
        ]);
    }

    /**
     * Get project statistics.
     */
    public function statistics(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $tasks = $project->tasks;

        $stats = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $tasks->where('status', 'pending')->count(),
            'under_review_tasks' => $tasks->where('status', 'under_review')->count(),
            'cancelled_tasks' => $tasks->where('status', 'cancelled')->count(),
            'overdue_tasks' => $tasks->filter(fn($t) => $t->is_overdue)->count(),
            'progress' => $project->progress,
            'priority_breakdown' => [
                'low' => $tasks->where('priority', 'low')->count(),
                'medium' => $tasks->where('priority', 'medium')->count(),
                'high' => $tasks->where('priority', 'high')->count(),
                'urgent' => $tasks->where('priority', 'urgent')->count(),
            ],
        ];

        return response()->json($stats);
    }
}

