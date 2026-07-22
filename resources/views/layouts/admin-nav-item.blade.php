{{-- Variables attendues: $href, $label, $icon, $active (bool), $badge (int|null), $tip (string), $fill (bool) --}}
@php
    $active = $active ?? false;
    $badge  = $badge  ?? null;
    $tip    = $tip    ?? $label;
    $fill   = $fill   ?? false;
@endphp

<div class="relative group">
    <a href="{{ $href }}"
       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors relative {{ $active ? 'nav-active text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
       :class="sidebarCollapsed ? 'justify-center' : ''">

        <svg class="w-[18px] h-[18px] flex-shrink-0 {{ $active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}"
             fill="{{ $fill ? 'currentColor' : 'none' }}"
             stroke="{{ $fill ? 'none' : 'currentColor' }}"
             viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>

        <span class="flex-1 truncate font-medium leading-none"
              x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>
            {{ $label }}
        </span>

        @if($badge)
            <span class="flex-shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none {{ $active ? 'bg-blue-200 text-blue-700' : 'bg-red-500 text-white' }}"
                  x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>
                {{ $badge }}
            </span>
        @endif
    </a>

    <div x-show="sidebarCollapsed" x-cloak
         class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none whitespace-nowrap z-50">
        {{ $tip }}
        @if($badge)
            <span class="ml-1.5 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $badge }}</span>
        @endif
    </div>
</div>
