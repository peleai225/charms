@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" x-init="init()" class="space-y-5">

    {{-- ── KPI STRIP (style WeEats — stats inline avec dividers) ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">

            {{-- CA --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Chiffre d'affaires</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none"
                   x-text="kpi.revenue_fmt || '{{ format_price($stats['monthly_revenue']) }}'"></p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span :class="(kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0
                                  ? 'text-green-600 bg-green-50'
                                  : 'text-red-500 bg-red-50'"
                          class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="(kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/>
                            <path x-show="(kpi.growth ?? {{ $stats['revenue_growth'] }}) < 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7-7-7"/>
                        </svg>
                        <span x-text="Math.abs(kpi.growth ?? {{ $stats['revenue_growth'] }}) + '%'"></span>
                    </span>
                    <span class="text-[11px] text-gray-400">/Month</span>
                </div>
            </div>

            {{-- Commandes --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Total Commandes</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none"
                   x-text="kpi.orders ?? {{ $stats['today_orders'] }}"></p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded bg-amber-50 text-amber-600">
                        <span x-text="(kpi.pending ?? {{ $stats['pending_orders'] }}) + ' en attente'"></span>
                    </span>
                </div>
            </div>

            {{-- Clients --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Nouveaux Clients</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none"
                   x-text="kpi.new_customers ?? {{ $stats['new_customers'] }}"></p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded
                                 {{ $stats['new_customers'] >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/>
                        </svg>
                        {{ $stats['new_customers'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">/Month</span>
                </div>
            </div>

            {{-- Stock --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Valeur du Stock</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none">{{ format_price($stats['stock_value']) }}</p>
                <div class="flex items-center gap-1.5 mt-2">
                    @if($stats['out_of_stock'] > 0)
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded bg-red-50 text-red-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7-7-7"/></svg>
                            {{ $stats['out_of_stock'] }} rupture(s)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded bg-green-50 text-green-600">
                            Stock OK
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── ANALYTICS + TOP PRODUITS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Order Analytics --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <p class="text-[14px] font-semibold text-gray-900">Order Analytics</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-[22px] font-bold text-gray-900"
                              x-text="kpi.revenue_fmt || '{{ format_price($stats['monthly_revenue']) }}'"></span>
                        <span :class="(kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0
                                      ? 'text-green-600 bg-green-50'
                                      : 'text-red-500 bg-red-50'"
                              class="text-[11px] font-semibold px-1.5 py-0.5 rounded"
                              x-text="'+' + (kpi.growth ?? {{ $stats['revenue_growth'] }}) + '%'"></span>
                        <span class="text-[11px] text-gray-400" x-text="periodLabel"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-lg p-0.5">
                        <template x-for="p in periods" :key="p.value">
                            <button @click="setPeriod(p.value)"
                                    :class="period === p.value ? 'bg-white shadow-sm text-gray-800' : 'text-gray-400 hover:text-gray-600'"
                                    class="px-3 py-1 rounded-md text-[11px] font-semibold transition-all"
                                    x-text="p.label">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mb-4 text-[11px] text-gray-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                    Mois précédent
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-800 inline-block"></span>
                    Ce mois
                </span>
                <div x-show="loading" class="ml-auto flex items-center gap-1 text-gray-400">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>
            <canvas id="salesChart" height="110"></canvas>
        </div>

        {{-- Top produits --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-[14px] font-semibold text-gray-900 mb-4">Top Produits</p>
            <div class="space-y-3">
                @forelse($topProducts as $product)
                    @php
                        $maxSold = $topProducts->first()->total_sold ?? 1;
                        $pct = $maxSold > 0 ? round(($product->total_sold / $maxSold) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold flex-shrink-0
                                    {{ $loop->iteration === 1 ? 'bg-amber-100 text-amber-600' : ($loop->iteration === 2 ? 'bg-gray-100 text-gray-500' : 'bg-orange-100 text-orange-500') }}">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-medium text-gray-800 truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gray-800" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 whitespace-nowrap flex-shrink-0">{{ $product->total_sold }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-[12px] text-gray-400 text-center py-6">Aucune vente</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── ORDER ACTIVITIES (table style Uxerflow) ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-[14px] font-semibold text-gray-900">Order Activities</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Suivez les activités récentes</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 border border-gray-200 rounded-lg px-3 py-1.5 text-[12px] text-gray-600 cursor-pointer hover:bg-gray-50 transition-colors">
                    <span>Status: Tous</span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="flex items-center gap-1.5 border border-gray-200 rounded-lg px-3 py-1.5 text-[12px] text-gray-600 cursor-pointer hover:bg-gray-50 transition-colors">
                    <span>Aujourd'hui</span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <a href="{{ route('admin.orders.index') }}"
                   class="flex items-center justify-center w-8 h-8 border border-gray-200 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" class="w-3.5 h-3.5 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                                Order ID
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                                Client
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                                Date & Heure
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-right">
                            <span class="text-[11px] font-semibold text-gray-500">Montant</span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500">Statut</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="text-[12px] font-medium text-gray-800 hover:text-orange-500 transition-colors">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[12px] text-gray-700">
                                    {{ $order->customer
                                        ? $order->customer->first_name . ' ' . $order->customer->last_name
                                        : 'Client inconnu' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[12px] text-gray-500">
                                    {{ $order->created_at->format('d M Y à H:i') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-[12px] font-semibold text-gray-800">
                                    {{ format_price($order->total) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusMap = [
                                        'delivered'  => ['bg-green-50 text-green-600',  'Livré'],
                                        'shipped'    => ['bg-blue-50 text-blue-600',    'Expédié'],
                                        'confirmed'  => ['bg-cyan-50 text-cyan-600',    'Confirmé'],
                                        'processing' => ['bg-cyan-50 text-cyan-600',    'En cours'],
                                        'pending'    => ['bg-amber-50 text-amber-600',  'En attente'],
                                        'cancelled'  => ['bg-red-50 text-red-500',      'Annulé'],
                                        'refunded'   => ['bg-red-50 text-red-500',      'Remboursé'],
                                    ];
                                    [$cls, $lbl] = $statusMap[$order->status] ?? ['bg-gray-100 text-gray-500', $order->status_label];
                                @endphp
                                <span class="text-[11px] font-semibold px-2 py-1 rounded-md {{ $cls }}">
                                    {{ $lbl }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-[12px] text-gray-400">
                                Aucune commande récente
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-[11px] text-gray-400">{{ count($recentOrders) }} commandes affichées</span>
            <a href="{{ route('admin.orders.index') }}"
               class="text-[12px] text-orange-500 hover:text-orange-600 font-semibold">
                Voir toutes les commandes →
            </a>
        </div>
    </div>

    {{-- ── ALERTES STOCK ── --}}
    @if($lowStockProducts->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-[14px] font-semibold text-gray-900">Alertes Stock</p>
            <a href="{{ route('admin.stock.alerts') }}" class="text-[12px] text-orange-500 hover:text-orange-600 font-semibold">Gérer →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($lowStockProducts as $product)
                <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="w-7 h-7 rounded-lg {{ $product->stock_quantity <= 0 ? 'bg-red-50' : 'bg-amber-50' }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 {{ $product->stock_quantity <= 0 ? 'text-red-400' : 'text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-medium text-gray-800 truncate">{{ $product->name }}</p>
                        <p class="text-[11px] text-gray-400">SKU: {{ $product->sku }}</p>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md flex-shrink-0
                                 {{ $product->stock_quantity <= 0 ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600' }}">
                        {{ $product->stock_quantity <= 0 ? 'Rupture' : $product->stock_quantity . ' restant(s)' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function dashboardApp() {
    return {
        period: 'month',
        loading: false,
        kpi: {},
        periods: [
            { value: 'today', label: "Auj." },
            { value: 'week',  label: '7j' },
            { value: 'month', label: 'Mois' },
        ],
        get periodLabel() {
            const map = { today: "aujourd'hui", week: 'cette semaine', month: 'ce mois' };
            return map[this.period];
        },
        init() {
            this.$nextTick(() => {
                initSalesChart(
                    @json($salesChart['labels']),
                    @json($salesChart['revenues']),
                    @json($salesChart['orders'])
                );
            });
        },
        async setPeriod(v) {
            if (this.period === v) return;
            this.period = v;
            this.loading = true;
            try {
                const r = await fetch('/api/admin/dashboard-stats?period=' + v, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) throw new Error();
                const d = await r.json();
                this.kpi = d;
                if (window._salesChart && d.chart) {
                    window._salesChart.data.labels = d.chart.labels;
                    window._salesChart.data.datasets[0].data = d.chart.revenues;
                    window._salesChart.data.datasets[1].data = d.chart.orders;
                    window._salesChart.update('active');
                }
            } catch(e) {}
            this.loading = false;
        }
    };
}

function initSalesChart(labels, revenues, orders) {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;
    if (window._salesChart) { window._salesChart.destroy(); window._salesChart = null; }

    window._salesChart = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Mois précédent',
                    data: revenues.map(v => v * 0.85),
                    borderColor: '#d1d5db',
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    tension: 0.4,
                    pointRadius: 0,
                    fill: false,
                    yAxisID: 'y',
                },
                {
                    label: 'Ce mois',
                    data: revenues,
                    borderColor: '#111827',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#111827',
                    fill: false,
                    yAxisID: 'y',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f9fafb',
                    bodyColor: '#d1d5db',
                    padding: 10,
                    cornerRadius: 8,
                    borderWidth: 0,
                }
            },
            scales: {
                y: {
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: { color: '#9ca3af', font: { size: 10 }, maxTicksLimit: 5 }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9ca3af', font: { size: 10 } }
                }
            }
        }
    });
}
</script>
@endpush
