@extends('layouts.app')

@section('title', 'Manage Team - ' . $project->name)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Projects</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.show', $project) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($project->name, 15) }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Team</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to project
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Manage Team</h1>
        <p class="text-slate-500 dark:text-slate-400">Add or remove team members from {{ $project->name }}</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ route('projects.members.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Incharges -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shield" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Project Incharges
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Can manage tasks and assign users</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    @foreach($users as $user)
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   name="incharges[]" 
                                   value="{{ $user->id }}" 
                                   {{ $project->incharges->contains($user->id) ? 'checked' : '' }}
                                   class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                            <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="sm" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $user->full_name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                            </div>
                            <x-ui.badge variant="primary" size="xs">{{ ucfirst($user->role) }}</x-ui.badge>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Members -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                        Project Members
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Can view project and update assigned tasks</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    @foreach($users as $user)
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   name="members[]" 
                                   value="{{ $user->id }}" 
                                   {{ $project->members->contains($user->id) ? 'checked' : '' }}
                                   class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                            <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="sm" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $user->full_name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                            </div>
                            <x-ui.badge variant="secondary" size="xs">{{ ucfirst($user->role) }}</x-ui.badge>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Info -->
            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                <div class="flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"></i>
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <p class="font-medium mb-1">Note about roles:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-600 dark:text-blue-400">
                            <li>Incharges can create, edit, and delete tasks</li>
                            <li>Incharges can assign tasks to project members</li>
                            <li>Members can only update their assigned tasks</li>
                            <li>A user can be both an incharge and a member</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                <x-ui.button href="{{ route('projects.show', $project) }}" variant="ghost">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    Save Changes
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

