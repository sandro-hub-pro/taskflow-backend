@extends('layouts.app')

@section('title', 'Assign Users - ' . $task->title)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.show', $project) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($project->name, 15) }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('tasks.show', ['project' => $project->id, 'task' => $task->id]) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($task->title, 15) }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Assign</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('tasks.show', ['project' => $project->id, 'task' => $task->id]) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to task
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Assign Users</h1>
        <p class="text-slate-500 dark:text-slate-400">Select team members to work on "{{ $task->title }}"</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ route('tasks.assign.update', ['project' => $project->id, 'task' => $task->id]) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Project Members -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Project Team Members
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Select users to assign to this task</span>
                </label>
                
                @if($project->users->count() > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                        @foreach($project->users as $user)
                            <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                                <input type="checkbox" 
                                       name="assignees[]" 
                                       value="{{ $user->id }}" 
                                       {{ $task->assignees->contains($user->id) ? 'checked' : '' }}
                                       class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="md" />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 dark:text-white truncate">{{ $user->full_name }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                                </div>
                                <x-ui.badge :variant="$user->pivot->role === 'incharge' ? 'primary' : 'secondary'" size="sm">
                                    {{ ucfirst($user->pivot->role) }}
                                </x-ui.badge>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p>No team members in this project.</p>
                        <a href="{{ route('projects.members', $project) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline mt-2 inline-block">Add team members first</a>
                    </div>
                @endif
            </div>
            
            <!-- Currently Assigned -->
            @if($task->assignees->count() > 0)
                <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
                    <h3 class="text-sm font-medium text-indigo-900 dark:text-indigo-300 mb-2">Currently Assigned</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($task->assignees as $assignee)
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-full">
                                <x-ui.avatar :name="$assignee->full_name" :src="$assignee->profile_picture ? Storage::url($assignee->profile_picture) : null" size="xs" />
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $assignee->full_name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                <x-ui.button href="{{ route('tasks.show', ['project' => $project->id, 'task' => $task->id]) }}" variant="ghost">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    Save Assignments
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

