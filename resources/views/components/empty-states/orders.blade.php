{{-- Empty State Premium pour Commandes --}}
<div class="flex flex-col items-center justify-center py-16 px-4">
    <div class="relative mb-6">
        {{-- Animated background circles --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-32 h-32 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full animate-pulse"></div>
        </div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-200 to-indigo-200 rounded-full animate-ping opacity-20"></div>
        </div>

        {{-- Icon --}}
        <div class="relative w-20 h-20 mx-auto bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/30 transform hover:scale-110 transition-transform duration-300">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
        </div>
    </div>

    {{-- Content --}}
    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $title ?? 'Aucune commande' }}</h3>
    <p class="text-slate-500 text-center max-w-md mb-6">
        {{ $message ?? 'Les commandes apparaîtront ici une fois que vos clients auront passé leurs premières commandes.' }}
    </p>

    {{-- Action Button --}}
    @if(isset($action))
        <a href="{{ $action['url'] ?? '#' }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transform hover:scale-105 transition-all duration-300">
            @if(isset($action['icon']))
                {!! $action['icon'] !!}
            @endif
            {{ $action['label'] ?? 'Commencer' }}
        </a>
    @endif

    {{-- Tips --}}
    @if(isset($tips))
        <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-xl max-w-md">
            <p class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Astuce
            </p>
            <p class="text-xs text-blue-700">{{ $tips }}</p>
        </div>
    @endif
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>
