@extends('layouts.app')

@section('title', $user->full_name)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('users.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Users</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">{{ $user->first_name }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Profile Header -->
    <x-ui.card>
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                @if($user->profile_picture)
                    <img src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->full_name }}" class="w-24 h-24 rounded-2xl object-cover">
                @else
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            
            <!-- Info -->
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->full_name }}</h1>
                    @php
                        $roleVariants = [
                            'admin' => 'primary',
                            'incharge' => 'secondary',
                            'user' => 'default'
                        ];
                    @endphp
                    <x-ui.badge :variant="$roleVariants[$user->role] ?? 'default'" :dot="true">
                        {{ ucfirst($user->role) }}
                    </x-ui.badge>
                </div>
                <p class="text-slate-500 dark:text-slate-400">@<span class="font-mono">{{ $user->username }}</span></p>
                <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $user->email }}</p>
                
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 mt-4 text-sm text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        Joined {{ $user->created_at->format('M j, Y') }}
                    </span>
                    @if($user->email_verified_at)
                        <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Email Verified
                        </span>
                    @else
                        <span class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            Email Not Verified
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                <x-ui.button href="{{ route('users.edit', $user) }}" variant="secondary" icon="edit-2" size="sm">
                    Edit
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $user->projects->count() }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Projects</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $user->assignedTasks->count() }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Assigned Tasks</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $user->assignedTasks->where('status', 'completed')->count() }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Completed</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $user->assignedTasks->whereNotIn('status', ['completed', 'cancelled'])->count() }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">In Progress</div>
        </x-ui.card>
    </div>
    
    <!-- Projects & Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Projects -->
        <x-ui.card :padding="false">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Projects</h2>
            </div>
            @if($user->projects->count() > 0)
                <div class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($user->projects->take(5) as $project)
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-3 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <i data-lucide="folder" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-white truncate">{{ $project->name }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ ucfirst($project->pivot->role) }}</p>
                            </div>
                            <x-ui.badge :variant="$project->status === 'active' ? 'success' : 'default'" size="xs">
                                {{ ucfirst($project->status) }}
                            </x-ui.badge>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                    <i data-lucide="folder" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No projects assigned</p>
                </div>
            @endif
        </x-ui.card>
        
        <!-- Recent Tasks -->
        <x-ui.card :padding="false">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Tasks</h2>
            </div>
            @if($user->assignedTasks->count() > 0)
                <div class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($user->assignedTasks->take(5) as $task)
                        <a href="{{ route('tasks.show', ['project' => $task->project_id, 'task' => $task->id]) }}" class="flex items-center gap-3 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex-shrink-0">
                                @if($task->status === 'completed')
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                                        <i data-lucide="check" class="w-4 h-4 text-white"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-white truncate">{{ $task->title }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $task->project->name ?? 'Unknown' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $task->progress }}%</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                    <i data-lucide="check-square" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No tasks assigned</p>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
@endsection

