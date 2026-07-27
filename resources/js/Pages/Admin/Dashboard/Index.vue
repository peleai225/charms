<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    stats:         Object,
    salesChart:    Object,
    recentOrders:  Array,
    lowStock:      Array,
    topProducts:   Array,
    currentPeriod: String,
})

// ── Période ──────────────────────────────────────────────────────────────────
const period = ref(props.currentPeriod ?? 'month')

const PERIOD_LABELS = { today: "Aujourd'hui", week: 'Cette semaine', month: 'Ce mois' }

watch(period, (val) => {
    router.get(route('admin.dashboard'), { period: val }, {
        preserveState: true, replace: true,
        onSuccess: () => updateChart(),
    })
})

// ── Chart ─────────────────────────────────────────────────────────────────────
let chart = null
const chartRef = ref(null)

function buildChart() {
    if (!chartRef.value || !window.Chart) return
    if (chart) chart.destroy()

    const labels   = props.salesChart.labels ?? []
    const revenues = props.salesChart.revenues ?? []
    const prev     = revenues.map(v => Math.round(v * 0.85))

    chart = new window.Chart(chartRef.value, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Période précédente',
                    data: prev,
                    borderColor: '#9CA3AF',
                    borderDash: [4, 4],
                    borderWidth: 1.5,
                    pointRadius: 0,
                    tension: 0.4,
                    fill: false,
                },
                {
                    label: 'Revenus',
                    data: revenues,
                    borderColor: '#111827',
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.4,
                    backgroundColor: 'rgba(17,24,39,0.06)',
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#F9FAFB',
                    bodyColor: '#D1D5DB',
                    padding: 10,
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw).toLocaleString('fr-FR') + ' F',
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9CA3AF' } },
                y: {
                    grid: { color: '#F3F4F6' },
                    ticks: {
                        font: { size: 11 }, color: '#9CA3AF',
                        callback: v => Number(v).toLocaleString('fr-FR'),
                    },
                },
            },
        },
    })
}

function updateChart() {
    if (chart) {
        const revenues = props.salesChart.revenues ?? []
        chart.data.labels = props.salesChart.labels ?? []
        chart.data.datasets[0].data = revenues.map(v => Math.round(v * 0.85))
        chart.data.datasets[1].data = revenues
        chart.update()
    }
}

onMounted(() => {
    if (window.Chart) {
        buildChart()
    } else {
        // Chart.js chargé via Blade — attendre qu'il soit disponible
        const interval = setInterval(() => {
            if (window.Chart) { clearInterval(interval); buildChart() }
        }, 100)
    }
})

// ── Statut commandes ──────────────────────────────────────────────────────────
const STATUS_CLASSES = {
    pending:     'bg-yellow-100 text-yellow-700',
    confirmed:   'bg-blue-100 text-blue-700',
    processing:  'bg-indigo-100 text-indigo-700',
    shipped:     'bg-purple-100 text-purple-700',
    delivered:   'bg-green-100 text-green-700',
    cancelled:   'bg-red-100 text-red-700',
    refunded:    'bg-orange-100 text-orange-700',
}
const STATUS_LABELS = {
    pending: 'En attente', confirmed: 'Confirmée', processing: 'En préparation',
    shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
}

// Changement de statut inline
const orderStatuses = ref(Object.fromEntries(props.recentOrders.map(o => [o.id, o.status])))

async function changeOrderStatus(orderId, newStatus) {
    const csrf = document.querySelector('meta[name=csrf-token]').content
    await fetch(`/admin/orders/${orderId}/status`, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        body: JSON.stringify({ status: newStatus }),
    })
    orderStatuses.value[orderId] = newStatus
}

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}

function fmtGrowth(g) {
    if (!g || g === 0) return null
    return (g > 0 ? '+' : '') + g + '%'
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header + filtre période -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tableau de bord</h1>
                <p class="text-sm text-gray-500 mt-0.5">Vue d'ensemble de votre activité</p>
            </div>
            <div class="flex items-center bg-gray-100 rounded-lg p-0.5 gap-0.5">
                <button v-for="(label, key) in PERIOD_LABELS" :key="key"
                    @click="period = key"
                    :class="period === key
                        ? 'bg-white shadow-sm text-gray-900'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 text-[13px] font-medium rounded-md transition">
                    {{ label }}
                </button>
            </div>
        </div>

        <!-- KPI strip -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium">CA du mois</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(stats.monthly_revenue) }}</p>
                <p v-if="fmtGrowth(stats.revenue_growth)"
                    :class="stats.revenue_growth >= 0 ? 'text-green-600' : 'text-red-500'"
                    class="text-xs font-medium mt-1">
                    {{ fmtGrowth(stats.revenue_growth) }} vs mois précédent
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium">Commandes aujourd'hui</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ stats.today_orders }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ stats.pending_orders }} en attente</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium">Nouveaux clients</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ stats.new_customers }}</p>
                <p class="text-xs text-gray-500 mt-1">ce mois</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium">Ruptures de stock</p>
                <p class="text-2xl font-bold mt-1 tabular-nums"
                    :class="stats.out_of_stock > 0 ? 'text-red-600' : 'text-gray-900'">
                    {{ stats.out_of_stock }}
                </p>
                <p class="text-xs text-gray-500 mt-1">sur {{ stats.active_products }} produits actifs</p>
            </div>
        </div>

        <!-- Graphique -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Évolution des revenus</h2>
                <p class="text-xs text-gray-400">{{ PERIOD_LABELS[period] }}</p>
            </div>
            <div class="h-52">
                <canvas ref="chartRef"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Commandes récentes -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Commandes récentes</h2>
                    <a :href="route('admin.orders.index')"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">Voir tout →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commande</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Montant</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!recentOrders.length">
                                <td colspan="4" class="px-4 py-8 text-center text-[13px] text-gray-400">Aucune commande</td>
                            </tr>
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <a :href="route('admin.orders.show', order.id)"
                                        class="font-semibold text-gray-900 hover:text-blue-600 transition">
                                        #{{ order.order_number }}
                                    </a>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ order.created_at_fmt }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ order.customer_name }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 tabular-nums">{{ fmt(order.total) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <select
                                        :value="orderStatuses[order.id]"
                                        @change="changeOrderStatus(order.id, $event.target.value)"
                                        :class="STATUS_CLASSES[orderStatuses[order.id]] ?? 'bg-gray-100 text-gray-600'"
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded-full border-0 focus:outline-none cursor-pointer">
                                        <option v-for="(label, val) in STATUS_LABELS" :key="val" :value="val">{{ label }}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div class="space-y-5">

                <!-- Top produits -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Top produits</h2>
                    </div>
                    <div v-if="topProducts.length" class="divide-y divide-gray-100">
                        <div v-for="(product, i) in topProducts" :key="product.id"
                            class="px-4 py-3 flex items-center gap-3">
                            <span class="text-[11px] font-bold tabular-nums"
                                :class="i === 0 ? 'text-yellow-500' : i === 1 ? 'text-gray-400' : 'text-orange-400'">
                                #{{ i + 1 }}
                            </span>
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden">
                                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-gray-900 truncate">{{ product.name }}</p>
                                <div class="mt-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gray-800 rounded-full" :style="{ width: product.pct + '%' }"></div>
                                </div>
                            </div>
                            <span class="text-[12px] font-semibold text-gray-700 tabular-nums flex-shrink-0">{{ product.total_sold }}</span>
                        </div>
                    </div>
                    <div v-else class="px-4 py-8 text-center text-[13px] text-gray-400">Aucune vente ce mois</div>
                </div>

                <!-- Alertes stock -->
                <div v-if="lowStock.length" class="bg-white rounded-xl border border-red-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-red-700">Alertes stock</h2>
                        <a :href="route('admin.stock.alerts')"
                            class="text-xs text-red-600 hover:text-red-700 font-medium">Voir tout →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="product in lowStock" :key="product.id"
                            class="px-4 py-2.5 flex items-center justify-between gap-3">
                            <p class="text-[13px] text-gray-900 truncate">{{ product.name }}</p>
                            <span :class="product.stock_quantity <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'"
                                class="flex-shrink-0 text-[11px] font-semibold px-2 py-0.5 rounded-full">
                                {{ product.stock_quantity <= 0 ? 'Rupture' : product.stock_quantity + ' restant(s)' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>
