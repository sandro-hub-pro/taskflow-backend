@props([
    'src' => null,
    'alt' => '',
    'name' => null,
    'size' => 'md',
    'status' => null
])

@php
    $sizes = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-lg',
        '2xl' => 'w-20 h-20 text-xl',
    ];
    
    $statusSizes = [
        'xs' => 'w-1.5 h-1.5',
        'sm' => 'w-2 h-2',
        'md' => 'w-2.5 h-2.5',
        'lg' => 'w-3 h-3',
        'xl' => 'w-4 h-4',
        '2xl' => 'w-5 h-5',
    ];
    
    $statusColors = [
        'online' => 'bg-emerald-500',
        'offline' => 'bg-slate-400',
        'busy' => 'bg-red-500',
        'away' => 'bg-amber-500',
    ];
    
    $initials = '';
    if ($name) {
        $words = explode(' ', $name);
        $initials = strtoupper(substr($words[0], 0, 1));
        if (count($words) > 1) {
            $initials .= strtoupper(substr(end($words), 0, 1));
        }
    }
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="relative inline-flex">
    @if($src)
        <img src="{{ $src }}" 
             alt="{{ $alt }}" 
             class="{{ $sizeClass }} rounded-xl object-cover"
             {{ $attributes }}>
    @else
        <div class="{{ $sizeClass }} rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-semibold"
             {{ $attributes }}>
            {{ $initials ?: '?' }}
        </div>
    @endif
    
    @if($status)
        <span class="absolute -bottom-0.5 -right-0.5 {{ $statusSizes[$size] ?? $statusSizes['md'] }} {{ $statusColors[$status] ?? $statusColors['offline'] }} border-2 border-white dark:border-slate-900 rounded-full"></span>
    @endif
</div>

