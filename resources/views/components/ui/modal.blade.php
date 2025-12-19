@props([
    'name' => 'modal',
    'title' => null,
    'maxWidth' => 'md',
    'closeable' => true
])

@php
    $maxWidthClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        'full' => 'sm:max-w-full sm:m-4',
    ];
@endphp

<div x-data="{ open: false }"
     x-on:open-{{ $name }}.window="open = true"
     x-on:close-{{ $name }}.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    
    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
         @if($closeable) @click="open = false" @endif>
    </div>
    
    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full {{ $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['md'] }} bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
            
            <!-- Header -->
            @if($title || $closeable)
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    @if($title)
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    @else
                        <div></div>
                    @endif
                    
                    @if($closeable)
                        <button @click="open = false" 
                                class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    @endif
                </div>
            @endif
            
            <!-- Content -->
            <div class="p-6">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            @if(isset($footer))
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

