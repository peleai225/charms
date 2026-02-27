@extends('layouts.admin')

@section('title', 'Rapport stock')
@section('page-title', 'Rapport du stock')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux rapports
        </a>
        <a href="{{ route('admin.reports.stock.export-csv') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm font-medium text-slate-500 mb-2">Valeur stock (coût)</p>
            <p class="text-3xl font-bold text-slate-900">{{ format_price($stockValue->cost_value ?? 0) }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ number_format($stockValue->total_units ?? 0, 0, ',', ' ') }} unités</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm font-medium text-slate-500 mb-2">Valeur stock (vente)</p>
            <p class="text-3xl font-bold text-green-600">{{ format_price($stockValue->sale_value ?? 0) }}</p>
            <p class="text-sm text-slate-500 mt-1">Marge potentielle</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm font-medium text-slate-500 mb-2">Alertes stock</p>
            <p class="text-3xl font-bold text-amber-600">{{ $outOfStock->count() + $lowStock->count() }}</p>
            <p class="text-sm text-slate-500 mt-1">produits à surveiller</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Ruptures de stock -->
        <div class="bg-white rounded-2xl shadow-sm border border-red-200">
            <div class="p-6 border-b border-red-100 flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-red-900">Ruptures de stock ({{ $outOfStock->count() }})</h3>
            </div>
            @if($outOfStock->count() > 0)
            <div class="divide-y divide-red-100 max-h-80 overflow-y-auto">
                @foreach($outOfStock as $product)
                <div class="p-4 flex items-center justify-between hover:bg-red-50">
                    <div>
                        <p class="font-medium text-slate-900">{{ $product->name }}</p>
                        <p class="text-sm text-slate-500">SKU: {{ $product->sku ?? 'N/A' }}</p>
                    </div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-sm text-blue-600 hover:underline">
                        Modifier
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-green-600">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Aucune rupture de stock
            </div>
            @endif
        </div>

        <!-- Stock faible -->
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200">
            <div class="p-6 border-b border-amber-100 flex items-center gap-3">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-amber-900">Stock faible ({{ $lowStock->count() }})</h3>
            </div>
            @if($lowStock->count() > 0)
            <div class="divide-y divide-amber-100 max-h-80 overflow-y-auto">
                @foreach($lowStock as $product)
                <div class="p-4 flex items-center justify-between hover:bg-amber-50">
                    <div>
                        <p class="font-medium text-slate-900">{{ $product->name }}</p>
                        <p class="text-sm text-slate-500">
                            Stock: <span class="font-medium text-amber-600">{{ $product->stock_quantity }}</span>
                            / Seuil: {{ $product->stock_alert_threshold }}
                        </p>
                    </div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-sm text-blue-600 hover:underline">
                        Modifier
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-green-600">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tous les stocks sont OK
            </div>
            @endif
        </div>
    </div>

    <!-- Rotation du stock -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Rotation du stock (30 derniers jours)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Vendus (30j)</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Stock actuel</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Jours de stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($stockRotation as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ Str::limit($product->name, 40) }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ $product->sold_30d }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ $product->stock_quantity }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($product->days_of_stock !== null)
                                    <span class="font-medium {{ $product->days_of_stock < 7 ? 'text-red-600' : ($product->days_of_stock < 30 ? 'text-amber-600' : 'text-green-600') }}">
                                        {{ $product->days_of_stock }} jours
                                    </span>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                Aucune donnée de vente
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

