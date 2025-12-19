@props([
    'value' => 0,
    'max' => 100,
    'size' => 'md',
    'showLabel' => false,
    'color' => 'primary',
    'animated' => true
])

@php
    $percentage = min(100, max(0, ($value / $max) * 100));
    
    $sizes = [
        'xs' => 'h-1',
        'sm' => 'h-1.5',
        'md' => 'h-2',
        'lg' => 'h-3',
        'xl' => 'h-4',
    ];
    
    $colors = [
        'primary' => 'from-indigo-500 to-purple-500',
        'success' => 'from-emerald-500 to-teal-500',
        'warning' => 'from-amber-500 to-orange-500',
        'danger' => 'from-red-500 to-pink-500',
        'info' => 'from-blue-500 to-cyan-500',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel)
        <div class="flex justify-between items-center mb-1.5">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $slot ?? 'Progress' }}</span>
            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ round($percentage) }}%</span>
        </div>
    @endif
    
    <div class="{{ $sizeClass }} rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
        <div class="{{ $sizeClass }} rounded-full bg-gradient-to-r {{ $colorClass }} {{ $animated ? 'transition-all duration-500 ease-out' : '' }}"
             style="width: {{ $percentage }}%"
             role="progressbar"
             aria-valuenow="{{ $value }}"
             aria-valuemin="0"
             aria-valuemax="{{ $max }}">
        </div>
    </div>
</div>

