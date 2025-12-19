@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Welcome back</h1>
        <p class="text-slate-600 dark:text-slate-400">Sign in to continue to your dashboard</p>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        
        <x-ui.input 
            type="email" 
            name="email" 
            label="Email Address" 
            placeholder="you@example.com"
            icon="mail"
            required 
        />
        
        <x-ui.input 
            type="password" 
            name="password" 
            label="Password" 
            placeholder="••••••••"
            icon="lock"
            required 
        />
        
        <div class="flex items-center justify-between">
            <x-ui.checkbox name="remember" label="Remember me" />
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                Forgot password?
            </a>
        </div>
        
        <x-ui.button type="submit" variant="primary" class="w-full" icon="log-in">
            Sign in
        </x-ui.button>
    </form>
    
    <!-- Divider -->
    <div class="my-6 flex items-center">
        <hr class="flex-1 border-slate-200 dark:border-slate-700">
        <span class="px-4 text-sm text-slate-500 dark:text-slate-400">or</span>
        <hr class="flex-1 border-slate-200 dark:border-slate-700">
    </div>
    
    <!-- Register Link -->
    <p class="text-center text-sm text-slate-600 dark:text-slate-400">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
            Create an account
        </a>
    </p>
</div>
@endsection

