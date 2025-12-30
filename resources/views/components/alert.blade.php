@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $variants = [
        'success' => [
            'container' => 'bg-success-50 border-success-200 text-success-800',
            'icon' => 'text-success-500',
            'iconPath' => 'M5 13l4 4L19 7',
        ],
        'warning' => [
            'container' => 'bg-warning-50 border-warning-200 text-warning-800',
            'icon' => 'text-warning-500',
            'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'danger' => [
            'container' => 'bg-danger-50 border-danger-200 text-danger-800',
            'icon' => 'text-danger-500',
            'iconPath' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'info' => [
            'container' => 'bg-info-50 border-info-200 text-info-800',
            'icon' => 'text-info-500',
            'iconPath' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];
    
    $config = $variants[$variant] ?? $variants['info'];
@endphp

<div 
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    {{ $attributes->merge(['class' => 'rounded-lg border px-4 py-3 ' . $config['container']]) }}
>
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $config['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['iconPath'] }}"/>
        </svg>
        
        <div class="flex-1">
            @if($title)
                <h4 class="font-semibold mb-1">{{ $title }}</h4>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>
        
        @if($dismissible)
            <button 
                @click="show = false"
                class="p-1 opacity-50 hover:opacity-100 transition-opacity"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>

