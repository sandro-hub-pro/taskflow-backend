@extends('layouts.app')

@section('title', $task->title)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.show', $project) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($project->name, 15) }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">{{ Str::limit($task->title, 20) }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Link -->
    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to {{ $project->name }}
    </a>
    
    <!-- Task Header -->
    <x-ui.card>
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    @php
                        $statusClasses = [
                            'pending' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                            'in_progress' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                            'under_review' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                            'completed' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
                            'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                        ];
                        $priorityVariants = [
                            'low' => 'success',
                            'medium' => 'info',
                            'high' => 'warning',
                            'urgent' => 'danger'
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClasses[$task->status] ?? '' }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                    <x-ui.badge :variant="$priorityVariants[$task->priority] ?? 'default'" :dot="true">
                        {{ ucfirst($task->priority) }} Priority
                    </x-ui.badge>
                    @if($task->is_overdue)
                        <x-ui.badge variant="danger" :dot="true">Overdue</x-ui.badge>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $task->title }}</h1>
            </div>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isProjectIncharge($project->id))
                <div class="flex items-center gap-2">
                    <x-ui.button href="{{ route('tasks.edit', ['project' => $project->id, 'task' => $task->id]) }}" variant="secondary" icon="edit-2" size="sm">
                        Edit
                    </x-ui.button>
                </div>
            @endif
        </div>
        
        @if($task->description)
            <div class="prose prose-slate dark:prose-invert max-w-none mb-6">
                <p class="text-slate-600 dark:text-slate-400">{{ $task->description }}</p>
            </div>
        @endif
        
        <!-- Task Meta -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Project</p>
                <a href="{{ route('projects.show', $project) }}" class="font-medium text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                    {{ $project->name }}
                </a>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Due Date</p>
                <p class="font-medium text-slate-900 dark:text-white {{ $task->is_overdue ? 'text-red-600 dark:text-red-400' : '' }}">
                    {{ $task->due_date ? $task->due_date->format('M j, Y') : 'No due date' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Created By</p>
                <p class="font-medium text-slate-900 dark:text-white">{{ $task->creator->full_name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Created</p>
                <p class="font-medium text-slate-900 dark:text-white">{{ $task->created_at->format('M j, Y') }}</p>
            </div>
        </div>
    </x-ui.card>
    
    <!-- Progress Section -->
    <x-ui.card x-data="{ editing: false }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Progress</h2>
            @if($canUpdateProgress)
                <button @click="editing = !editing" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    <span x-text="editing ? 'Cancel' : 'Update Progress'"></span>
                </button>
            @endif
        </div>
        
        <!-- Progress Display -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-slate-500 dark:text-slate-400">Task Completion</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $task->progress }}%</span>
            </div>
            <x-ui.progress-bar :value="$task->progress" size="lg" />
        </div>
        
        <!-- Progress Update Form -->
        @if($canUpdateProgress)
            <form x-show="editing" x-cloak method="POST" action="{{ route('tasks.update-progress', ['project' => $project->id, 'task' => $task->id]) }}" class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                @csrf
                @method('PATCH')
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">New Progress</label>
                    <div class="flex items-center gap-4">
                        <input type="range" name="progress" value="{{ $task->progress }}" min="0" max="100" step="5"
                               class="flex-1" oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <span class="w-12 text-right font-medium text-slate-900 dark:text-white">{{ $task->progress }}%</span>
                    </div>
                </div>
                
                <x-ui.select 
                    name="status" 
                    label="Status"
                    :options="[
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'under_review' => 'Under Review',
                        'completed' => 'Completed'
                    ]"
                    :selected="$task->status"
                />
                
                <div class="flex justify-end gap-2">
                    <x-ui.button type="button" variant="ghost" @click="editing = false">Cancel</x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="check">Save Progress</x-ui.button>
                </div>
            </form>
        @endif
    </x-ui.card>
    
    <!-- Assignees -->
    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Assignees</h2>
            @if(auth()->user()->isAdmin() || auth()->user()->isProjectIncharge($project->id))
                <x-ui.button href="{{ route('tasks.assign', ['project' => $project->id, 'task' => $task->id]) }}" variant="ghost" size="sm" icon="user-plus">
                    Manage
                </x-ui.button>
            @endif
        </div>
        
        @if($task->assignees->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($task->assignees as $assignee)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <x-ui.avatar :name="$assignee->full_name" :src="$assignee->profile_picture ? Storage::url($assignee->profile_picture) : null" size="md" status="online" />
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ $assignee->full_name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ $assignee->email }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-slate-500 dark:text-slate-400 py-4">No one is assigned to this task yet.</p>
        @endif
    </x-ui.card>
    
    <!-- Comments -->
    <x-ui.card :padding="false">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Comments</h2>
        </div>
        
        @if($task->comments->count() > 0)
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($task->comments as $comment)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.avatar :name="$comment->user->full_name" :src="$comment->user->profile_picture ? Storage::url($comment->user->profile_picture) : null" size="sm" />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $comment->user->full_name }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-400">{{ $comment->content }}</p>
                            </div>
                            @if(auth()->user()->id === $comment->user_id || auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('tasks.comments.destroy', ['project' => $project->id, 'task' => $task->id, 'comment' => $comment->id]) }}" class="flex-shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-500 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                <i data-lucide="message-circle" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>No comments yet. Be the first to comment!</p>
            </div>
        @endif
        
        <!-- Add Comment Form -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
            <form method="POST" action="{{ route('tasks.comments.store', ['project' => $project->id, 'task' => $task->id]) }}" class="flex gap-3">
                @csrf
                <x-ui.avatar :name="auth()->user()->full_name" :src="auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : null" size="sm" />
                <div class="flex-1">
                    <textarea name="content" rows="2" placeholder="Write a comment..." required
                              class="w-full px-4 py-2 rounded-xl border-0 bg-white dark:bg-slate-700 ring-1 ring-slate-200 dark:ring-slate-600 focus:ring-2 focus:ring-indigo-500 text-sm resize-none"></textarea>
                </div>
                <x-ui.button type="submit" variant="primary" icon="send" size="sm">Send</x-ui.button>
            </form>
        </div>
    </x-ui.card>
</div>
@endsection

