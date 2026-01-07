<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isIncharge()) {
            return $this->inchargeDashboard($user);
        }

        return $this->userDashboard($user);
    }

    /**
     * Admin dashboard statistics.
     */
    private function adminDashboard(): JsonResponse
    {
        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalTasks = Task::count();

        $projectsByStatus = Project::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $tasksByStatus = Task::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $tasksByPriority = Task::selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $recentProjects = Project::with('creator')
            ->latest()
            ->take(5)
            ->get();

        $recentTasks = Task::with(['project', 'assignees'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($task) {
                $taskArray = $task->toArray();
                $taskArray['overall_progress'] = $task->calculated_progress;
                return $taskArray;
            });

        $usersByRole = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        return response()->json([
            'total_users' => $totalUsers,
            'total_projects' => $totalProjects,
            'total_tasks' => $totalTasks,
            'projects_by_status' => $projectsByStatus,
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
            'recent_projects' => $recentProjects,
            'recent_tasks' => $recentTasks,
            'users_by_role' => $usersByRole,
        ]);
    }

    /**
     * Incharge dashboard statistics.
     */
    private function inchargeDashboard(User $user): JsonResponse
    {
        $projectIds = $user->projects()->pluck('projects.id');

        $totalProjects = $projectIds->count();
        $totalTasks = Task::whereIn('project_id', $projectIds)->count();

        $tasksByStatus = Task::whereIn('project_id', $projectIds)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $tasksByPriority = Task::whereIn('project_id', $projectIds)
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $myProjects = $user->projects()
            ->with('creator')
            ->withCount('tasks')
            ->latest()
            ->take(5)
            ->get();

        $recentTasks = Task::whereIn('project_id', $projectIds)
            ->with(['project', 'assignees'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($task) {
                $taskArray = $task->toArray();
                $taskArray['overall_progress'] = $task->calculated_progress;
                return $taskArray;
            });

        $overdueTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['project', 'assignees'])
            ->get()
            ->map(function ($task) {
                $taskArray = $task->toArray();
                $taskArray['overall_progress'] = $task->calculated_progress;
                return $taskArray;
            });

        return response()->json([
            'total_projects' => $totalProjects,
            'total_tasks' => $totalTasks,
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
            'my_projects' => $myProjects,
            'recent_tasks' => $recentTasks,
            'overdue_tasks' => $overdueTasks,
        ]);
    }

    /**
     * User dashboard statistics.
     */
    private function userDashboard(User $user): JsonResponse
    {
        $assignedTasks = $user->assignedTasks()->with(['project', 'creator'])->get();

        $tasksByStatus = $assignedTasks->groupBy('status')->map->count();
        $tasksByPriority = $assignedTasks->groupBy('priority')->map->count();

        $myProjects = $user->projects()
            ->with('creator')
            ->withCount('tasks')
            ->get();

        $upcomingTasks = $user->assignedTasks()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->with(['project', 'assignees'])
            ->take(5)
            ->get()
            ->map(function ($task) use ($user) {
                $taskArray = $task->toArray();
                $taskArray['overall_progress'] = $task->calculated_progress;
                $userAssignment = $task->assignees->firstWhere('id', $user->id);
                $taskArray['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
                return $taskArray;
            });

        $overdueTasks = $user->assignedTasks()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['project', 'assignees'])
            ->get()
            ->map(function ($task) use ($user) {
                $taskArray = $task->toArray();
                $taskArray['overall_progress'] = $task->calculated_progress;
                $userAssignment = $task->assignees->firstWhere('id', $user->id);
                $taskArray['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
                return $taskArray;
            });

        $recentTasks = $user->assignedTasks()
            ->with(['project', 'assignees'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($task) use ($user) {
                $taskArray = $task->toArray();
                $taskArray['overall_progress'] = $task->calculated_progress;
                $userAssignment = $task->assignees->firstWhere('id', $user->id);
                $taskArray['my_progress'] = $userAssignment ? $userAssignment->pivot->progress : 0;
                return $taskArray;
            });

        return response()->json([
            'total_assigned_tasks' => $assignedTasks->count(),
            'completed_tasks' => $assignedTasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $assignedTasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $assignedTasks->where('status', 'pending')->count(),
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
            'my_projects' => $myProjects,
            'upcoming_tasks' => $upcomingTasks,
            'overdue_tasks' => $overdueTasks,
            'recent_tasks' => $recentTasks,
        ]);
    }
}

