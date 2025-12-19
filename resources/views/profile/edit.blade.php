@extends('layouts.app')

@section('title', 'Edit Profile')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Dashboard</a>
    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-medium">Profile</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Profile Header -->
    <x-ui.card>
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="relative" x-data="{ preview: '{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : '' }}' }">
                @if(auth()->user()->profile_picture)
                    <img x-show="!preview || preview === '{{ Storage::url(auth()->user()->profile_picture) }}'" 
                         src="{{ Storage::url(auth()->user()->profile_picture) }}" 
                         alt="{{ auth()->user()->full_name }}" 
                         class="w-24 h-24 rounded-2xl object-cover">
                @else
                    <div x-show="!preview" class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                    </div>
                @endif
                <img x-show="preview && preview !== '{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : '' }}'" 
                     :src="preview" 
                     class="w-24 h-24 rounded-2xl object-cover" x-cloak>
                
                <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data" id="picture-form">
                    @csrf
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden"
                           @change="preview = URL.createObjectURL($event.target.files[0]); setTimeout(() => document.getElementById('picture-form').submit(), 100)">
                </form>
                <button type="button" onclick="document.getElementById('profile_picture').click()" 
                        class="absolute -bottom-2 -right-2 p-2 bg-indigo-600 text-white rounded-lg shadow-lg hover:bg-indigo-700 transition-colors">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="text-center sm:text-left">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ auth()->user()->full_name }}</h1>
                <p class="text-slate-500 dark:text-slate-400">@<span class="font-mono">{{ auth()->user()->username }}</span></p>
                <p class="text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </x-ui.card>
    
    <!-- Update Profile Information -->
    <x-ui.card>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Profile Information</h2>
        
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            
            <x-ui.input 
                name="username" 
                label="Username" 
                :value="auth()->user()->username"
                icon="at-sign"
                required 
            />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    name="first_name" 
                    label="First Name" 
                    :value="auth()->user()->first_name"
                    required 
                />
                
                <x-ui.input 
                    name="last_name" 
                    label="Last Name" 
                    :value="auth()->user()->last_name"
                    required 
                />
            </div>
            
            <x-ui.input 
                name="middle_name" 
                label="Middle Name" 
                :value="auth()->user()->middle_name"
            />
            
            <x-ui.input 
                type="email" 
                name="email" 
                label="Email Address" 
                :value="auth()->user()->email"
                icon="mail"
                required 
            />
            
            @if(!auth()->user()->email_verified_at)
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400"></i>
                    <p class="text-sm text-amber-700 dark:text-amber-400">Your email is not verified. <a href="{{ route('verification.send') }}" class="font-medium underline">Resend verification email</a></p>
                </div>
            @endif
            
            <div class="flex justify-end">
                <x-ui.button type="submit" variant="primary" icon="check">
                    Save Changes
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
    
    <!-- Change Password -->
    <x-ui.card>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Change Password</h2>
        
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            
            <x-ui.input 
                type="password" 
                name="current_password" 
                label="Current Password" 
                placeholder="••••••••"
                icon="lock"
                required 
            />
            
            <x-ui.input 
                type="password" 
                name="password" 
                label="New Password" 
                placeholder="••••••••"
                icon="lock"
                hint="Minimum 8 characters"
                required 
            />
            
            <x-ui.input 
                type="password" 
                name="password_confirmation" 
                label="Confirm New Password" 
                placeholder="••••••••"
                icon="lock"
                required 
            />
            
            <div class="flex justify-end">
                <x-ui.button type="submit" variant="primary" icon="key">
                    Change Password
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

