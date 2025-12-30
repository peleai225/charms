@extends('layouts.admin')

@section('title', 'Gestion du Stock')
@section('page-title', 'Gestion du Stock')

@section('content')
<div class="space-y-6">
    <!-- Actions rapides -->
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.stock.create-movement') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau mouvement
        </a>
        <a href="{{ route('admin.stock.reception') }}" class="px-4 py-2 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            Réception fournisseur
        </a>
        <a href="{{ route('admin.stock.inventory') }}" class="px-4 py-2 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Inventaire
        </a>
        <a href="{{ route('admin.stock.alerts') }}" class="px-4 py-2 bg-amber-600 text-white font-medium rounded-xl hover:bg-amber-700 transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Alertes ({{ $stats['out_of_stock'] + $stats['low_stock'] }})
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Produits actifs</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_products'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Unités en stock</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_units'], 0, ',', ' ') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Valeur du stock</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ format_price($stats['stock_value']) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-5">
            <p class="text-sm text-slate-500">Ruptures</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['out_of_stock'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5">
            <p class="text-sm text-slate-500">Stock faible</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['low_stock'] }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Alertes -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">⚠️ Produits en alerte</h3>
                <a href="{{ route('admin.stock.alerts') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>
            @if($alertProducts->count() > 0)
            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                @foreach($alertProducts as $product)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50">
                    <div>
                        <p class="font-medium text-slate-900">{{ $product->name }}</p>
                        <p class="text-sm text-slate-500">SKU: {{ $product->sku ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full {{ $product->stock_quantity == 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $product->stock_quantity }} unités
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-green-600">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tous les stocks sont OK
            </div>
            @endif
        </div>

        <!-- Derniers mouvements -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">📦 Derniers mouvements</h3>
                <a href="{{ route('admin.stock.movements') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>
            @if($recentMovements->count() > 0)
            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                @foreach($recentMovements as $movement)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center {{ $movement->quantity > 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            @if($movement->quantity > 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            @endif
                        </span>
                        <div>
                            <p class="font-medium text-slate-900">{{ $movement->product?->name ?? 'Produit supprimé' }}</p>
                            <p class="text-xs text-slate-500">{{ $movement->reason }} • {{ $movement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="font-semibold {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-slate-500">Aucun mouvement récent</div>
            @endif
        </div>
    </div>
</div>
@endsection
