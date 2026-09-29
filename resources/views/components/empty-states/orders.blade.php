{{-- Empty State pour Commandes --}}
<div class="flex flex-col items-center justify-center py-16 px-4">
    <div class="mb-6">
        {{-- Icon --}}
        <div class="w-20 h-20 mx-auto bg-primary-600 rounded-2xl flex items-center justify-center">
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
        <a href="{{ $action['url'] ?? '#' }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
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
