@extends('layouts.app')

@section('title', 'Projects')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Projects</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Projects</h1>
            <p class="text-slate-500 dark:text-slate-400">Manage and track all your projects</p>
        </div>
        
        @if(auth()->user()->isAdmin())
        <x-ui.button href="{{ route('projects.create') }}" variant="primary" icon="plus">
            New Project
        </x-ui.button>
        @endif
    </div>
    
    <!-- Filters -->
    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('projects.index') }}" class="p-4 flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search projects..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                </div>
            </div>
            
            <div class="flex gap-2">
                <select name="status" onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">All Status</option>
                    <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planning</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                
                <x-ui.button type="submit" variant="secondary" icon="filter">
                    Filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
    
    <!-- Projects Grid -->
    @if($projects->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                <x-ui.card hover class="flex flex-col">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white">
                            <i data-lucide="folder" class="w-6 h-6"></i>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @php
                                $statusVariants = [
                                    'planning' => 'default',
                                    'active' => 'success',
                                    'on_hold' => 'warning',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ];
                            @endphp
                            <x-ui.badge :variant="$statusVariants[$project->status] ?? 'default'" :dot="true">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </x-ui.badge>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ $project->name }}
                        </a>
                    </h3>
                    
                    @if($project->description)
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 line-clamp-2">{{ $project->description }}</p>
                    @endif
                    
                    <!-- Progress -->
                    <div class="mt-auto pt-4">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-slate-500 dark:text-slate-400">Progress</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $project->progress }}%</span>
                        </div>
                        <x-ui.progress-bar :value="$project->progress" size="md" />
                    </div>
                    
                    <!-- Footer -->
                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <i data-lucide="check-square" class="w-4 h-4"></i>
                            <span>{{ $project->completed_tasks_count }}/{{ $project->tasks_count }} tasks</span>
                        </div>
                        
                        <!-- Team Avatars -->
                        <div class="flex -space-x-2">
                            @foreach($project->users->take(3) as $user)
                                <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="sm" />
                            @endforeach
                            @if($project->users->count() > 3)
                                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-xs font-medium text-slate-600 dark:text-slate-400">
                                    +{{ $project->users->count() - 3 }}
                                </div>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @else
        <x-ui.card>
            <x-ui.empty-state 
                icon="folder" 
                title="No projects found"
                description="Get started by creating your first project."
                :action="auth()->user()->isAdmin() ? route('projects.create') : null"
                actionLabel="Create Project"
                actionIcon="plus"
            />
        </x-ui.card>
    @endif
</div>
@endsection

