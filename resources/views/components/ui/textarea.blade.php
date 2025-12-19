@props([
    'name' => '',
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'rows' => 4,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'resize' => true
])

@php
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    
    $textareaClasses = 'block w-full rounded-xl border-0 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200 text-sm px-4 py-3';
    
    if ($hasError) {
        $textareaClasses .= ' ring-2 ring-red-500 focus:ring-red-500';
    } else {
        $textareaClasses .= ' ring-1 ring-slate-200 dark:ring-slate-700';
    }
    
    if (!$resize) {
        $textareaClasses .= ' resize-none';
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
    
    <textarea 
        name="{{ $name }}" 
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except('class')->merge(['class' => $textareaClasses]) }}
    >{{ old($name, $value) }}</textarea>
    
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

