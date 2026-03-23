@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6"
     x-data="dashboardKpi()"
     x-init="init()">

    <!-- Filtres période -->
    <div class="flex items-center justify-between">
        <div class="flex gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
            <template x-for="p in periods" :key="p.value">
                <button @click="setPeriod(p.value)"
                        :class="period === p.value
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all"
                        x-text="p.label">
                </button>
            </template>
        </div>
        <div x-show="loading" class="flex items-center gap-2 text-sm text-slate-400">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Chargement...
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- CA -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500" x-text="periodLabel + ' — CA'"></p>
                    <p class="text-2xl font-bold text-slate-900 mt-1" x-text="kpi.revenue_fmt || '{{ format_price($stats['monthly_revenue']) }}'"></p>
                    <div class="flex items-center gap-1 mt-2">
                        <span :class="(kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0 ? 'text-green-500' : 'text-red-500'"
                              class="text-sm font-medium"
                              x-text="((kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0 ? '+' : '') + (kpi.growth ?? {{ $stats['revenue_growth'] }}) + '%'"></span>
                        <span class="text-slate-400 text-sm">vs période préc.</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Commandes -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Commandes</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1" x-text="kpi.orders ?? {{ $stats['today_orders'] }}"></p>
                    <p class="text-sm text-slate-400 mt-2"><span x-text="kpi.pending ?? {{ $stats['pending_orders'] }}"></span> en attente</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Clients -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total clients</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_customers'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-green-500 mt-2">+<span x-text="kpi.new_customers ?? {{ $stats['new_customers'] }}"></span> sur la période</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stock -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Valeur du stock</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ format_price($stats['stock_value']) }}</p>
                    @if($stats['out_of_stock'] > 0)
                        <p class="text-sm text-red-500 mt-2">{{ $stats['out_of_stock'] }} produit(s) en rupture</p>
                    @else
                        <p class="text-sm text-green-500 mt-2">Stock OK</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Graphique des ventes -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-slate-900" x-text="chartTitle"></h2>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        Chiffre d'affaires
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        Commandes
                    </span>
                </div>
            </div>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Top produits -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Top produits</h2>
            <div class="space-y-4">
                @forelse($topProducts as $product)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 flex-shrink-0">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-500">{{ $product->total_sold }} vendus</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 text-center py-4">Aucune vente ce mois</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Commandes récentes et alertes stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Commandes récentes -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Commandes récentes</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Voir tout →
                </a>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($recentOrders as $order)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-slate-600">
                                    {{ $order->customer ? strtoupper(substr($order->customer->first_name, 0, 1) . substr($order->customer->last_name, 0, 1)) : 'IN' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $order->order_number }}</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ format_price($order->total) }}</p>
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full
                                @switch($order->status)
                                    @case('delivered') bg-green-100 text-green-700 @break
                                    @case('cancelled') @case('refunded') bg-red-100 text-red-700 @break
                                    @case('shipped') bg-blue-100 text-blue-700 @break
                                    @case('confirmed') @case('processing') bg-cyan-100 text-cyan-700 @break
                                    @case('pending') bg-amber-100 text-amber-700 @break
                                    @default bg-slate-100 text-slate-700
                                @endswitch">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        Aucune commande
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Alertes stock -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Alertes stock</h2>
                <a href="{{ route('admin.stock.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Gérer →
                </a>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($lowStockProducts as $product)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $product->stock_quantity <= 0 ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $product->name }}</p>
                                <p class="text-xs text-slate-500">SKU: {{ $product->sku }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($product->stock_quantity <= 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                    Rupture
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                                    {{ $product->stock_quantity }} restant(s)
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        <svg class="w-12 h-12 mx-auto text-green-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Tous les stocks sont OK !
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Chart instance globale pour pouvoir la mettre à jour
let salesChartInstance = null;

function dashboardKpi() {
    return {
        period: 'month',
        loading: false,
        kpi: {},
        periods: [
            { value: 'today', label: "Aujourd'hui" },
            { value: 'week',  label: 'Cette semaine' },
            { value: 'month', label: 'Ce mois' },
        ],

        get periodLabel() {
            return this.periods.find(p => p.value === this.period)?.label ?? 'Ce mois';
        },

        get chartTitle() {
            const map = { today: 'Ventes aujourd\'hui (par heure)', week: 'Ventes des 7 derniers jours', month: 'Ventes des 30 derniers jours' };
            return map[this.period] ?? 'Ventes';
        },

        init() {
            // Initialiser le graphique avec les données PHP initiales
            this.$nextTick(() => {
                initChart(@json($salesChart['labels']), @json($salesChart['revenues']), @json($salesChart['orders']));
            });
        },

        async setPeriod(value) {
            if (this.period === value) return;
            this.period = value;
            await this.fetchStats();
        },

        async fetchStats() {
            this.loading = true;
            try {
                const res = await fetch('/api/admin/dashboard-stats?period=' + this.period, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.kpi = data;

                // Mettre à jour le graphique
                if (salesChartInstance && data.chart) {
                    salesChartInstance.data.labels = data.chart.labels;
                    salesChartInstance.data.datasets[0].data = data.chart.revenues;
                    salesChartInstance.data.datasets[1].data = data.chart.orders;
                    salesChartInstance.update('active');
                }
            } catch (e) {
                console.error('Dashboard stats error:', e);
            } finally {
                this.loading = false;
            }
        }
    };
}

function initChart(labels, revenues, orders) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    salesChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'CA (F CFA)',
                    data: revenues,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'Commandes',
                    data: orders,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                y:  { type: 'linear', display: true, position: 'left',  grid: { color: '#f1f5f9' } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } },
                x:  { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
@endsection
