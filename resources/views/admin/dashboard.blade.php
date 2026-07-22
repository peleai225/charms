@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6" x-data="dashboardKpi()" x-init="init()">

    {{-- ── KPI CARDS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- CA --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"
                   x-text="periodLabel + ' — CA'"></p>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 leading-none"
               x-text="kpi.revenue_fmt || '{{ format_price($stats['monthly_revenue']) }}'"></p>
            <div class="flex items-center gap-1.5 mt-2">
                <span :class="(kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0
                              ? 'bg-green-50 text-green-600'
                              : 'bg-red-50 text-red-600'"
                      class="text-[11px] font-semibold px-2 py-0.5 rounded-full leading-none"
                      x-text="((kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0 ? '+' : '') + (kpi.growth ?? {{ $stats['revenue_growth'] }}) + '%'">
                </span>
                <span class="text-[11px] text-gray-400">vs mois préc.</span>
            </div>
        </div>

        {{-- Commandes --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Commandes</p>
                <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 leading-none"
               x-text="kpi.orders ?? {{ $stats['today_orders'] }}"></p>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="text-[11px] font-semibold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full leading-none"
                      x-text="(kpi.pending ?? {{ $stats['pending_orders'] }}) + ' en attente'"></span>
            </div>
        </div>

        {{-- Clients --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Clients</p>
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($stats['total_customers'], 0, ',', ' ') }}</p>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="text-[11px] font-semibold bg-green-50 text-green-600 px-2 py-0.5 rounded-full leading-none">
                    +<span x-text="kpi.new_customers ?? {{ $stats['new_customers'] }}"></span> nouveaux
                </span>
                <span class="text-[11px] text-gray-400">sur la période</span>
            </div>
        </div>

        {{-- Stock --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Valeur stock</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ format_price($stats['stock_value']) }}</p>
            <div class="flex items-center gap-1.5 mt-2">
                @if($stats['out_of_stock'] > 0)
                    <span class="text-[11px] font-semibold bg-red-50 text-red-600 px-2 py-0.5 rounded-full leading-none">
                        {{ $stats['out_of_stock'] }} rupture(s)
                    </span>
                @else
                    <span class="text-[11px] font-semibold bg-green-50 text-green-600 px-2 py-0.5 rounded-full leading-none">Stock OK</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── FILTRES PÉRIODE + ACTIONS RAPIDES ── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
            <template x-for="p in periods" :key="p.value">
                <button @click="setPeriod(p.value)"
                        :class="period === p.value
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'"
                        class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all"
                        x-text="p.label">
                </button>
            </template>
        </div>

        <div class="flex items-center gap-2">
            <div x-show="loading" class="flex items-center gap-1.5 text-xs text-gray-400">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Chargement…
            </div>
            @if(in_array(auth()->user()->role, ['admin', 'manager']))
            <a href="{{ route('admin.products.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau produit
            </a>
            @endif
            <a href="{{ route('admin.orders.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg border border-gray-200 transition-colors shadow-sm">
                Voir commandes
            </a>
        </div>
    </div>

    {{-- ── GRAPHIQUE + TOP PRODUITS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Graphique des ventes --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="text-sm font-semibold text-gray-900" x-text="chartTitle"></p>
                    <p class="text-xs text-gray-400 mt-0.5">Chiffre d'affaires & commandes</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>CA
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"></span>Commandes
                    </span>
                </div>
            </div>
            <canvas id="salesChart" height="120"></canvas>
        </div>

        {{-- Top produits --}}
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-900 mb-4">Top produits</p>
            <div class="space-y-3">
                @forelse($topProducts as $product)
                    @php
                        $maxSold = $topProducts->first()->total_sold ?? 1;
                        $pct = $maxSold > 0 ? round(($product->total_sold / $maxSold) * 100) : 0;
                        $rankColors = ['bg-amber-400','bg-gray-300','bg-orange-300'];
                        $rankColor = $rankColors[$loop->index] ?? 'bg-gray-100';
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full {{ $rankColor }} flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-800 truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $product->total_sold }} ventes</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">Aucune vente ce mois</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── COMMANDES RÉCENTES + ALERTES STOCK ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Commandes récentes --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-900">Commandes récentes</p>
                <a href="{{ route('admin.orders.index') }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">Voir tout →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}"
                       class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 text-xs font-bold text-gray-600">
                            {{ $order->customer ? strtoupper(substr($order->customer->first_name, 0, 1) . substr($order->customer->last_name, 0, 1)) : 'IN' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-sm font-semibold text-gray-800">{{ format_price($order->total) }}</span>
                            @php
                                $statusClasses = [
                                    'delivered'  => 'bg-green-50 text-green-700',
                                    'shipped'    => 'bg-blue-50 text-blue-700',
                                    'confirmed'  => 'bg-cyan-50 text-cyan-700',
                                    'processing' => 'bg-cyan-50 text-cyan-700',
                                    'pending'    => 'bg-amber-50 text-amber-700',
                                    'cancelled'  => 'bg-red-50 text-red-700',
                                    'refunded'   => 'bg-red-50 text-red-700',
                                ];
                            @endphp
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-400">Aucune commande</div>
                @endforelse
            </div>
        </div>

        {{-- Alertes stock --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-900">Alertes stock</p>
                <a href="{{ route('admin.stock.alerts') }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">Gérer →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 {{ $product->stock_quantity <= 0 ? 'text-red-500' : 'text-amber-500' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">SKU: {{ $product->sku }}</p>
                        </div>
                        @if($product->stock_quantity <= 0)
                            <span class="text-[11px] font-semibold bg-red-50 text-red-600 px-2 py-0.5 rounded-full flex-shrink-0">Rupture</span>
                        @else
                            <span class="text-[11px] font-semibold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full flex-shrink-0">
                                {{ $product->stock_quantity }} restant(s)
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-10 flex flex-col items-center text-gray-400">
                        <svg class="w-10 h-10 text-green-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Tous les stocks sont OK</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
if (!window.salesChartInstance) window.salesChartInstance = null;

function dashboardKpi() {
    return {
        period: 'month',
        loading: false,
        kpi: {},
        periods: [
            { value: 'today', label: "Aujourd'hui" },
            { value: 'week',  label: '7 jours' },
            { value: 'month', label: 'Ce mois' },
        ],
        get periodLabel() {
            return this.periods.find(p => p.value === this.period)?.label ?? 'Ce mois';
        },
        get chartTitle() {
            const map = { today: 'Ventes aujourd\'hui (par heure)', week: 'Ventes — 7 jours', month: 'Ventes — 30 jours' };
            return map[this.period] ?? 'Ventes';
        },
        init() {
            this.$nextTick(() => {
                initSalesChart(@json($salesChart['labels']), @json($salesChart['revenues']), @json($salesChart['orders']));
            });
        },
        async setPeriod(value) {
            if (this.period === value) return;
            this.period = value;
            this.loading = true;
            try {
                const r = await fetch('/api/admin/dashboard-stats?period=' + value, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const d = await r.json();
                this.kpi = d;
                if (window.salesChartInstance && d.chart) {
                    window.salesChartInstance.data.labels = d.chart.labels;
                    window.salesChartInstance.data.datasets[0].data = d.chart.revenues;
                    window.salesChartInstance.data.datasets[1].data = d.chart.orders;
                    window.salesChartInstance.update('active');
                }
            } catch(e) { console.error(e); }
            this.loading = false;
        }
    };
}

function initSalesChart(labels, revenues, orders) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;
    if (window.salesChartInstance) { window.salesChartInstance.destroy(); window.salesChartInstance = null; }

    window.salesChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'CA',
                    data: revenues,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,.06)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    yAxisID: 'y',
                },
                {
                    label: 'Commandes',
                    data: orders,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,.06)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    titleColor: '#111827',
                    bodyColor: '#6b7280',
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                y:  { type: 'linear', display: true, position: 'left',  grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
                x:  { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } }
            }
        }
    });
}
</script>
@endpush
