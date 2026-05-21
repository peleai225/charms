{{-- Empty State Generic Premium --}}
<div class="flex flex-col items-center justify-center py-16 px-4">
    <div class="relative mb-6">
        {{-- Animated background circles --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-32 h-32 bg-gradient-to-br {{ $bgColor ?? 'from-slate-100 to-slate-200' }} rounded-full animate-pulse"></div>
        </div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-24 h-24 bg-gradient-to-br {{ $bgColorLight ?? 'from-slate-200 to-slate-300' }} rounded-full animate-ping opacity-20"></div>
        </div>

        {{-- Icon --}}
        <div class="relative w-20 h-20 mx-auto bg-gradient-to-br {{ $iconBg ?? 'from-slate-500 to-slate-600' }} rounded-2xl flex items-center justify-center shadow-xl {{ $shadowColor ?? 'shadow-slate-500/30' }} transform hover:scale-110 transition-transform duration-300">
            @if(isset($icon))
                {!! $icon !!}
            @else
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            @endif
        </div>
    </div>

    {{-- Content --}}
    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $title ?? 'Aucun élément' }}</h3>
    <p class="text-slate-500 text-center max-w-md mb-6">
        {{ $message ?? 'Aucun élément à afficher pour le moment.' }}
    </p>

    {{-- Action Button --}}
    @if(isset($action))
        <a href="{{ $action['url'] ?? '#' }}"
           class="inline-flex items-center gap-2 px-6 py-3 {{ $buttonClass ?? 'bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 shadow-slate-500/30' }} text-white font-semibold rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300">
            @if(isset($action['icon']))
                {!! $action['icon'] !!}
            @endif
            <span>{{ $action['label'] ?? 'Ajouter' }}</span>
        </a>
    @endif
</div>
