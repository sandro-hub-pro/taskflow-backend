@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 focus:ring-indigo-500',
        'secondary' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 focus:ring-slate-500',
        'outline' => 'border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:ring-slate-500',
        'ghost' => 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white focus:ring-slate-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-500/25 hover:shadow-red-500/40 focus:ring-red-500',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 focus:ring-emerald-500',
        'warning' => 'bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 focus:ring-amber-500',
    ];
    
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs gap-1.5',
        'sm' => 'px-3 py-2 text-sm gap-2',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
        'xl' => 'px-8 py-4 text-lg gap-3',
    ];
    
    $iconSizes = [
        'xs' => 'w-3.5 h-3.5',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-6 h-6',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSizes[$size] ?? $iconSizes['md'] }}"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSizes[$size] ?? $iconSizes['md'] }}"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled || $loading]) }}>
        @if($loading)
            <svg class="animate-spin {{ $iconSizes[$size] ?? $iconSizes['md'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSizes[$size] ?? $iconSizes['md'] }}"></i>
        @endif
        {{ $slot }}
        @if(!$loading && $icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSizes[$size] ?? $iconSizes['md'] }}"></i>
        @endif
    </button>
@endif

