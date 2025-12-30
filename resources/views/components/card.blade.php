@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'hover' => false,
    'header' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden' . ($hover ? ' transition-all duration-300 hover:shadow-lg hover:-translate-y-1' : '')]) }}>
    @if($title || $header)
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($header)
                <div>{{ $header }}</div>
            @endif
        </div>
    @endif
    
    <div @class(['p-6' => $padding])>
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $footer }}
        </div>
    @endif
</div>

