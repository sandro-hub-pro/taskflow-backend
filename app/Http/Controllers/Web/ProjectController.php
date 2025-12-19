<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Project::with(['creator', 'users', 'tasks']);

        // Admin sees all projects
        if (!$user->isAdmin()) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->withCount('tasks')->latest()->paginate(12);

        // Add computed attributes
        $projects->getCollection()->transform(function ($project) {
            $project->progress = $project->progress;
            $project->completed_tasks_count = $project->completed_tasks_count;
            return $project;
        });

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $users = User::all();
        return view('projects.create', compact('users'));
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): RedirectResponse
    {
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
            'created_by' => auth()->id(),
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

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, Project $project): View
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isProjectMember($project->id)) {
            abort(403, 'You are not a member of this project.');
        }

        $project->load([
            'creator',
            'users',
            'incharges',
            'members',
            'tasks' => function ($query) {
                $query->with(['assignees', 'creator'])->latest();
            }
        ]);

        $project->progress = $project->progress;
        $project->completed_tasks_count = $project->completed_tasks_count;
        $project->total_tasks_count = $project->total_tasks_count;

        $tasks = $project->tasks;
        $stats = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $tasks->where('status', 'pending')->count(),
            'under_review_tasks' => $tasks->where('status', 'under_review')->count(),
            'overdue_tasks' => $tasks->filter(fn($t) => $t->is_overdue)->count(),
        ];

        return view('projects.show', compact('project', 'stats'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['planning', 'active', 'on_hold', 'completed', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    /**
     * Show the members management form.
     */
    public function members(Project $project): View
    {
        $project->load(['incharges', 'members']);
        $users = User::all();

        return view('projects.members', compact('project', 'users'));
    }

    /**
     * Update project members.
     */
    public function updateMembers(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'incharges' => ['sometimes', 'array'],
            'incharges.*' => ['exists:users,id'],
            'members' => ['sometimes', 'array'],
            'members.*' => ['exists:users,id'],
        ]);

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

        return redirect()->route('projects.show', $project)->with('success', 'Team members updated successfully.');
    }
}

