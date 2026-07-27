<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Chart, BarController, BarElement, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend } from 'chart.js'
Chart.register(BarController, BarElement, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend)

const props = defineProps({
    stats: Object,
    revenueChart: Object,
    paymentMethods: Array,
    topProducts: Array,
    recentEntries: Array,
    journals: Array,
    period: String,
})

const periods = [
    { value: 'week', label: 'Cette semaine' },
    { value: 'month', label: 'Ce mois' },
    { value: 'quarter', label: 'Trimestre' },
    { value: 'year', label: 'Année' },
]

function setPeriod(p) {
    router.get(route('admin.accounting.index'), { period: p }, { preserveState: true, replace: true })
}

// FEC modal
const showFecModal = ref(false)
const fecForm = ref({
    start_date: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    format: 'fec',
})

const csrfToken = ref('')
const chartCanvas = ref(null)
let chartInstance = null

function buildChart() {
    if (!chartCanvas.value || !props.revenueChart?.labels?.length) return
    if (chartInstance) { chartInstance.destroy(); chartInstance = null }
    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: props.revenueChart.labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Revenus (F CFA)',
                    data: props.revenueChart.revenues,
                    backgroundColor: 'rgba(37,99,235,0.15)',
                    borderColor: 'rgba(37,99,235,0.8)',
                    borderWidth: 2,
                    borderRadius: 4,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Commandes',
                    data: props.revenueChart.orders,
                    borderColor: 'rgba(22,163,74,0.9)',
                    backgroundColor: 'rgba(22,163,74,0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y2',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 12 }, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.datasetIndex === 0
                            ? ' ' + Number(ctx.raw).toLocaleString('fr-FR') + ' F CFA'
                            : ' ' + ctx.raw + ' cmd',
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
                y: {
                    type: 'linear', position: 'left',
                    ticks: { font: { size: 11 }, callback: (v) => Number(v).toLocaleString('fr-FR') + ' F' },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                y2: {
                    type: 'linear', position: 'right',
                    ticks: { font: { size: 11 }, stepSize: 1 },
                    grid: { drawOnChartArea: false },
                },
            },
        },
    })
}

onMounted(() => {
    csrfToken.value = document.querySelector('meta[name=csrf-token]')?.content ?? ''
    nextTick(() => buildChart())
})

onUnmounted(() => { if (chartInstance) chartInstance.destroy() })

watch(() => props.revenueChart, () => { nextTick(() => buildChart()) }, { deep: true })

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F CFA'
}

function paymentLabel(method) {
    const map = { cinetpay: 'CinetPay', cash_on_delivery: 'À la livraison' }
    return map[method] ?? (method ? method.charAt(0).toUpperCase() + method.slice(1) : 'Autre')
}

function paymentPercent(total) {
    if (!props.stats?.revenue || props.stats.revenue <= 0) return 0
    return Math.round((total / props.stats.revenue) * 100)
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Comptabilité</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Tableau de bord financier</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button v-for="p in periods" :key="p.value"
                    @click="setPeriod(p.value)"
                    :class="period === p.value
                        ? 'bg-blue-600 text-white'
                        : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="h-9 px-4 text-[13px] font-medium rounded-lg transition-colors">
                    {{ p.label }}
                </button>
            </div>
        </div>

        <!-- Quick links -->
        <div class="flex flex-wrap gap-2">
            <a :href="route('admin.accounting.entries')"
                class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                Écritures comptables
            </a>
            <a :href="route('admin.accounting.entries.create')"
                class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle écriture
            </a>
            <a :href="route('admin.accounting.accounts')"
                class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                Plan comptable
            </a>
            <a :href="route('admin.accounting.balance')"
                class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                Balance générale
            </a>
            <a :href="route('admin.accounting.ledger')"
                class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                Grand livre
            </a>
            <button @click="showFecModal = true"
                class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export FEC
            </button>
        </div>

        <!-- KPI strip -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
                <div class="p-5">
                    <p class="text-[12px] font-medium text-gray-500">Chiffre d'affaires</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(stats?.revenue) }}</p>
                </div>
                <div class="p-5">
                    <p class="text-[12px] font-medium text-gray-500">Commandes payées</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ stats?.orders_count ?? 0 }}</p>
                </div>
                <div class="p-5">
                    <p class="text-[12px] font-medium text-gray-500">Panier moyen</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(stats?.average_order) }}</p>
                </div>
                <div class="p-5">
                    <p class="text-[12px] font-medium text-gray-500">Remboursements</p>
                    <p class="text-2xl font-bold text-red-600 mt-1 tabular-nums">{{ fmt(stats?.refunds) }}</p>
                </div>
            </div>
        </div>

        <!-- Charts row -->
        <div class="grid lg:grid-cols-3 gap-5">
            <!-- Revenue chart -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Évolution du chiffre d'affaires</h3>
                <div v-if="revenueChart?.labels?.length" class="relative h-56">
                    <canvas ref="chartCanvas"></canvas>
                </div>
                <div v-else class="flex items-center justify-center h-56 text-[13px] text-gray-400">
                    Aucune donnée sur cette période
                </div>
            </div>

            <!-- Payment methods -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Méthodes de paiement</h3>
                <div v-if="paymentMethods?.length" class="space-y-4">
                    <div v-for="method in paymentMethods" :key="method.payment_method">
                        <div class="flex justify-between text-[13px] mb-1">
                            <span class="font-medium text-gray-700">{{ paymentLabel(method.payment_method) }}</span>
                            <span class="text-gray-500">{{ fmt(method.total) }} ({{ method.count }})</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" :style="{ width: paymentPercent(method.total) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-[13px] text-gray-400 text-center py-8">Aucune donnée</p>
            </div>
        </div>

        <!-- Bottom row -->
        <div class="grid lg:grid-cols-2 gap-5">

            <!-- Top products -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-[14px] font-semibold text-gray-900">Top 10 produits par CA</h3>
                </div>
                <div v-if="topProducts?.length" class="divide-y divide-gray-50">
                    <div v-for="(product, index) in topProducts" :key="product.id"
                        class="px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center text-[11px] font-semibold text-gray-600 flex-shrink-0">
                                {{ index + 1 }}
                            </span>
                            <div>
                                <p class="text-[13px] font-medium text-gray-900">{{ product.name?.substring(0, 30) }}{{ product.name?.length > 30 ? '…' : '' }}</p>
                                <p class="text-[11px] text-gray-400">{{ product.quantity_sold }} vendus</p>
                            </div>
                        </div>
                        <p class="text-[13px] font-semibold text-gray-900">{{ fmt(product.revenue) }}</p>
                    </div>
                </div>
                <div v-else class="p-8 text-center">
                    <p class="text-[13px] text-gray-400">Aucune vente sur cette période</p>
                </div>
            </div>

            <!-- Recent entries -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-[14px] font-semibold text-gray-900">Dernières écritures</h3>
                    <a :href="route('admin.accounting.entries')" class="text-[13px] text-blue-600 hover:underline">Voir tout</a>
                </div>
                <div v-if="recentEntries?.length" class="divide-y divide-gray-50">
                    <a v-for="entry in recentEntries" :key="entry.id"
                        :href="route('admin.accounting.entries.show', entry.id)"
                        class="px-5 py-3 block hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between mb-0.5">
                            <p class="text-[13px] font-medium text-gray-900">{{ entry.entry_number ?? 'N/A' }}</p>
                            <p class="text-[13px] font-semibold text-gray-900">{{ fmt(entry.total_debit) }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[12px] text-gray-500">{{ entry.label ?? entry.description ?? 'N/A' }}</span>
                            <span class="text-[11px] text-gray-400">{{ entry.entry_date_fmt }}</span>
                        </div>
                    </a>
                </div>
                <div v-else class="p-8 text-center">
                    <p class="text-[13px] text-gray-400">Aucune écriture comptable</p>
                </div>
            </div>
        </div>

        <!-- Journals -->
        <div v-if="journals?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[14px] font-semibold text-gray-900">Journaux comptables</h3>
            </div>
            <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-4 p-5">
                <div v-for="journal in journals" :key="journal.id" class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-[13px] font-medium text-gray-900">{{ journal.name }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ journal.entries_count ?? 0 }}</p>
                    <p class="text-[12px] text-gray-500">écritures</p>
                </div>
            </div>
        </div>

        <!-- FEC Export Modal -->
        <Teleport to="body">
            <div v-if="showFecModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @keydown.escape.window="showFecModal = false">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md mx-4 p-6"
                    @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-[15px] font-semibold text-gray-900">Export FEC / CSV</h3>
                        <button @click="showFecModal = false"
                            class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form id="fec-export-form" :action="route('admin.accounting.export')" method="POST" class="space-y-4">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Date début *</label>
                                <input type="date" name="start_date" v-model="fecForm.start_date" required
                                    class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Date fin *</label>
                                <input type="date" name="end_date" v-model="fecForm.end_date" required
                                    class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Format</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 text-[13px] cursor-pointer">
                                    <input type="radio" name="format" value="fec" v-model="fecForm.format" class="accent-blue-600">
                                    FEC (Administration fiscale)
                                </label>
                                <label class="flex items-center gap-2 text-[13px] cursor-pointer">
                                    <input type="radio" name="format" value="csv" v-model="fecForm.format" class="accent-blue-600">
                                    CSV simple
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showFecModal = false"
                                class="h-9 px-4 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg">
                                Annuler
                            </button>
                            <button type="submit"
                                class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                                Télécharger
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

    </div>
</template>
