@extends('layouts.app')

@section('title', 'Add User')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('users.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Users</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Add User</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to users
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Add New User</h1>
        <p class="text-slate-500 dark:text-slate-400">Create a new user account</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Profile Picture -->
            <div x-data="{ preview: null }">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Profile Picture</label>
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <i data-lucide="user" class="w-10 h-10"></i>
                            </template>
                        </div>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden"
                               @change="preview = URL.createObjectURL($event.target.files[0])">
                        <x-ui.button type="button" variant="secondary" size="sm" icon="upload" onclick="document.getElementById('profile_picture').click()">
                            Upload Photo
                        </x-ui.button>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">JPG, PNG or GIF. Max 2MB.</p>
                    </div>
                </div>
                @error('profile_picture')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <x-ui.input 
                name="username" 
                label="Username" 
                placeholder="johndoe"
                icon="at-sign"
                required 
            />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    name="first_name" 
                    label="First Name" 
                    placeholder="John"
                    required 
                />
                
                <x-ui.input 
                    name="last_name" 
                    label="Last Name" 
                    placeholder="Doe"
                    required 
                />
            </div>
            
            <x-ui.input 
                name="middle_name" 
                label="Middle Name" 
                placeholder="(Optional)"
            />
            
            <x-ui.input 
                type="email" 
                name="email" 
                label="Email Address" 
                placeholder="john@example.com"
                icon="mail"
                required 
            />
            
            <x-ui.input 
                type="password" 
                name="password" 
                label="Password" 
                placeholder="••••••••"
                icon="lock"
                hint="Minimum 8 characters"
                required 
            />
            
            @php
                $roleOptions = [
                    'user' => 'User - Can view and update assigned tasks',
                    'incharge' => 'Incharge - Can manage tasks in assigned projects',
                    'admin' => 'Admin - Full access to all features',
                ];
                if (auth()->user()->isSuperAdmin()) {
                    $roleOptions['superadmin'] = 'Superadmin - Full access with user deletion rights';
                }
            @endphp
            <x-ui.select 
                name="role" 
                label="Role"
                :options="$roleOptions"
                selected="user"
                required
            />
            
            <!-- Role Info -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">Role Permissions</h4>
                <dl class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    <div class="flex items-start gap-2">
                        <x-ui.badge variant="default" size="xs">User</x-ui.badge>
                        <span>View assigned tasks, update progress</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-ui.badge variant="secondary" size="xs">Incharge</x-ui.badge>
                        <span>Manage tasks in assigned projects, assign users to tasks</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-ui.badge variant="primary" size="xs">Admin</x-ui.badge>
                        <span>Full access: manage users, projects, and all tasks</span>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <div class="flex items-start gap-2">
                        <x-ui.badge variant="danger" size="xs">Superadmin</x-ui.badge>
                        <span>Full access with ability to delete admin users</span>
                    </div>
                    @endif
                </dl>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
                <x-ui.button href="{{ route('users.index') }}" variant="ghost">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="user-plus">
                    Create User
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

