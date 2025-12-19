@props([
    'name' => '',
    'label' => null,
    'checked' => false,
    'value' => '1',
    'disabled' => false,
    'error' => null
])

@php
    $hasError = $error || $errors->has($name);
    $isChecked = old($name, $checked);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'flex items-start gap-3']) }}>
    <div class="flex items-center h-5">
        <input type="checkbox" 
               name="{{ $name }}" 
               id="{{ $name }}" 
               value="{{ $value }}"
               {{ $isChecked ? 'checked' : '' }}
               {{ $disabled ? 'disabled' : '' }}
               class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-2 focus:ring-offset-0 dark:focus:ring-offset-slate-900 transition-colors {{ $hasError ? 'border-red-500' : '' }}">
    </div>
    
    @if($label)
        <div class="text-sm leading-5">
            <label for="{{ $name }}" class="font-medium text-slate-700 dark:text-slate-300 {{ $disabled ? 'opacity-50' : 'cursor-pointer' }}">
                {{ $label }}
            </label>
            
            @if($hasError)
                <p class="text-xs text-red-500 mt-1">{{ $error ?? $errors->first($name) }}</p>
            @endif
        </div>
    @endif
</div>

