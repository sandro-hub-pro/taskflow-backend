@props([
    'name' => '',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false
])

@php
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $selectedValue = old($name, $selected);
    
    $selectClasses = 'block w-full rounded-xl border-0 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200 text-sm px-4 py-3 pr-10';
    
    if ($hasError) {
        $selectClasses .= ' ring-2 ring-red-500 focus:ring-red-500';
    } else {
        $selectClasses .= ' ring-1 ring-slate-200 dark:ring-slate-700';
    }
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
        <select 
            name="{{ $name }}" 
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('class')->merge(['class' => $selectClasses]) }}
        >
            @if($placeholder)
                <option value="" {{ !$selectedValue ? 'selected' : '' }} disabled>{{ $placeholder }}</option>
            @endif
            
            @foreach($options as $value => $label)
                <option value="{{ $value }}" {{ $selectedValue == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400"></i>
        </div>
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

