@extends('layouts.app')

@section('title', $project->name)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Projects</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">{{ Str::limit($project->name, 20) }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Project Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 lg:p-8">
        <div class="absolute inset-0 bg-mesh opacity-30"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        
        <div class="relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <i data-lucide="folder" class="w-8 h-8 text-white"></i>
                    </div>
                    <div class="text-white">
                        <h1 class="text-2xl lg:text-3xl font-bold">{{ $project->name }}</h1>
                        @if($project->description)
                            <p class="text-white/80 mt-1">{{ Str::limit($project->description, 100) }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    @php
                        $statusVariants = [
                            'planning' => 'default',
                            'active' => 'success',
                            'on_hold' => 'warning',
                            'completed' => 'info',
                            'cancelled' => 'danger'
                        ];
                    @endphp
                    <x-ui.badge :variant="$statusVariants[$project->status] ?? 'default'" size="lg" :dot="true">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </x-ui.badge>
                    
                    @if(auth()->user()->isAdmin())
                        <x-ui.button href="{{ route('projects.edit', $project) }}" variant="secondary" icon="edit-2" size="sm">
                            Edit
                        </x-ui.button>
                    @endif
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                <div class="flex items-center justify-between text-white mb-2">
                    <span class="text-sm font-medium">Overall Progress</span>
                    <span class="text-2xl font-bold">{{ $project->progress }}%</span>
                </div>
                <div class="h-3 rounded-full bg-white/30 overflow-hidden">
                    <div class="h-full rounded-full bg-white transition-all duration-500" style="width: {{ $project->progress }}%"></div>
                </div>
                <div class="flex items-center justify-between mt-2 text-sm text-white/80">
                    <span>{{ $project->completed_tasks_count }} of {{ $project->total_tasks_count }} tasks completed</span>
                    @if($project->end_date)
                        <span class="flex items-center gap-1">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            Due {{ $project->end_date->format('M j, Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $project->total_tasks_count }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Total Tasks</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['in_progress_tasks'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">In Progress</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $project->completed_tasks_count }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Completed</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue_tasks'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Overdue</div>
        </x-ui.card>
    </div>
    
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tasks Section -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Tasks</h2>
                @if(auth()->user()->isAdmin() || auth()->user()->isProjectIncharge($project->id))
                    <x-ui.button href="{{ route('projects.tasks.create', $project) }}" variant="primary" icon="plus" size="sm">
                        Add Task
                    </x-ui.button>
                @endif
            </div>
            
            <x-ui.card :padding="false">
                @if($project->tasks->count() > 0)
                    <div class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($project->tasks as $task)
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <!-- Status Indicator -->
                                    <div class="flex-shrink-0 mt-1">
                                        @if($task->status === 'completed')
                                            <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                                                <i data-lucide="check" class="w-4 h-4 text-white"></i>
                                            </div>
                                        @elseif($task->status === 'in_progress')
                                            <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                                                <i data-lucide="play" class="w-3 h-3 text-white"></i>
                                            </div>
                                        @else
                                            <div class="w-6 h-6 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                                        @endif
                                    </div>
                                    
                                    <!-- Task Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('tasks.show', ['project' => $project->id, 'task' => $task->id]) }}" 
                                               class="font-medium text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
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
                                            <x-ui.badge :variant="$priorityVariants[$task->priority] ?? 'default'" size="xs">
                                                {{ ucfirst($task->priority) }}
                                            </x-ui.badge>
                                        </div>
                                        
                                        <div class="flex items-center gap-4 mt-2 text-sm text-slate-500 dark:text-slate-400">
                                            @if($task->due_date)
                                                <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-500' : '' }}">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                    {{ $task->due_date->format('M j, Y') }}
                                                </span>
                                            @endif
                                            
                                            @if($task->assignees->count() > 0)
                                                <div class="flex items-center gap-1">
                                                    <div class="flex -space-x-2">
                                                        @foreach($task->assignees->take(3) as $assignee)
                                                            <x-ui.avatar :name="$assignee->full_name" :src="$assignee->profile_picture ? Storage::url($assignee->profile_picture) : null" size="xs" />
                                                        @endforeach
                                                    </div>
                                                    @if($task->assignees->count() > 3)
                                                        <span class="text-xs">+{{ $task->assignees->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Progress -->
                                    <div class="flex-shrink-0 w-20 text-right">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $task->progress }}%</div>
                                        <x-ui.progress-bar :value="$task->progress" size="sm" class="mt-1" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state 
                        icon="check-square" 
                        title="No tasks yet"
                        description="Create your first task for this project."
                        :action="(auth()->user()->isAdmin() || auth()->user()->isProjectIncharge($project->id)) ? route('projects.tasks.create', $project) : null"
                        actionLabel="Add Task"
                        actionIcon="plus"
                    />
                @endif
            </x-ui.card>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Project Details -->
            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Details</h3>
                <dl class="space-y-4">
                    @if($project->start_date)
                        <div>
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Start Date</dt>
                            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $project->start_date->format('F j, Y') }}</dd>
                        </div>
                    @endif
                    
                    @if($project->end_date)
                        <div>
                            <dt class="text-sm text-slate-500 dark:text-slate-400">Due Date</dt>
                            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $project->end_date->format('F j, Y') }}</dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Created by</dt>
                        <dd class="mt-1 flex items-center gap-2">
                            <x-ui.avatar :name="$project->creator->full_name" :src="$project->creator->profile_picture ? Storage::url($project->creator->profile_picture) : null" size="sm" />
                            <span class="font-medium text-slate-900 dark:text-white">{{ $project->creator->full_name }}</span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Created</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $project->created_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </x-ui.card>
            
            <!-- Team Members -->
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Team</h3>
                    @if(auth()->user()->isAdmin())
                        <x-ui.button href="{{ route('projects.members', $project) }}" variant="ghost" size="sm" icon="user-plus">
                            Manage
                        </x-ui.button>
                    @endif
                </div>
                
                @if($project->incharges->count() > 0)
                    <div class="mb-4">
                        <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Incharges</h4>
                        <div class="space-y-2">
                            @foreach($project->incharges as $incharge)
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-50 dark:bg-slate-800">
                                    <x-ui.avatar :name="$incharge->full_name" :src="$incharge->profile_picture ? Storage::url($incharge->profile_picture) : null" size="sm" status="online" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $incharge->full_name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $incharge->email }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if($project->members->count() > 0)
                    <div>
                        <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Members</h4>
                        <div class="space-y-2">
                            @foreach($project->members as $member)
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-50 dark:bg-slate-800">
                                    <x-ui.avatar :name="$member->full_name" :src="$member->profile_picture ? Storage::url($member->profile_picture) : null" size="sm" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $member->full_name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $member->email }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if($project->users->count() === 0)
                    <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">No team members assigned.</p>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
@endsection

