@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
            <i data-lucide="lock-keyhole" class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Reset your password</h1>
        <p class="text-slate-600 dark:text-slate-400">Enter your new password below.</p>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        
        <x-ui.input 
            type="email" 
            name="email" 
            label="Email Address" 
            placeholder="you@example.com"
            icon="mail"
            :value="old('email', $request->email)"
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
        
        <x-ui.button type="submit" variant="primary" class="w-full" icon="check">
            Reset password
        </x-ui.button>
    </form>
    
    <!-- Back to Login -->
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to login
        </a>
    </div>
</div>
@endsection

