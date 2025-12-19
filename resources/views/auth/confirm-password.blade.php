@extends('layouts.auth')

@section('title', 'Confirm Password')

@section('content')
<div class="animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
            <i data-lucide="shield-check" class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Confirm your password</h1>
        <p class="text-slate-600 dark:text-slate-400">This is a secure area. Please confirm your password before continuing.</p>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf
        
        <x-ui.input 
            type="password" 
            name="password" 
            label="Password" 
            placeholder="••••••••"
            icon="lock"
            required 
        />
        
        <x-ui.button type="submit" variant="primary" class="w-full" icon="check">
            Confirm
        </x-ui.button>
    </form>
</div>
@endsection

