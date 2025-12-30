@extends('layouts.admin')

@section('title', 'Rapport des clients')
@section('page-title', 'Rapport des clients')

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
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Appliquer
            </button>
        </form>
    </div>

    <!-- Stats principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Nouveaux clients</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $newCustomers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Clients actifs</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $activeCustomers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">CA moyen/client</p>
                    <p class="text-3xl font-bold text-slate-900">
                        @if($activeCustomers > 0 && $topCustomers->sum('orders_sum_total') > 0)
                            {{ format_price($topCustomers->sum('orders_sum_total') / $activeCustomers) }}
                        @else
                            {{ format_price(0) }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top clients -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Top 20 clients</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">#</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Client</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Commandes</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($topCustomers as $index => $customer)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-500 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                    </a>
                                    @if($customer->user?->email)
                                        <p class="text-sm text-slate-500">{{ $customer->user->email }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-slate-900 font-semibold">{{ $customer->orders_count }}</td>
                                <td class="px-6 py-4 text-right font-bold text-green-600">{{ format_price($customer->orders_sum_total ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    Aucun client actif sur cette période
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Répartition géographique -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Répartition géographique</h3>
            </div>
            <div class="p-6">
                <canvas id="geoChart" height="250"></canvas>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Ville</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Commandes</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($geoStats as $city)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $city->shipping_city ?? 'Non renseigné' }}</td>
                                <td class="px-6 py-4 text-right text-slate-600">{{ $city->orders_count }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">{{ format_price($city->revenue) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                                    Aucune donnée géographique disponible
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('geoChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($geoStats->pluck('shipping_city')->map(fn($v) => $v ?? 'Non renseigné')),
            datasets: [{
                label: 'CA (F CFA)',
                data: @json($geoStats->pluck('revenue')),
                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                borderRadius: 8,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('fr-FR').format(context.raw) + ' F CFA';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(value);
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

