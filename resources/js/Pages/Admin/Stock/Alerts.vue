<script setup>
defineProps({
    outOfStock: Array,
    lowStock: Array,
})
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <a :href="route('admin.stock.index')"
                class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Alertes de stock</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ outOfStock.length + lowStock.length }} produit(s) nécessitent votre attention
                </p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-5">

            <!-- Ruptures de stock -->
            <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-red-100 bg-red-50 flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-[13px] font-semibold text-red-900">
                        Ruptures de stock ({{ outOfStock.length }})
                    </h3>
                </div>

                <template v-if="outOfStock.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Catégorie</th>
                                    <th class="px-5 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="product in outOfStock" :key="product.id"
                                    class="hover:bg-red-50/30 transition-colors">
                                    <td class="px-5 py-3 text-[13px] font-medium text-gray-900">{{ product.name }}</td>
                                    <td class="px-5 py-3 text-[12px] text-gray-400 font-mono">{{ product.sku ?? '—' }}</td>
                                    <td class="px-5 py-3 text-[12px] text-gray-500">{{ product.category_name ?? 'Sans catégorie' }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a :href="route('admin.products.edit', product.id)"
                                                class="h-7 px-2 inline-flex items-center text-[11px] font-medium border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                                                Modifier
                                            </a>
                                            <a :href="route('admin.stock.create-movement') + '?product_id=' + product.id"
                                                class="h-7 px-2 inline-flex items-center text-[11px] font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                + Stock
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <div v-else class="py-12 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-[13px] font-medium text-green-700">Aucune rupture de stock</p>
                </div>
            </div>

            <!-- Stock faible -->
            <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-100 bg-amber-50 flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-[13px] font-semibold text-amber-900">
                        Stock faible ({{ lowStock.length }})
                    </h3>
                </div>

                <template v-if="lowStock.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                                    <th class="px-5 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock / Seuil</th>
                                    <th class="px-5 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="product in lowStock" :key="product.id"
                                    class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-5 py-3 text-[13px] font-medium text-gray-900">{{ product.name }}</td>
                                    <td class="px-5 py-3 text-[12px] text-gray-400 font-mono">{{ product.sku ?? '—' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-[12px] font-semibold text-amber-600">{{ product.stock_quantity }}</span>
                                        <span class="text-[12px] text-gray-400"> / {{ product.stock_alert_threshold }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a :href="route('admin.products.edit', product.id)"
                                                class="h-7 px-2 inline-flex items-center text-[11px] font-medium border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                                                Modifier
                                            </a>
                                            <a :href="route('admin.stock.create-movement') + '?product_id=' + product.id"
                                                class="h-7 px-2 inline-flex items-center text-[11px] font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                + Stock
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <div v-else class="py-12 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-[13px] font-medium text-green-700">Tous les stocks sont suffisants</p>
                </div>
            </div>

        </div>

    </div>
</template>
