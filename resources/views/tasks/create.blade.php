@extends('layouts.app')

@section('title', 'Create Task')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    @if(isset($project))
        <a href="{{ route('projects.show', $project) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($project->name, 15) }}</a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    @endif
    <span class="text-slate-900 dark:text-white font-medium">Create Task</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ isset($project) ? route('projects.show', $project) : route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create New Task</h1>
        <p class="text-slate-500 dark:text-slate-400">Add a new task to the project</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ isset($project) ? route('projects.tasks.store', $project) : route('tasks.store') }}" class="space-y-6">
            @csrf
            
            @if(!isset($project))
                <x-ui.select 
                    name="project_id" 
                    label="Project"
                    placeholder="Select a project"
                    :options="$projects->pluck('name', 'id')->toArray()"
                    required
                />
            @endif
            
            <x-ui.input 
                name="title" 
                label="Task Title" 
                placeholder="Enter task title"
                required 
            />
            
            <x-ui.textarea 
                name="description" 
                label="Description" 
                placeholder="Describe the task..."
                rows="4"
            />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.select 
                    name="status" 
                    label="Status"
                    :options="[
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'under_review' => 'Under Review',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ]"
                    selected="pending"
                />
                
                <x-ui.select 
                    name="priority" 
                    label="Priority"
                    :options="[
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent'
                    ]"
                    selected="medium"
                />
            </div>
            
            <x-ui.input 
                type="date" 
                name="due_date" 
                label="Due Date"
            />
            
            <!-- Assignees -->
            @if(isset($project) && $project->users->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Assign To
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        @foreach($project->users as $user)
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
                                <input type="checkbox" name="assignees[]" value="{{ $user->id }}" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="sm" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $user->full_name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $user->pivot->role === 'incharge' ? 'Incharge' : 'Member' }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                <x-ui.button href="{{ isset($project) ? route('projects.show', $project) : route('dashboard') }}" variant="ghost">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    Create Task
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

