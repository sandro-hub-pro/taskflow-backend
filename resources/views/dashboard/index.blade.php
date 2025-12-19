@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span class="text-slate-900 dark:text-white font-medium">Dashboard</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 lg:p-8">
        <div class="absolute inset-0 bg-mesh opacity-30"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-white">
                <h1 class="text-2xl lg:text-3xl font-bold mb-2">
                    Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->first_name }}! 👋
                </h1>
                <p class="text-white/80">Here's what's happening with your tasks today.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right text-white hidden sm:block">
                    <p class="text-sm text-white/70">Today is</p>
                    <p class="text-lg font-semibold">{{ now()->format('F j, Y') }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                    <i data-lucide="calendar" class="w-8 h-8 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Projects -->
        <x-ui.card hover class="group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Projects</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['total_projects'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl group-hover:scale-110 transition-transform">
                    <i data-lucide="folder" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm">
                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                    {{ $stats['active_projects'] ?? 0 }}
                </span>
                <span class="text-slate-500 dark:text-slate-400">active</span>
            </div>
        </x-ui.card>
        
        <!-- My Tasks -->
        <x-ui.card hover class="group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">My Tasks</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['my_tasks'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl group-hover:scale-110 transition-transform">
                    <i data-lucide="check-square" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm">
                <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    {{ $stats['pending_tasks'] ?? 0 }}
                </span>
                <span class="text-slate-500 dark:text-slate-400">pending</span>
            </div>
        </x-ui.card>
        
        <!-- Completed Tasks -->
        <x-ui.card hover class="group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Completed</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['completed_tasks'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl group-hover:scale-110 transition-transform">
                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="mt-4">
                <x-ui.progress-bar :value="$stats['completion_rate'] ?? 0" size="sm" color="success" />
            </div>
        </x-ui.card>
        
        <!-- Overdue Tasks -->
        <x-ui.card hover class="group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Overdue</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['overdue_tasks'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-xl group-hover:scale-110 transition-transform">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm">
                @if(($stats['overdue_tasks'] ?? 0) > 0)
                    <span class="text-red-600 dark:text-red-400">Needs attention</span>
                @else
                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        All caught up!
                    </span>
                @endif
            </div>
        </x-ui.card>
    </div>
    
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Tasks -->
        <div class="lg:col-span-2">
            <x-ui.card :padding="false">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Tasks</h2>
                    <a href="{{ route('tasks.my') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        View all
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                
                @if(isset($recentTasks) && $recentTasks->count() > 0)
                    <div class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($recentTasks as $task)
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 mt-1">
                                        @if($task->status === 'completed')
                                            <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center">
                                                <i data-lucide="check" class="w-3 h-3 text-white"></i>
                                            </div>
                                        @else
                                            <div class="w-5 h-5 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <a href="{{ route('tasks.show', ['project' => $task->project_id, 'task' => $task->id]) }}" 
                                               class="font-medium text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate">
                                                {{ $task->title }}
                                            </a>
                                            <x-ui.badge :variant="$task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'default'))" size="xs">
                                                {{ ucfirst($task->priority) }}
                                            </x-ui.badge>
                                        </div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                            <span class="truncate">{{ $task->project->name ?? 'No Project' }}</span>
                                            @if($task->due_date)
                                                <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-500' : '' }}">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    {{ $task->due_date->format('M j') }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="flex-shrink-0 w-16">
                                        <x-ui.progress-bar :value="$task->progress" size="sm" />
                                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center mt-1">{{ $task->progress }}%</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state 
                        icon="check-square" 
                        title="No tasks yet"
                        description="You don't have any tasks assigned. Check back later!"
                    />
                @endif
            </x-ui.card>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            @if(auth()->user()->isAdmin() || auth()->user()->isIncharge())
            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('projects.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <i data-lucide="folder-plus" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">Create Project</span>
                    </a>
                    @endif
                    <a href="{{ route('tasks.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <i data-lucide="plus-square" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">Create Task</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.create') }}" 
                       class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <i data-lucide="user-plus" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">Add User</span>
                    </a>
                    @endif
                </div>
            </x-ui.card>
            @endif
            
            <!-- Project Overview -->
            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">My Projects</h3>
                @if(isset($myProjects) && $myProjects->count() > 0)
                    <div class="space-y-3">
                        @foreach($myProjects->take(5) as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block p-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-slate-900 dark:text-white truncate">{{ $project->name }}</span>
                                    <x-ui.badge :variant="$project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'default')" size="xs">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </x-ui.badge>
                                </div>
                                <x-ui.progress-bar :value="$project->progress" size="sm" />
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $project->completed_tasks_count }}/{{ $project->total_tasks_count }} tasks</p>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('projects.index') }}" class="mt-4 block text-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                        View all projects
                    </a>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">No projects assigned yet.</p>
                @endif
            </x-ui.card>
            
            <!-- Team Members (Admin Only) -->
            @if(auth()->user()->isAdmin())
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Team Overview</h3>
                    <a href="{{ route('users.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <i data-lucide="shield" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <span class="text-slate-700 dark:text-slate-300">Admins</span>
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $stats['admins_count'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                <i data-lucide="user-cog" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <span class="text-slate-700 dark:text-slate-300">Incharges</span>
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $stats['incharges_count'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                <i data-lucide="users" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                            </div>
                            <span class="text-slate-700 dark:text-slate-300">Users</span>
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $stats['users_count'] ?? 0 }}</span>
                    </div>
                </div>
            </x-ui.card>
            @endif
        </div>
    </div>
</div>
@endsection

