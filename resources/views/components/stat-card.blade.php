@props([
    'title' => '',
    'value' => '',
    'trend' => null,
    'trendValue' => null,
    'icon' => null,
    'iconBg' => 'bg-primary-100',
    'iconColor' => 'text-primary-600',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl p-6 shadow-sm border border-slate-100']) }}>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $value }}</p>
            
            @if($trend !== null || $trendValue)
                <div class="flex items-center gap-1.5 mt-2">
                    @if($trend === 'up')
                        <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span class="text-sm font-medium text-success-600">{{ $trendValue }}</span>
                    @elseif($trend === 'down')
                        <svg class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                        <span class="text-sm font-medium text-danger-600">{{ $trendValue }}</span>
                    @else
                        <span class="text-sm text-slate-500">{{ $trendValue }}</span>
                    @endif
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="w-12 h-12 rounded-xl {{ $iconBg }} {{ $iconColor }} flex items-center justify-center flex-shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
    
    @if($slot->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-slate-100">
            {{ $slot }}
        </div>
    @endif
</div>

