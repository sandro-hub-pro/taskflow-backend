@extends('layouts.app')

@section('title', 'Create Project')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Projects</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Create</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to projects
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create New Project</h1>
        <p class="text-slate-500 dark:text-slate-400">Fill in the details to create a new project</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
            @csrf
            
            <x-ui.input 
                name="name" 
                label="Project Name" 
                placeholder="Enter project name"
                required 
            />
            
            <x-ui.textarea 
                name="description" 
                label="Description" 
                placeholder="Describe your project..."
                rows="4"
            />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.select 
                    name="status" 
                    label="Status"
                    :options="[
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ]"
                    selected="planning"
                />
                
                <x-ui.input 
                    type="date" 
                    name="start_date" 
                    label="Start Date"
                />
            </div>
            
            <x-ui.input 
                type="date" 
                name="end_date" 
                label="End Date (Optional)"
            />
            
            <!-- Team Assignment -->
            <div class="border-t border-slate-200 dark:border-slate-800 pt-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Team Assignment</h3>
                
                <!-- Incharges -->
                <div class="mb-4" x-data="{ selectedIncharges: [] }">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Project Incharges
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        @foreach($users as $user)
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
                                <input type="checkbox" name="incharges[]" value="{{ $user->id }}" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="sm" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $user->full_name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                <!-- Members -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Project Members
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        @foreach($users as $user)
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
                                <input type="checkbox" name="members[]" value="{{ $user->id }}" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="sm" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $user->full_name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                <x-ui.button href="{{ route('projects.index') }}" variant="ghost">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    Create Project
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

