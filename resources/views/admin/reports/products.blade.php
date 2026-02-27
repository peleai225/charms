@extends('layouts.admin')

@section('title', 'Rapport des produits')
@section('page-title', 'Rapport des produits')

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
        
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.reports.products.export-csv', request()->query()) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
            <form method="GET" class="flex flex-wrap gap-3">
                <input type="date" name="start_date" value="{{ $startDate }}" 
                    class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <input type="date" name="end_date" value="{{ $endDate }}" 
                    class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                    Appliquer
                </button>
            </form>
        </div>
    </div>

    <!-- Stats par catégorie -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Ventes par catégorie</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <canvas id="categoryChart" height="200"></canvas>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Catégorie</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Quantité</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($categoryStats as $cat)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $cat->name }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $cat->quantity_sold }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ format_price($cat->revenue) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top produits -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Top 50 produits les plus vendus</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Catégorie</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Quantité</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Commandes</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">CA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($topProducts as $index => $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-slate-500 font-medium">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-sm">{{ $product->sku }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $product->category_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right text-slate-900 font-semibold">{{ $product->quantity_sold }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ $product->orders_count }}</td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">{{ format_price($product->revenue) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Aucune vente sur cette période
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Produits sans vente -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Produits sans vente sur la période</h3>
            <p class="text-sm text-slate-500 mt-1">Ces produits n'ont généré aucune vente durant cette période</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Stock</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Prix</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Valeur stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($noSalesProducts as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-sm">{{ $product->sku }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($product->stock_quantity <= 0)
                                    <span class="text-red-600 font-semibold">{{ $product->stock_quantity }}</span>
                                @else
                                    <span class="text-slate-900">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ format_price($product->sale_price) }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-900">{{ format_price($product->stock_quantity * $product->sale_price) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Tous les produits ont été vendus durant cette période
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($categoryStats->pluck('name')),
            datasets: [{
                data: @json($categoryStats->pluck('revenue')),
                backgroundColor: [
                    'rgba(37, 99, 235, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                ],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' F CFA';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

