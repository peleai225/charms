@extends('layouts.admin')

@section('title', 'Rapport des ventes')
@section('page-title', 'Rapport des ventes')

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
        
        <!-- Filtres -->
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="date" name="start_date" value="{{ $startDate }}" 
                class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <input type="date" name="end_date" value="{{ $endDate }}" 
                class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <select name="group_by" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="day" {{ $groupBy === 'day' ? 'selected' : '' }}>Par jour</option>
                <option value="week" {{ $groupBy === 'week' ? 'selected' : '' }}>Par semaine</option>
                <option value="month" {{ $groupBy === 'month' ? 'selected' : '' }}>Par mois</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Appliquer
            </button>
        </form>
    </div>

    <!-- Stats principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-500">Chiffre d'affaires</p>
                @if($comparison['revenue']['direction'] === 'up')
                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
                        +{{ $comparison['revenue']['value'] }}%
                    </span>
                @elseif($comparison['revenue']['direction'] === 'down')
                    <span class="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded-full">
                        -{{ $comparison['revenue']['value'] }}%
                    </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ format_price($totals['revenue']) }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-500">Commandes</p>
                @if($comparison['orders']['direction'] === 'up')
                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
                        +{{ $comparison['orders']['value'] }}%
                    </span>
                @elseif($comparison['orders']['direction'] === 'down')
                    <span class="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded-full">
                        -{{ $comparison['orders']['value'] }}%
                    </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $totals['orders'] }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm font-medium text-slate-500 mb-2">Panier moyen</p>
            <p class="text-3xl font-bold text-slate-900">{{ format_price($totals['average']) }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm font-medium text-slate-500 mb-2">Réductions</p>
            <p class="text-3xl font-bold text-red-600">{{ format_price($totals['discounts']) }}</p>
        </div>
    </div>

    <!-- Graphique -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Évolution des ventes</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    <!-- Tableau détaillé -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Détail par période</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Période</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Commandes</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">CA</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Panier moyen</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Réductions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($salesData as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $row->period }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ $row->orders_count }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-900">{{ format_price($row->revenue) }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ format_price($row->average_order) }}</td>
                            <td class="px-6 py-4 text-right text-red-600">{{ format_price($row->discounts) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Aucune donnée pour cette période
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($salesData->count() > 0)
                <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                    <tr>
                        <td class="px-6 py-4 font-bold text-slate-900">TOTAL</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ $totals['orders'] }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($totals['revenue']) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($totals['average']) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">{{ format_price($totals['discounts']) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($salesData->pluck('period')),
            datasets: [{
                label: 'CA (F CFA)',
                data: @json($salesData->pluck('revenue')),
                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                borderRadius: 8,
            }, {
                label: 'Commandes',
                data: @json($salesData->pluck('orders_count')),
                type: 'line',
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'transparent',
                yAxisID: 'y1',
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(value);
                        }
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
</script>
@endpush
@endsection

