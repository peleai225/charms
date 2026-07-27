<script setup>
defineProps({
    outOfStock:    Array,
    lowStock:      Array,
    stockValue:    Object,
    stockRotation: Array,
})

function fmt(n) { return Number(n ?? 0).toLocaleString('fr-FR') + ' F' }
function fmtN(n) { return Number(n ?? 0).toLocaleString('fr-FR') }
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Rapport stock</h1>
            <p class="text-sm text-gray-500 mt-0.5">État des stocks en temps réel</p>
        </div>

        <!-- KPI valeur stock -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Valeur au coût</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(stockValue?.cost_value) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Valeur de vente</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(stockValue?.sale_value) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Unités totales</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmtN(stockValue?.total_units) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- Ruptures -->
            <div class="bg-white rounded-xl border border-red-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-red-700">Ruptures de stock</h2>
                    <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">{{ outOfStock.length }}</span>
                </div>
                <div v-if="outOfStock.length" class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                                <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in outOfStock" :key="p.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-medium text-gray-900">{{ p.name }}</td>
                                <td class="px-4 py-2.5 font-mono text-gray-500 text-xs">{{ p.sku }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="bg-red-100 text-red-700 text-[11px] font-semibold px-2 py-0.5 rounded-full">Rupture</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-4 py-8 text-center text-[13px] text-gray-400">Aucune rupture de stock</div>
            </div>

            <!-- Stock faible -->
            <div class="bg-white rounded-xl border border-amber-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-amber-100 bg-amber-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-amber-700">Stock faible</h2>
                    <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">{{ lowStock.length }}</span>
                </div>
                <div v-if="lowStock.length" class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Seuil</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in lowStock" :key="p.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-medium text-gray-900">{{ p.name }}</td>
                                <td class="px-4 py-2.5 font-mono text-gray-500 text-xs">{{ p.sku }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-amber-700 tabular-nums">{{ p.stock_quantity }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-500 tabular-nums">{{ p.stock_alert_threshold }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-4 py-8 text-center text-[13px] text-gray-400">Aucun stock faible</div>
            </div>

        </div>

        <!-- Rotation des stocks -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-900">Rotation des stocks — 30 derniers jours</h2>
            </div>
            <div v-if="stockRotation.length" class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock actuel</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Vendus (30j)</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Jours restants</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in stockRotation" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-medium text-gray-900">{{ p.name }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ fmtN(p.stock_quantity) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-gray-900">{{ fmtN(p.sold_30d) }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums">
                                <span v-if="p.days_of_stock !== null"
                                    :class="p.days_of_stock <= 7 ? 'text-red-600 font-semibold' : p.days_of_stock <= 14 ? 'text-amber-600 font-semibold' : 'text-gray-700'">
                                    {{ p.days_of_stock }}j
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-4 py-8 text-center text-[13px] text-gray-400">Aucune vente sur les 30 derniers jours</div>
        </div>

    </div>
</template>
