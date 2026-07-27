<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    newCustomers:    Number,
    activeCustomers: Number,
    avgRevenue:      Number,
    topCustomers:    Array,
    geoStats:        Array,
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
    router.get(route('admin.reports.customers'), {
        start_date: startDate.value,
        end_date:   endDate.value,
    }, { preserveState: true, replace: true, onSuccess: () => updateChart() })
}

function fmt(n) { return Number(n ?? 0).toLocaleString('fr-FR') + ' F' }

// ── Graphique répartition géographique ────────────────────────────────────────
let chart     = null
const chartRef = ref(null)

function buildChart() {
    if (!chartRef.value || !window.Chart) return
    if (chart) chart.destroy()

    chart = new window.Chart(chartRef.value, {
        type: 'bar',
        data: {
            labels: props.chartData?.labels ?? [],
            datasets: [{
                label: 'CA (F CFA)',
                data: props.chartData?.revenues ?? [],
                backgroundColor: 'rgba(37,99,235,0.8)',
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw ?? 0).toLocaleString('fr-FR') + ' F CFA',
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: '#F3F4F6' },
                    ticks: {
                        font: { size: 11 },
                        color: '#9CA3AF',
                        callback: v => Number(v).toLocaleString('fr-FR', { notation: 'compact' }),
                    },
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#6B7280' },
                },
            },
        },
    })
}

function updateChart() {
    if (chart) {
        chart.data.labels              = props.chartData?.labels ?? []
        chart.data.datasets[0].data   = props.chartData?.revenues ?? []
        chart.update()
    }
}

onMounted(() => {
    if (window.Chart) buildChart()
    else {
        const t = setInterval(() => { if (window.Chart) { clearInterval(t); buildChart() } }, 100)
    }
})
</script>

<template>
<div class="p-6 space-y-5">

    <!-- Header + filtres -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Rapport des clients</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Analyse des clients sur la période sélectionnée</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <input
                v-model="startDate"
                type="date"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <span class="text-gray-400 text-[12px]">→</span>
            <input
                v-model="endDate"
                type="date"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button
                @click="apply"
                class="h-9 px-4 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition"
            >
                Appliquer
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <p class="text-[12px] text-gray-500">Nouveaux clients</p>
                <p class="text-2xl font-black text-gray-900">{{ newCustomers }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div>
                <p class="text-[12px] text-gray-500">Clients actifs</p>
                <p class="text-2xl font-black text-gray-900">{{ activeCustomers }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[12px] text-gray-500">CA moyen / client actif</p>
                <p class="text-2xl font-black text-gray-900">{{ fmt(avgRevenue) }}</p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">

        <!-- Top clients -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[14px] font-semibold text-gray-900">Top 20 clients</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-8">#</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template v-if="topCustomers?.length">
                            <tr
                                v-for="(customer, index) in topCustomers"
                                :key="customer.id"
                                class="hover:bg-gray-50/60"
                            >
                                <td class="px-5 py-3 text-gray-400 font-medium">{{ index + 1 }}</td>
                                <td class="px-5 py-3">
                                    <a
                                        :href="route('admin.customers.show', customer.id)"
                                        class="font-medium text-gray-900 hover:text-blue-600 transition"
                                    >{{ customer.full_name }}</a>
                                    <p v-if="customer.email" class="text-[11px] text-gray-400">{{ customer.email }}</p>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ customer.orders_count }}</td>
                                <td class="px-5 py-3 text-right font-bold text-green-600">{{ fmt(customer.orders_sum_total) }}</td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="4" class="px-5 py-12 text-center text-[13px] text-gray-400">
                                Aucun client actif sur cette période
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Répartition géographique -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[14px] font-semibold text-gray-900">Répartition géographique</h3>
            </div>

            <div class="p-5" style="height:220px">
                <canvas ref="chartRef"></canvas>
            </div>

            <div class="overflow-x-auto border-t border-gray-100">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Ville</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template v-if="geoStats?.length">
                            <tr
                                v-for="city in geoStats"
                                :key="city.shipping_city ?? 'unknown'"
                                class="hover:bg-gray-50/60"
                            >
                                <td class="px-5 py-3 font-medium text-gray-900">{{ city.shipping_city ?? 'Non renseigné' }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ city.orders_count }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ fmt(city.revenue) }}</td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="3" class="px-5 py-12 text-center text-[13px] text-gray-400">
                                Aucune donnée géographique disponible
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</template>
