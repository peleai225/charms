<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    totalCustomers:    Number,
    activeCustomers:   Number,
    vipCustomers:      Number,
    newCustomers:      Number,
    inactiveCustomers: Number,
    avgOrderValue:     Number,
    avgLifetimeValue:  Number,
    totalRevenue:      Number,
    topCustomers:      Array,
    recentOrders:      Array,
    tags:              Array,
    segmentData:       Object,
})

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}

function pct(count) {
    if (!props.totalCustomers) return 0
    return Math.round((count / props.totalCustomers) * 100)
}

const classifyProcessing = ref(false)
function autoClassify() {
    if (!confirm('Lancer la classification automatique pour tous les clients ?')) return
    classifyProcessing.value = true
    router.post(route('admin.crm.auto-classify'), {}, {
        onFinish: () => { classifyProcessing.value = false },
    })
}

const SEGMENT_COLORS = {
    vip:      { bar: 'bg-amber-500', badge: 'bg-amber-50 text-amber-700' },
    loyal:    { bar: 'bg-blue-500',  badge: 'bg-blue-50 text-blue-700'  },
    new:      { bar: 'bg-green-500', badge: 'bg-green-50 text-green-700'},
    inactive: { bar: 'bg-red-400',   badge: 'bg-red-50 text-red-700'   },
}

const SEGMENT_LABELS = {
    vip: 'VIP', loyal: 'Fidèles', new: 'Nouveaux', inactive: 'Inactifs',
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">CRM Clients</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Vue d'ensemble de votre base clients</p>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('admin.crm.tags')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                    Gérer les tags
                </a>
                <button @click="autoClassify" :disabled="classifyProcessing"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-60">
                    <svg v-if="classifyProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Classifier automatiquement
                </button>
            </div>
        </div>

        <!-- KPI Strip -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
                <div v-for="[label, value] in [
                    ['Total Clients', totalCustomers],
                    ['Clients VIP', vipCustomers],
                    ['Nouveaux (30j)', newCustomers],
                    ['Inactifs (90j+)', inactiveCustomers],
                ]" :key="label" class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ label }}</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ Number(value).toLocaleString('fr-FR') }}</p>
                </div>
            </div>
        </div>

        <!-- Revenue KPIs -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div v-for="[label, val] in [
                ['Chiffre d\'affaires total', totalRevenue],
                ['Panier moyen', avgOrderValue],
                ['Valeur vie client moy.', avgLifetimeValue],
            ]" :key="label" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ label }}</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">
                    {{ Number(val ?? 0).toLocaleString('fr-FR', { maximumFractionDigits: 0 }) }}
                    <span class="text-sm font-normal text-gray-400">F CFA</span>
                </p>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-5">

            <!-- Segmentation + Tags -->
            <div class="lg:col-span-4 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Segmentation Clients</h3>
                <div class="space-y-3">
                    <div v-for="[key, cnt] in [
                        ['vip', segmentData.vip],
                        ['loyal', segmentData.loyal],
                        ['new', segmentData.new],
                        ['inactive', segmentData.inactive],
                    ]" :key="key">
                        <div class="flex justify-between text-[12px] mb-1">
                            <span class="font-medium text-gray-700">{{ SEGMENT_LABELS[key] }}</span>
                            <span class="text-gray-400">{{ cnt }} ({{ pct(cnt) }}%)</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div :class="SEGMENT_COLORS[key].bar"
                                class="h-full rounded-full transition-all"
                                :style="{ width: pct(cnt) + '%' }">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <template v-if="tags?.length">
                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <h4 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-3">Tags</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-for="tag in tags" :key="tag.id"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium text-white"
                                :style="{ background: tag.color }">
                                {{ tag.name }}
                                <span class="bg-black/10 px-1 rounded">{{ tag.customers_count }}</span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Top clients -->
            <div class="lg:col-span-8 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-[13px] font-semibold text-gray-900">Top 10 Clients</h3>
                    <a :href="route('admin.customers.index')"
                        class="text-[12px] text-blue-600 font-medium hover:underline">Voir tous</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Dépensé</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Panier moy.</th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Tags</th>
                                <th class="px-5 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="!topCustomers?.length">
                                <td colspan="6" class="px-5 py-12 text-center text-[13px] text-gray-400">
                                    Aucun client avec des commandes
                                </td>
                            </tr>
                            <tr v-for="c in topCustomers" :key="c.id"
                                class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold flex-shrink-0">
                                            {{ c.initials }}
                                        </div>
                                        <div>
                                            <p class="text-[13px] font-medium text-gray-900">{{ c.full_name }}</p>
                                            <p class="text-[11px] text-gray-400">{{ c.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-[13px] font-medium text-gray-700">{{ c.orders_count }}</td>
                                <td class="px-5 py-3 text-[13px] font-medium text-gray-900">{{ Number(c.total_spent ?? 0).toLocaleString('fr-FR', { maximumFractionDigits: 0 }) }} F</td>
                                <td class="px-5 py-3 text-[13px] text-gray-600">{{ Number(c.avg_order_value ?? 0).toLocaleString('fr-FR', { maximumFractionDigits: 0 }) }} F</td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-1">
                                        <span v-for="tag in (c.tags ?? [])" :key="tag.id"
                                            class="w-2.5 h-2.5 rounded-full"
                                            :style="{ background: tag.color }"
                                            :title="tag.name">
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a :href="route('admin.crm.customer-analytics', c.id)"
                                        class="text-[12px] text-blue-600 font-medium hover:underline">Analyser</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dernières commandes -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-gray-900">5 dernières commandes</h3>
                <a :href="route('admin.orders.index')"
                    class="text-[12px] text-blue-600 font-medium hover:underline">Voir toutes</a>
            </div>
            <div v-if="!recentOrders?.length" class="py-12 text-center text-[13px] text-gray-400">
                Aucune commande récente
            </div>
            <div v-else class="divide-y divide-gray-50">
                <div v-for="order in recentOrders" :key="order.id"
                    class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ order.customer_name ?? '—' }}</p>
                        <p class="text-[11px] text-gray-400">{{ order.reference }} · {{ order.created_at_fmt }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[13px] font-semibold text-gray-900 tabular-nums">{{ fmt(order.total) }}</span>
                        <a :href="route('admin.orders.show', order.id)"
                            class="text-[12px] text-blue-600 font-medium hover:underline">Voir</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
