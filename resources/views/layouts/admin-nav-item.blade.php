{{-- Variables: $href, $label, $match (route pattern), $icon (SVG path d=""), $badge (int|null) --}}
@php
    $isActive = request()->routeIs($match);
    $badge    = $badge ?? null;
@endphp

<div class="relative group mx-2">
    <a href="{{ $href }}"
       class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors relative
              {{ $isActive
                  ? 'nav-active bg-gray-50 text-gray-900'
                  : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}"
       :class="sidebarCollapsed ? 'justify-center px-0' : ''">

        <svg class="w-[16px] h-[16px] flex-shrink-0 {{ $isActive ? 'text-gray-800' : 'text-gray-400 group-hover:text-gray-600' }}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
        </svg>

        <span class="flex-1 truncate text-[13px] font-medium leading-none"
              x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>
            {{ $label }}
        </span>

        @if($badge)
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full leading-none flex-shrink-0
                         {{ $isActive ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500' }}"
                  x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>
                {{ $badge }}
            </span>
        @endif
    </a>

    {{-- Tooltip en mode réduit --}}
    <div x-show="sidebarCollapsed" x-cloak
         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-2.5 py-1.5
                bg-gray-800 text-white text-[12px] rounded-lg shadow-lg
                opacity-0 group-hover:opacity-100 transition-opacity duration-150
                pointer-events-none whitespace-nowrap z-50">
        {{ $label }}
        @if($badge)
            <span class="ml-1 bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $badge }}</span>
        @endif
        <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-800"></div>
    </div>
</div>
