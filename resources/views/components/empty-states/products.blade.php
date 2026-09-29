{{-- Empty State pour Produits --}}
<div class="flex flex-col items-center justify-center py-16 px-4">
    <div class="mb-6">
        {{-- Icon --}}
        <div class="w-20 h-20 mx-auto bg-success-600 rounded-2xl flex items-center justify-center">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
    </div>

    {{-- Content --}}
    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $title ?? 'Aucun produit' }}</h3>
    <p class="text-slate-500 text-center max-w-md mb-6">
        {{ $message ?? 'Commencez par ajouter vos premiers produits pour démarrer vos ventes.' }}
    </p>

    {{-- Action Button --}}
    @if(isset($action))
        <a href="{{ $action['url'] ?? '#' }}" class="inline-flex items-center gap-2 px-6 py-3 bg-success-600 hover:bg-success-700 text-white font-semibold rounded-xl transition-colors">
            @if(isset($action['icon']))
                {!! $action['icon'] !!}
            @endif
            <span>{{ $action['label'] ?? 'Ajouter un produit' }}</span>
        </a>
    @endif
</div>
