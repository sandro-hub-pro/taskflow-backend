@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Settings</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Appearance -->
    <x-ui.card>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Appearance</h2>
        
        <div class="space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">Choose how TaskFlow looks to you. Select a single theme, or sync with your system settings.</p>
            
            <div class="grid grid-cols-3 gap-4" x-data>
                <!-- Light Theme -->
                <button @click="setTheme('light')" 
                        class="relative p-4 rounded-xl border-2 transition-all"
                        :class="theme === 'light' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                    <div class="w-full aspect-video rounded-lg bg-white border border-slate-200 mb-3 p-2">
                        <div class="w-full h-2 bg-slate-200 rounded mb-1"></div>
                        <div class="w-3/4 h-2 bg-slate-100 rounded"></div>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Light</span>
                    </div>
                    <div x-show="theme === 'light'" class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                        <i data-lucide="check" class="w-3 h-3 text-white"></i>
                    </div>
                </button>
                
                <!-- Dark Theme -->
                <button @click="setTheme('dark')" 
                        class="relative p-4 rounded-xl border-2 transition-all"
                        :class="theme === 'dark' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                    <div class="w-full aspect-video rounded-lg bg-slate-900 border border-slate-700 mb-3 p-2">
                        <div class="w-full h-2 bg-slate-700 rounded mb-1"></div>
                        <div class="w-3/4 h-2 bg-slate-800 rounded"></div>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <i data-lucide="moon" class="w-4 h-4 text-indigo-400"></i>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Dark</span>
                    </div>
                    <div x-show="theme === 'dark'" class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                        <i data-lucide="check" class="w-3 h-3 text-white"></i>
                    </div>
                </button>
                
                <!-- System Theme -->
                <button @click="setTheme('system')" 
                        class="relative p-4 rounded-xl border-2 transition-all"
                        :class="theme === 'system' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                    <div class="w-full aspect-video rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 mb-3 flex">
                        <div class="w-1/2 bg-white p-1">
                            <div class="w-full h-2 bg-slate-200 rounded mb-1"></div>
                            <div class="w-3/4 h-2 bg-slate-100 rounded"></div>
                        </div>
                        <div class="w-1/2 bg-slate-900 p-1">
                            <div class="w-full h-2 bg-slate-700 rounded mb-1"></div>
                            <div class="w-3/4 h-2 bg-slate-800 rounded"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <i data-lucide="monitor" class="w-4 h-4 text-slate-500"></i>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">System</span>
                    </div>
                    <div x-show="theme === 'system'" class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                        <i data-lucide="check" class="w-3 h-3 text-white"></i>
                    </div>
                </button>
            </div>
        </div>
    </x-ui.card>
    
    <!-- Account Info -->
    <x-ui.card>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Account Information</h2>
        
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">Role</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Your current account role</p>
                </div>
                @php
                    $roleVariants = [
                        'admin' => 'primary',
                        'incharge' => 'secondary',
                        'user' => 'default'
                    ];
                @endphp
                <x-ui.badge :variant="$roleVariants[auth()->user()->role] ?? 'default'" size="lg">
                    {{ ucfirst(auth()->user()->role) }}
                </x-ui.badge>
            </div>
            
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">Email Verification</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Status of your email verification</p>
                </div>
                @if(auth()->user()->email_verified_at)
                    <x-ui.badge variant="success" size="lg">Verified</x-ui.badge>
                @else
                    <x-ui.badge variant="warning" size="lg">Not Verified</x-ui.badge>
                @endif
            </div>
            
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">Member Since</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">When you joined TaskFlow</p>
                </div>
                <span class="text-slate-900 dark:text-white font-medium">{{ auth()->user()->created_at->format('M j, Y') }}</span>
            </div>
        </div>
    </x-ui.card>
    
    <!-- Danger Zone -->
    <x-ui.card class="border-red-200 dark:border-red-800">
        <h2 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Danger Zone</h2>
        
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-medium text-red-900 dark:text-red-300">Delete Account</h3>
                    <p class="text-sm text-red-700 dark:text-red-400 mt-1">Permanently delete your account and all associated data. This action cannot be undone.</p>
                </div>
                <x-ui.button variant="danger" size="sm" icon="trash-2" disabled>
                    Delete
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>
</div>
@endsection

