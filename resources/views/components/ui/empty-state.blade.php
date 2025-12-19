@props([
    'icon' => 'inbox',
    'title' => 'No data',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
    'actionIcon' => null
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-4 text-center']) }}>
    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
        <i data-lucide="{{ $icon }}" class="w-8 h-8 text-slate-400 dark:text-slate-500"></i>
    </div>
    
    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">{{ $title }}</h3>
    
    @if($description)
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mb-4">{{ $description }}</p>
    @endif
    
    @if($action)
        <x-ui.button :href="$action" variant="primary" :icon="$actionIcon">
            {{ $actionLabel }}
        </x-ui.button>
    @endif
    
    {{ $slot }}
</div>

