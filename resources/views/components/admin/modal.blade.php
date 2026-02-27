@props([
    'id' => 'modal',
    'title' => '',
    'maxWidth' => 'max-w-lg',
    'open' => false,
])

@php
    $initialOpen = $open || $errors->any() || request('open_modal');
@endphp

<div x-data="{ open: {{ $initialOpen ? 'true' : 'false' }} }"
     x-on:open-modal.window="if ($event.detail === '{{ $id }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $id }}' || !$event.detail) open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[100] overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
         @click="open = false">
    </div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full {{ $maxWidth }} bg-white rounded-2xl shadow-xl"
             @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h2 id="modal-title" class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
                <button type="button"
                        @click="open = false"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
