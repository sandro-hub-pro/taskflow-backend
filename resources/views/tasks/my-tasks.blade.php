@extends('layouts.app')

@section('title', 'My Tasks')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">My Tasks</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Tasks</h1>
            <p class="text-slate-500 dark:text-slate-400">Track and update your assigned tasks</p>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="{{ route('tasks.my') }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors {{ !request('status') ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">All Tasks</div>
        </a>
        <a href="{{ route('tasks.my', ['status' => 'pending']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-600 transition-colors {{ request('status') === 'pending' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="text-2xl font-bold text-slate-600 dark:text-slate-400">{{ $stats['pending'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Pending</div>
        </a>
        <a href="{{ route('tasks.my', ['status' => 'in_progress']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 transition-colors {{ request('status') === 'in_progress' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['in_progress'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">In Progress</div>
        </a>
        <a href="{{ route('tasks.my', ['status' => 'completed']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors {{ request('status') === 'completed' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['completed'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Completed</div>
        </a>
        <a href="{{ route('tasks.my', ['overdue' => 1]) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-red-300 dark:hover:border-red-700 transition-colors {{ request('overdue') ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Overdue</div>
        </a>
    </div>
    
    <!-- Filters -->
    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('tasks.my') }}" class="p-4 flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search tasks..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                </div>
            </div>
            
            <div class="flex gap-2 flex-wrap">
                <select name="priority" onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                
                <select name="project_id" onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
                
                <x-ui.button type="submit" variant="secondary" icon="filter">
                    Filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
    
    <!-- Tasks List -->
    <x-ui.card :padding="false">
        @if($tasks->count() > 0)
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($tasks as $task)
                    <div class="p-4 lg:p-6 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <!-- Status & Task Info -->
                            <div class="flex items-start gap-4 flex-1">
                                <div class="flex-shrink-0">
                                    @if($task->status === 'completed')
                                        <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                                            <i data-lucide="check" class="w-5 h-5 text-white"></i>
                                        </div>
                                    @elseif($task->status === 'in_progress')
                                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                                            <i data-lucide="play" class="w-4 h-4 text-white"></i>
                                        </div>
                                    @elseif($task->is_overdue)
                                        <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 text-white"></i>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                                    @endif
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <a href="{{ route('tasks.show', ['project' => $task->project_id, 'task' => $task->id]) }}" 
                                           class="text-lg font-semibold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $task->title }}
                                        </a>
                                        @php
                                            $priorityVariants = [
                                                'low' => 'success',
                                                'medium' => 'info',
                                                'high' => 'warning',
                                                'urgent' => 'danger'
                                            ];
                                        @endphp
                                        <x-ui.badge :variant="$priorityVariants[$task->priority] ?? 'default'" size="sm">
                                            {{ ucfirst($task->priority) }}
                                        </x-ui.badge>
                                        @if($task->is_overdue)
                                            <x-ui.badge variant="danger" size="sm">Overdue</x-ui.badge>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                                        <a href="{{ route('projects.show', $task->project) }}" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            <i data-lucide="folder" class="w-4 h-4"></i>
                                            {{ $task->project->name }}
                                        </a>
                                        @if($task->due_date)
                                            <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-500' : '' }}">
                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                                {{ $task->due_date->format('M j, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress -->
                            <div class="lg:w-48">
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-slate-500 dark:text-slate-400">Progress</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $task->progress }}%</span>
                                </div>
                                <x-ui.progress-bar :value="$task->progress" size="md" />
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <x-ui.button href="{{ route('tasks.show', ['project' => $task->project_id, 'task' => $task->id]) }}" variant="ghost" size="sm" icon="eye">
                                    View
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $tasks->links() }}
            </div>
        @else
            <x-ui.empty-state 
                icon="check-square" 
                title="No tasks found"
                description="You don't have any tasks matching the current filters."
            />
        @endif
    </x-ui.card>
</div>
@endsection

