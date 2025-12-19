@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
<div class="animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Create your account</h1>
        <p class="text-slate-600 dark:text-slate-400">Start managing your tasks like a pro</p>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        
        <x-ui.input 
            type="text" 
            name="username" 
            label="Username" 
            placeholder="johndoe"
            icon="at-sign"
            required 
        />
        
        <div class="grid grid-cols-2 gap-4">
            <x-ui.input 
                type="text" 
                name="first_name" 
                label="First Name" 
                placeholder="John"
                required 
            />
            
            <x-ui.input 
                type="text" 
                name="last_name" 
                label="Last Name" 
                placeholder="Doe"
                required 
            />
        </div>
        
        <x-ui.input 
            type="text" 
            name="middle_name" 
            label="Middle Name" 
            placeholder="(Optional)"
        />
        
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
            hint="Minimum 8 characters"
            required 
        />
        
        <x-ui.input 
            type="password" 
            name="password_confirmation" 
            label="Confirm Password" 
            placeholder="••••••••"
            icon="lock"
            required 
        />
        
        <x-ui.checkbox name="terms" label="I agree to the Terms of Service and Privacy Policy" required />
        
        <x-ui.button type="submit" variant="primary" class="w-full" icon="user-plus">
            Create account
        </x-ui.button>
    </form>
    
    <!-- Divider -->
    <div class="my-6 flex items-center">
        <hr class="flex-1 border-slate-200 dark:border-slate-700">
        <span class="px-4 text-sm text-slate-500 dark:text-slate-400">or</span>
        <hr class="flex-1 border-slate-200 dark:border-slate-700">
    </div>
    
    <!-- Login Link -->
    <p class="text-center text-sm text-slate-600 dark:text-slate-400">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
            Sign in
        </a>
    </p>
</div>
@endsection

