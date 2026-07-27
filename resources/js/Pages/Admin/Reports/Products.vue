<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    topProducts:     Array,
    categoryStats:   Array,
    noSalesProducts: Array,
    chartData:       Object,
    filters:         Object,
})

const startDate = ref(props.filters?.start_date ?? '')
const endDate   = ref(props.filters?.end_date ?? '')

let debounce = null
watch([startDate, endDate], () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => apply(), 400)
})

function apply() {
    router.get(route('admin.reports.products'), {
        start_date: startDate.value,
        end_date:   endDate.value,
    }, { preserveState: true, replace: true, onSuccess: () => updateChart() })
}

// ── Chart camembert ──────────────────────────────────────────────────────────
let chart = null
const chartRef = ref(null)

const COLORS = ['#111827','#2563EB','#16A34A','#F59E0B','#DC2626','#7C3AED','#0891B2','#DB2777']

function buildChart() {
    if (!chartRef.value || !window.Chart) return
    if (chart) chart.destroy()

    chart = new window.Chart(chartRef.value, {
        type: 'doughnut',
        data: {
            labels: props.chartData.labels ?? [],
            datasets: [{
                data: props.chartData.revenues ?? [],
                backgroundColor: COLORS,
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    backgroundColor: '#111827',
                    callbacks: { label: ctx => ' ' + Number(ctx.raw).toLocaleString('fr-FR') + ' F' },
                },
            },
        },
    })
}

function updateChart() {
    if (chart) {
        chart.data.labels = props.chartData.labels ?? []
        chart.data.datasets[0].data = props.chartData.revenues ?? []
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
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header + filtres -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Rapport produits</h1>
                <p class="text-sm text-gray-500 mt-0.5">Performance par produit et catégorie</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="startDate" type="date"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-gray-400 text-sm">→</span>
                <input v-model="endDate" type="date"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <a :href="route('admin.reports.products.export-csv', { start_date: startDate, end_date: endDate })"
                    class="h-9 px-4 flex items-center gap-1.5 border border-gray-200 text-[13px] text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- Graphique catégories -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">CA par catégorie</h2>
                <div class="h-56">
                    <canvas ref="chartRef"></canvas>
                </div>
            </div>

            <!-- Stats catégories -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Catégories</h2>
                </div>
                <div v-if="categoryStats.length" class="divide-y divide-gray-100">
                    <div v-for="cat in categoryStats" :key="cat.id" class="px-4 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-[13px] font-medium text-gray-900">{{ cat.name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ fmtN(cat.quantity_sold) }} unités vendues</p>
                        </div>
                        <p class="text-[13px] font-semibold text-gray-900 tabular-nums">{{ fmt(cat.revenue) }}</p>
                    </div>
                </div>
                <div v-else class="px-4 py-8 text-center text-[13px] text-gray-400">Aucune vente sur cette période</div>
            </div>

        </div>

        <!-- Top 50 produits -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Top produits</h2>
                <span class="text-xs text-gray-400">{{ topProducts.length }} produit(s)</span>
            </div>
            <div v-if="topProducts.length" class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">#</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Catégorie</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Qté vendue</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CA</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(p, i) in topProducts" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-400 tabular-nums">{{ i + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-gray-900">{{ p.name }}</p>
                                <p class="text-xs font-mono text-gray-400 mt-0.5">{{ p.sku }}</p>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600">{{ p.category_name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-gray-900">{{ fmtN(p.quantity_sold) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-gray-900">{{ fmt(p.revenue) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-600">{{ fmtN(p.orders_count) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-4 py-10 text-center text-[13px] text-gray-400">Aucune vente sur cette période</div>
        </div>

        <!-- Produits sans ventes -->
        <div v-if="noSalesProducts.length" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Produits sans ventes</h2>
                <span class="text-xs text-gray-400">{{ noSalesProducts.length }} produit(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prix</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in noSalesProducts" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-medium text-gray-900">{{ p.name }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-400">{{ p.sku }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-600">{{ fmtN(p.stock_quantity) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ fmt(p.sale_price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
