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
                <h3 class="text-lg font-semibold text-slate-900">
                    <svg class="w-5 h-5 inline-block text-amber-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Produits en alerte
                </h3>
                <a href="{{ route('admin.stock.alerts') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>

            {{-- Desktop Table --}}
            @if($alertProducts->count() > 0)
            <div class="hidden md:block overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100/50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($alertProducts as $product)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-3">
                                <p class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ $product->name }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm text-slate-500 font-mono">{{ $product->sku ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-3">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-50 text-red-600 ring-1 ring-red-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Rupture
                                    </span>
                                @elseif($product->stock_quantity <= ($product->stock_alert_threshold ?? 5))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $product->stock_quantity }} unités
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-50 text-green-600 ring-1 ring-green-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        {{ $product->stock_quantity }} unités
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.stock.create-movement') }}?product_id={{ $product->id }}" class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Ajouter du stock">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-slate-100 max-h-80 overflow-y-auto">
                @foreach($alertProducts as $product)
                <div class="p-4 hover:bg-blue-50/30 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">SKU: {{ $product->sku ?? 'N/A' }}</p>
                        </div>
                        <div class="flex items-center gap-2 ml-3">
                            @if($product->stock_quantity <= 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-50 text-red-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Rupture
                                </span>
                            @elseif($product->stock_quantity <= ($product->stock_alert_threshold ?? 5))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-50 text-amber-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ $product->stock_quantity }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-50 text-green-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    {{ $product->stock_quantity }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Modifier</a>
                        <span class="text-slate-300">|</span>
                        <a href="{{ route('admin.stock.create-movement') }}?product_id={{ $product->id }}" class="text-xs text-green-600 hover:text-green-700 font-medium">Ajouter stock</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="font-semibold text-green-700">Tous les stocks sont OK</p>
                <p class="text-sm text-slate-500 mt-1">Aucun produit n'est en rupture ou en stock faible.</p>
            </div>
            @endif
        </div>

        <!-- Derniers mouvements -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">
                    <svg class="w-5 h-5 inline-block text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    Derniers mouvements
                </h3>
                <a href="{{ route('admin.stock.movements') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>

            {{-- Desktop Table --}}
            @if($recentMovements->count() > 0)
            <div class="hidden md:block overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100/50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Raison</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Quantité</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentMovements as $movement)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    @if($movement->quantity > 0)
                                        <span class="w-7 h-7 rounded-full flex items-center justify-center bg-green-100 text-green-600 flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        </span>
                                    @else
                                        <span class="w-7 h-7 rounded-full flex items-center justify-center bg-red-100 text-red-600 flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        </span>
                                    @endif
                                    <p class="font-medium text-slate-900 text-sm group-hover:text-blue-600 transition-colors truncate">{{ $movement->product?->name ?? 'Produit supprimé' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm text-slate-500">{{ $movement->reason }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-xs text-slate-400">{{ $movement->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if($movement->quantity > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg bg-green-50 text-green-600 ring-1 ring-green-100">
                                        +{{ $movement->quantity }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg bg-red-50 text-red-600 ring-1 ring-red-100">
                                        {{ $movement->quantity }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-slate-100 max-h-80 overflow-y-auto">
                @foreach($recentMovements as $movement)
                <div class="p-4 hover:bg-blue-50/30 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            @if($movement->quantity > 0)
                                <span class="w-8 h-8 rounded-full flex items-center justify-center bg-green-100 text-green-600 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                </span>
                            @else
                                <span class="w-8 h-8 rounded-full flex items-center justify-center bg-red-100 text-red-600 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                </span>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900 text-sm truncate">{{ $movement->product?->name ?? 'Produit supprimé' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $movement->reason }} · {{ $movement->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="ml-3 flex-shrink-0">
                            @if($movement->quantity > 0)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full bg-green-50 text-green-600">
                                    +{{ $movement->quantity }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full bg-red-50 text-red-600">
                                    {{ $movement->quantity }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <p class="font-semibold text-slate-700">Aucun mouvement récent</p>
                <p class="text-sm text-slate-500 mt-1">Les mouvements de stock apparaîtront ici.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
