@extends('layouts.app')

@section('title', 'Edit Project')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Projects</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('projects.show', $project) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($project->name, 15) }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Edit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to project
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Project</h1>
        <p class="text-slate-500 dark:text-slate-400">Update project details and settings</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <x-ui.input 
                name="name" 
                label="Project Name" 
                placeholder="Enter project name"
                :value="$project->name"
                required 
            />
            
            <x-ui.textarea 
                name="description" 
                label="Description" 
                placeholder="Describe your project..."
                :value="$project->description"
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
                    :selected="$project->status"
                />
                
                <x-ui.input 
                    type="date" 
                    name="start_date" 
                    label="Start Date"
                    :value="$project->start_date?->format('Y-m-d')"
                />
            </div>
            
            <x-ui.input 
                type="date" 
                name="end_date" 
                label="End Date (Optional)"
                :value="$project->end_date?->format('Y-m-d')"
            />
            
            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-slate-800">
                <button type="button" 
                        onclick="document.getElementById('delete-form').submit()"
                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete Project
                </button>
                
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('projects.show', $project) }}" variant="ghost">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="check">
                        Save Changes
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>
    
    <!-- Delete Form (Hidden) -->
    <form id="delete-form" method="POST" action="{{ route('projects.destroy', $project) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

