<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get user's projects
        if ($user->isAdmin()) {
            $myProjects = Project::with(['users', 'tasks'])->latest()->take(10)->get();
            $totalProjects = Project::count();
            $activeProjects = Project::where('status', 'active')->count();
        } else {
            $myProjects = $user->projects()->with(['users', 'tasks'])->latest()->take(10)->get();
            $totalProjects = $user->projects()->count();
            $activeProjects = $user->projects()->where('status', 'active')->count();
        }

        // Add computed attributes to projects
        $myProjects = $myProjects->map(function ($project) {
            $project->progress = $project->progress;
            $project->completed_tasks_count = $project->completed_tasks_count;
            $project->total_tasks_count = $project->total_tasks_count;
            return $project;
        });

        // Get user's tasks
        $myTasksQuery = Task::whereHas('assignees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        $recentTasks = $myTasksQuery->clone()
            ->with(['project', 'assignees'])
            ->latest()
            ->take(10)
            ->get();

        $myTasksCount = $myTasksQuery->count();
        $pendingTasks = $myTasksQuery->clone()->where('status', 'pending')->count();
        $completedTasks = $myTasksQuery->clone()->where('status', 'completed')->count();
        $overdueTasks = $myTasksQuery->clone()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $completionRate = $myTasksCount > 0 ? round(($completedTasks / $myTasksCount) * 100) : 0;

        // Admin stats
        $adminsCount = null;
        $inchargesCount = null;
        $usersCount = null;

        if ($user->isAdmin()) {
            $adminsCount = User::where('role', 'admin')->count();
            $inchargesCount = User::where('role', 'incharge')->count();
            $usersCount = User::where('role', 'user')->count();
        }

        $stats = [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'my_tasks' => $myTasksCount,
            'pending_tasks' => $pendingTasks,
            'completed_tasks' => $completedTasks,
            'overdue_tasks' => $overdueTasks,
            'completion_rate' => $completionRate,
            'admins_count' => $adminsCount,
            'incharges_count' => $inchargesCount,
            'users_count' => $usersCount,
        ];

        return view('dashboard.index', compact('stats', 'recentTasks', 'myProjects'));
    }
}

