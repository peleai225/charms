<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    salesData:  Array,
    totals:     Object,
    comparison: Object,
    chartData:  Object,
    filters:    Object,
})

const startDate = ref(props.filters?.start_date ?? '')
const endDate   = ref(props.filters?.end_date ?? '')
const groupBy   = ref(props.filters?.group_by ?? 'day')

let debounce = null
watch([startDate, endDate, groupBy], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => apply(), 400)
})

function apply() {
    router.get(route('admin.reports.sales'), {
        start_date: startDate.value,
        end_date:   endDate.value,
        group_by:   groupBy.value,
    }, { preserveState: true, replace: true, onSuccess: () => updateChart() })
}

// ── Chart ─────────────────────────────────────────────────────────────────────
let chart = null
const chartRef = ref(null)

function buildChart() {
    if (!chartRef.value || !window.Chart) return
    if (chart) chart.destroy()

    chart = new window.Chart(chartRef.value, {
        type: 'line',
        data: {
            labels: props.chartData.labels ?? [],
            datasets: [
                {
                    label: 'Revenus',
                    data: props.chartData.revenues ?? [],
                    borderColor: '#111827',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: 0.4,
                    backgroundColor: 'rgba(17,24,39,0.06)',
                    fill: true,
                    yAxisID: 'yRev',
                },
                {
                    label: 'Commandes',
                    data: props.chartData.orderCounts ?? [],
                    borderColor: '#2563EB',
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    pointRadius: 3,
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'yOrd',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#F9FAFB',
                    bodyColor: '#D1D5DB',
                    padding: 10,
                    callbacks: {
                        label: ctx => ctx.datasetIndex === 0
                            ? ' ' + Number(ctx.raw).toLocaleString('fr-FR') + ' F'
                            : ' ' + ctx.raw + ' commandes',
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9CA3AF' } },
                yRev: {
                    position: 'left',
                    grid: { color: '#F3F4F6' },
                    ticks: { font: { size: 11 }, color: '#9CA3AF', callback: v => Number(v).toLocaleString('fr-FR') },
                },
                yOrd: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { font: { size: 11 }, color: '#2563EB' },
                },
            },
        },
    })
}

function updateChart() {
    if (chart) {
        chart.data.labels = props.chartData.labels ?? []
        chart.data.datasets[0].data = props.chartData.revenues ?? []
        chart.data.datasets[1].data = props.chartData.orderCounts ?? []
        chart.update()
    }
}

onMounted(() => {
    if (window.Chart) { buildChart() }
    else {
        const t = setInterval(() => { if (window.Chart) { clearInterval(t); buildChart() } }, 100)
    }
})

function fmt(n) { return Number(n ?? 0).toLocaleString('fr-FR') + ' F' }
function fmtN(n) { return Number(n ?? 0).toLocaleString('fr-FR') }

function growthClass(dir) {
    return dir === 'up' ? 'text-green-600' : dir === 'down' ? 'text-red-500' : 'text-gray-400'
}
function growthArrow(dir) {
    return dir === 'up' ? '↑' : dir === 'down' ? '↓' : '—'
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header + filtres -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Rapport des ventes</h1>
                <p class="text-sm text-gray-500 mt-0.5">Analyse de votre chiffre d'affaires</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input v-model="startDate" type="date"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-gray-400 text-sm">→</span>
                <input v-model="endDate" type="date"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select v-model="groupBy"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="day">Par jour</option>
                    <option value="week">Par semaine</option>
                    <option value="month">Par mois</option>
                </select>
                <a :href="route('admin.reports.sales.export-csv', { start_date: startDate, end_date: endDate })"
                    class="h-9 px-4 flex items-center gap-1.5 border border-gray-200 text-[13px] text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- KPI totaux + comparaison -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Chiffre d'affaires</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(totals.revenue) }}</p>
                <p v-if="comparison.revenue" :class="growthClass(comparison.revenue.direction)" class="text-xs font-medium mt-1">
                    {{ growthArrow(comparison.revenue.direction) }} {{ comparison.revenue.value }}% vs période préc.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Commandes</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmtN(totals.orders) }}</p>
                <p v-if="comparison.orders" :class="growthClass(comparison.orders.direction)" class="text-xs font-medium mt-1">
                    {{ growthArrow(comparison.orders.direction) }} {{ comparison.orders.value }}% vs période préc.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Panier moyen</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(totals.average) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Remises</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(totals.discounts) }}</p>
            </div>
        </div>

        <!-- Graphique -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="h-60">
                <canvas ref="chartRef"></canvas>
            </div>
        </div>

        <!-- Tableau détaillé -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-900">Détail par période</h2>
            </div>
            <div v-if="salesData.length" class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Période</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CA</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Panier moyen</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Remises</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="row in salesData" :key="row.period" class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-mono text-gray-700 text-xs">{{ row.period }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ fmtN(row.orders_count) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-gray-900">{{ fmt(row.revenue) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-600">{{ fmt(row.average_order) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-500">{{ fmt(row.discounts) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-4 py-10 text-center text-[13px] text-gray-400">Aucune vente sur cette période</div>
        </div>

    </div>
</template>
