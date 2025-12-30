@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => false,
    'loading' => false,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-sm',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-500',
        'accent' => 'bg-accent-500 text-white hover:bg-accent-600 focus:ring-accent-400 shadow-sm',
        'danger' => 'bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500 shadow-sm',
        'success' => 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500 shadow-sm',
        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 focus:ring-warning-400 shadow-sm',
        'outline' => 'border-2 border-primary-600 text-primary-600 hover:bg-primary-600 hover:text-white focus:ring-primary-500 bg-transparent',
        'outline-secondary' => 'border border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-500 bg-transparent',
        'ghost' => 'text-slate-600 hover:bg-slate-100 focus:ring-slate-500 bg-transparent',
        'link' => 'text-primary-600 hover:text-primary-700 hover:underline focus:ring-primary-500 bg-transparent p-0',
    ];
    
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs gap-1',
        'sm' => 'px-3 py-2 text-sm gap-1.5',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-5 py-3 text-base gap-2',
        'xl' => 'px-6 py-3.5 text-base gap-2.5',
    ];
    
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a 
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) aria-disabled="true" @endif
    >
        @if($loading)
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && !$iconRight)
            {!! $icon !!}
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconRight && !$loading)
            {!! $icon !!}
        @endif
    </a>
@else
    <button 
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif
    >
        @if($loading)
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && !$iconRight)
            {!! $icon !!}
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconRight && !$loading)
            {!! $icon !!}
        @endif
    </button>
@endif

