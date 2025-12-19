@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="mx-auto w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4 animate-pulse-slow">
            <i data-lucide="mail-check" class="w-10 h-10 text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Check your email</h1>
        <p class="text-slate-600 dark:text-slate-400">
            We've sent a verification link to<br>
            <span class="font-medium text-slate-900 dark:text-white">{{ auth()->user()->email }}</span>
        </p>
    </div>
    
    @if(session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                <p class="text-sm text-emerald-700 dark:text-emerald-400">A new verification link has been sent to your email address.</p>
            </div>
        </div>
    @endif
    
    <!-- Info Box -->
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-slate-400 mt-0.5"></i>
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <p class="mb-2">Click the link in your email to verify your account. If you don't see the email, check your spam folder.</p>
                <p>The link will expire in 60 minutes.</p>
            </div>
        </div>
    </div>
    
    <!-- Resend Form -->
    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
        @csrf
        
        <x-ui.button type="submit" variant="primary" class="w-full" icon="refresh-cw">
            Resend verification email
        </x-ui.button>
    </form>
    
    <!-- Actions -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
        <a href="{{ route('profile.edit') }}" class="text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            Wrong email address? Update it
        </a>
        <span class="hidden sm:inline text-slate-300 dark:text-slate-700">•</span>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-slate-600 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                Sign out
            </button>
        </form>
    </div>
</div>
@endsection

