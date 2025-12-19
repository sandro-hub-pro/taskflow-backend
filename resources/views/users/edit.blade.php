@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('users.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Users</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <a href="{{ route('users.show', $user) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">{{ $user->first_name }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('users.show', $user) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to profile
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit User</h1>
        <p class="text-slate-500 dark:text-slate-400">Update {{ $user->full_name }}'s account details</p>
    </div>
    
    <!-- Form -->
    <x-ui.card>
        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Profile Picture -->
            <div x-data="{ preview: '{{ $user->profile_picture ? Storage::url($user->profile_picture) : '' }}' }">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Profile Picture</label>
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <span>{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</span>
                            </template>
                        </div>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden"
                               @change="preview = URL.createObjectURL($event.target.files[0])">
                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="secondary" size="sm" icon="upload" onclick="document.getElementById('profile_picture').click()">
                                Change Photo
                            </x-ui.button>
                            @if($user->profile_picture)
                                <button type="button" @click="preview = ''" class="text-sm text-red-600 dark:text-red-400 hover:underline">Remove</button>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">JPG, PNG or GIF. Max 2MB.</p>
                    </div>
                </div>
            </div>
            
            <x-ui.input 
                name="username" 
                label="Username" 
                placeholder="johndoe"
                :value="$user->username"
                icon="at-sign"
                required 
            />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    name="first_name" 
                    label="First Name" 
                    placeholder="John"
                    :value="$user->first_name"
                    required 
                />
                
                <x-ui.input 
                    name="last_name" 
                    label="Last Name" 
                    placeholder="Doe"
                    :value="$user->last_name"
                    required 
                />
            </div>
            
            <x-ui.input 
                name="middle_name" 
                label="Middle Name" 
                placeholder="(Optional)"
                :value="$user->middle_name"
            />
            
            <x-ui.input 
                type="email" 
                name="email" 
                label="Email Address" 
                placeholder="john@example.com"
                :value="$user->email"
                icon="mail"
                required 
            />
            
            <x-ui.input 
                type="password" 
                name="password" 
                label="New Password" 
                placeholder="Leave blank to keep current"
                icon="lock"
                hint="Leave blank to keep current password"
            />
            
            @php
                $roleOptions = [
                    'user' => 'User',
                    'incharge' => 'Incharge',
                    'admin' => 'Admin',
                ];
                if (auth()->user()->isSuperAdmin()) {
                    $roleOptions['superadmin'] = 'Superadmin';
                }
            @endphp
            <x-ui.select 
                name="role" 
                label="Role"
                :options="$roleOptions"
                :selected="$user->role"
                required
            />
            
            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-slate-800">
                @if($user->id !== auth()->id() && (!$user->isAdmin() || auth()->user()->isSuperAdmin()) && !$user->isSuperAdmin())
                    <button type="button" 
                            onclick="if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) document.getElementById('delete-form').submit()"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 flex items-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Delete User
                    </button>
                @else
                    <div></div>
                @endif
                
                <div class="flex items-center gap-3">
                    <x-ui.button href="{{ route('users.show', $user) }}" variant="ghost">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="check">
                        Save Changes
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>
    
    @if($user->id !== auth()->id() && (!$user->isAdmin() || auth()->user()->isSuperAdmin()) && !$user->isSuperAdmin())
        <!-- Delete Form (Hidden) -->
        <form id="delete-form" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
@endsection

