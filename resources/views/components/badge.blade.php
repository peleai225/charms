@props([
    'variant' => 'primary',
    'size' => 'md',
    'dot' => false,
    'removable' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary-100 text-primary-800',
        'secondary' => 'bg-slate-100 text-slate-800',
        'success' => 'bg-success-50 text-success-700',
        'warning' => 'bg-warning-50 text-warning-700',
        'danger' => 'bg-danger-50 text-danger-700',
        'info' => 'bg-info-50 text-info-700',
        'dark' => 'bg-slate-800 text-white',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];
    
    $dotColors = [
        'primary' => 'bg-primary-500',
        'secondary' => 'bg-slate-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-500',
        'info' => 'bg-info-500',
        'dark' => 'bg-white',
    ];
    
    $classes = 'inline-flex items-center font-medium rounded-full ' . 
               ($variants[$variant] ?? $variants['primary']) . ' ' . 
               ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $dotColors[$variant] ?? $dotColors['primary'] }}"></span>
    @endif
    
    {{ $slot }}
    
    @if($removable)
        <button type="button" class="ml-1.5 -mr-1 hover:opacity-75 transition-opacity">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</span>

