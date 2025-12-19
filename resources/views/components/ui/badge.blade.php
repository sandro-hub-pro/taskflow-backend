@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'removable' => false
])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        'primary' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        'secondary' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'danger' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    ];
    
    $sizes = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];
    
    $dotColors = [
        'default' => 'bg-slate-500',
        'primary' => 'bg-indigo-500',
        'secondary' => 'bg-purple-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
    ];
    
    $classes = 'inline-flex items-center font-medium rounded-full ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="mr-1.5 w-1.5 h-1.5 rounded-full {{ $dotColors[$variant] ?? $dotColors['default'] }}"></span>
    @endif
    {{ $slot }}
    @if($removable)
        <button type="button" class="ml-1.5 -mr-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
            <i data-lucide="x" class="w-3 h-3"></i>
        </button>
    @endif
</span>

