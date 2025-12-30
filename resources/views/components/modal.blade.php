@props([
    'name' => 'modal',
    'title' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
    $maxWidthClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        'full' => 'max-w-full mx-4',
    ];
@endphp

<div 
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <!-- Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
        @if($closeable) @click="show = false" @endif
    ></div>
    
    <!-- Modal container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full {{ $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['lg'] }} bg-white rounded-2xl shadow-2xl overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            @if($title || $closeable)
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    @if($title)
                        <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                    @else
                        <div></div>
                    @endif
                    
                    @if($closeable)
                        <button 
                            @click="show = false"
                            class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif
            
            <!-- Body -->
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            @if(isset($footer))
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

