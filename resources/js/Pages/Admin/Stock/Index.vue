<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    alertProducts: Array,
    recentMovements: Array,
})

const totalAlerts = computed(() => (props.stats?.out_of_stock ?? 0) + (props.stats?.low_stock ?? 0))

function fmtPrice(val) {
    return Number(val ?? 0).toLocaleString('fr-FR') + ' F CFA'
}

function fmtQty(val) {
    return Number(val ?? 0).toLocaleString('fr-FR')
}

function goToCreateMovement(productId) {
    router.get(route('admin.stock.create-movement'), { product_id: productId })
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion du Stock</h1>
                <p class="text-sm text-gray-500 mt-0.5">Suivez vos niveaux de stock et mouvements</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a :href="route('admin.stock.create-movement')"
                    class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Mouvement
                </a>
                <a :href="route('admin.stock.reception')"
                    class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Réception
                </a>
                <a :href="route('admin.stock.inventory')"
                    class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] font-medium border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Inventaire
                </a>
                <a :href="route('admin.stock.alerts')"
                    class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                    Alertes ({{ totalAlerts }})
                </a>
            </div>
        </div>

        <!-- KPI Strip -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-100">
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Produits actifs</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ fmtQty(stats.total_products) }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Unités en stock</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ fmtQty(stats.total_units) }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Valeur du stock</p>
                    <p class="text-2xl font-bold text-green-600 tabular-nums">{{ fmtPrice(stats.stock_value) }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Ruptures</p>
                    <p class="text-2xl font-bold text-red-600 tabular-nums">{{ stats.out_of_stock }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Stock faible</p>
                    <p class="text-2xl font-bold text-amber-600 tabular-nums">{{ stats.low_stock }}</p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-5">

            <!-- Produits en alerte -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-[13px] font-semibold text-gray-900">Produits en alerte</h3>
                    <a :href="route('admin.stock.alerts')" class="text-[12px] text-blue-600 font-medium hover:underline">Voir tout</a>
                </div>

                <template v-if="alertProducts.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                                    <th class="px-5 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="product in alertProducts" :key="product.id"
                                    class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3 text-[13px] font-medium text-gray-900">{{ product.name }}</td>
                                    <td class="px-5 py-3 text-[12px] text-gray-400 font-mono">{{ product.sku ?? 'N/A' }}</td>
                                    <td class="px-5 py-3">
                                        <span v-if="product.stock_quantity <= 0"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 text-red-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rupture
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ product.stock_quantity }} unités
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a :href="route('admin.products.edit', product.id)"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button @click="goToCreateMovement(product.id)"
                                                class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <div v-else class="py-12 text-center">
                    <p class="text-[13px] font-medium text-green-700">Tous les stocks sont OK</p>
                    <p class="text-[12px] text-gray-400 mt-1">Aucun produit en rupture ou stock faible</p>
                </div>
            </div>

            <!-- Derniers mouvements -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-[13px] font-semibold text-gray-900">Derniers mouvements</h3>
                    <a :href="route('admin.stock.movements')" class="text-[12px] text-blue-600 font-medium hover:underline">Voir tout</a>
                </div>

                <template v-if="recentMovements.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Raison</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                                    <th class="px-5 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Qté</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="movement in recentMovements" :key="movement.id"
                                    class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                                                :class="movement.quantity > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                                                <svg v-if="movement.quantity > 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                </svg>
                                            </span>
                                            <p class="text-[13px] font-medium text-gray-900 truncate">
                                                {{ movement.product_name ?? 'Produit supprimé' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-[12px] text-gray-500">{{ movement.reason }}</td>
                                    <td class="px-5 py-3 text-[11px] text-gray-400">{{ movement.created_at }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="text-[12px] font-bold"
                                            :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <div v-else class="py-12 text-center text-[13px] text-gray-400">Aucun mouvement récent</div>
            </div>
        </div>

    </div>
</template>
