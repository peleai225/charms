@props([
    'type' => 'text',
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'helper' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'icon' => null,
    'iconRight' => null,
])

@php
    $inputId = $id ?? $name ?? Str::random(8);
    $hasError = $error || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    
    $inputClasses = 'w-full px-4 py-2.5 border rounded-lg text-slate-900 placeholder-slate-400 
                     focus:outline-none focus:ring-2 focus:border-transparent transition-all duration-200';
    
    if ($hasError) {
        $inputClasses .= ' border-danger-500 focus:ring-danger-500';
    } else {
        $inputClasses .= ' border-slate-300 focus:ring-primary-500';
    }
    
    if ($icon) {
        $inputClasses .= ' pl-11';
    }
    
    if ($iconRight) {
        $inputClasses .= ' pr-11';
    }
    
    if ($disabled) {
        $inputClasses .= ' bg-slate-100 cursor-not-allowed';
    }
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                {!! $icon !!}
            </div>
        @endif
        
        <input 
            type="{{ $type }}"
            @if($name) name="{{ $name }}" @endif
            id="{{ $inputId }}"
            @if($value) value="{{ $value }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge(['class' => $inputClasses]) }}
        >
        
        @if($iconRight)
            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                {!! $iconRight !!}
            </div>
        @endif
    </div>
    
    @if($helper && !$hasError)
        <p class="form-helper">{{ $helper }}</p>
    @endif
    
    @if($hasError)
        <p class="form-error">{{ $errorMessage }}</p>
    @endif
</div>

