@props([
    'padding' => true,
    'hover' => false,
    'gradient' => false
])

@php
    $classes = 'bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm';
    
    if ($padding) {
        $classes .= ' p-6';
    }
    
    if ($hover) {
        $classes .= ' transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-indigo-200 dark:hover:border-indigo-800';
    }
    
    if ($gradient) {
        $classes = 'bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-indigo-500/20';
        if ($padding) {
            $classes .= ' p-6';
        }
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>

