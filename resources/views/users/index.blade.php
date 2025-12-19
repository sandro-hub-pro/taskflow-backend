@extends('layouts.app')

@section('title', 'Users')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Users</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Users</h1>
            <p class="text-slate-500 dark:text-slate-400">Manage system users and their roles</p>
        </div>
        
        <x-ui.button href="{{ route('users.create') }}" variant="primary" icon="user-plus">
            Add User
        </x-ui.button>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Total Users</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['superadmins'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Superadmins</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['admins'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Admins</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['incharges'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Incharges</div>
        </x-ui.card>
        <x-ui.card class="text-center">
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['users'] ?? 0 }}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Users</div>
        </x-ui.card>
    </div>
    
    <!-- Filters -->
    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('users.index') }}" class="p-4 flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, email, or username..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                </div>
            </div>
            
            <div class="flex gap-2">
                <select name="role" onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-xl border-0 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">All Roles</option>
                    <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="incharge" {{ request('role') === 'incharge' ? 'selected' : '' }}>Incharge</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                </select>
                
                <x-ui.button type="submit" variant="secondary" icon="filter">
                    Filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
    
    <!-- Users Table -->
    <x-ui.card :padding="false">
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :name="$user->full_name" :src="$user->profile_picture ? Storage::url($user->profile_picture) : null" size="md" />
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $user->full_name }}</p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-slate-600 dark:text-slate-400">@</span>
                                    <span class="text-slate-900 dark:text-white">{{ $user->username }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $roleVariants = [
                                            'superadmin' => 'danger',
                                            'admin' => 'primary',
                                            'incharge' => 'secondary',
                                            'user' => 'default'
                                        ];
                                    @endphp
                                    <x-ui.badge :variant="$roleVariants[$user->role] ?? 'default'" :dot="true">
                                        {{ ucfirst($user->role) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->email_verified_at)
                                        <x-ui.badge variant="success" size="sm">Verified</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="warning" size="sm">Pending</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    {{ $user->created_at->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-ui.button href="{{ route('users.show', $user) }}" variant="ghost" size="sm" icon="eye" />
                                        <x-ui.button href="{{ route('users.edit', $user) }}" variant="ghost" size="sm" icon="edit-2" />
                                        @if($user->id !== auth()->id() && (!$user->isAdmin() || auth()->user()->isSuperAdmin()) && !$user->isSuperAdmin())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this user?')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @else
            <x-ui.empty-state 
                icon="users" 
                title="No users found"
                description="No users match the current filters."
                action="{{ route('users.create') }}"
                actionLabel="Add User"
                actionIcon="user-plus"
            />
        @endif
    </x-ui.card>
</div>
@endsection

