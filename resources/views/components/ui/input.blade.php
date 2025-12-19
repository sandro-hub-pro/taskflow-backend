@props([
    'type' => 'text',
    'name' => '',
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'icon' => null,
    'iconPosition' => 'left',
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false
])

@php
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    
    $inputClasses = 'block w-full rounded-xl border-0 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200 text-sm';
    
    if ($hasError) {
        $inputClasses .= ' ring-2 ring-red-500 focus:ring-red-500';
    } else {
        $inputClasses .= ' ring-1 ring-slate-200 dark:ring-slate-700';
    }
    
    if ($icon) {
        $inputClasses .= $iconPosition === 'left' ? ' pl-10 pr-4' : ' pl-4 pr-10';
    } else {
        $inputClasses .= ' px-4';
    }
    
    $inputClasses .= ' py-3';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i data-lucide="{{ $icon }}" class="w-5 h-5 text-slate-400"></i>
            </div>
        @endif
        
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('class')->merge(['class' => $inputClasses]) }}
        >
        
        @if($icon && $iconPosition === 'right')
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i data-lucide="{{ $icon }}" class="w-5 h-5 text-slate-400"></i>
            </div>
        @endif
    </div>
    
    @if($hint && !$hasError)
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    
    @if($hasError)
        <p class="text-xs text-red-500 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
            {{ $errorMessage }}
        </p>
    @endif
</div>

