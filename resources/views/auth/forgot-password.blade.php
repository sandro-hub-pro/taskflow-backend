@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
            <i data-lucide="key-round" class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Forgot your password?</h1>
        <p class="text-slate-600 dark:text-slate-400">No worries! Enter your email and we'll send you a reset link.</p>
    </div>
    
    @if(session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('status') }}</p>
            </div>
        </div>
    @endif
    
    <!-- Form -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        
        <x-ui.input 
            type="email" 
            name="email" 
            label="Email Address" 
            placeholder="you@example.com"
            icon="mail"
            required 
        />
        
        <x-ui.button type="submit" variant="primary" class="w-full" icon="send">
            Send reset link
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

