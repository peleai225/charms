<script setup>
defineProps({
    product:        Object,
    stockMovements: Array,
    totalSales:     Number,
    totalRevenue:   Number,
})

function fmt(n) { return Number(n ?? 0).toLocaleString('fr-FR') + ' F' }
function fmtN(n) { return Number(n ?? 0).toLocaleString('fr-FR') }

function statusClass(s) {
    if (s === 'active')   return 'bg-green-100 text-green-700'
    if (s === 'archived') return 'bg-gray-100 text-gray-500'
    return 'bg-amber-100 text-amber-700'
}
function statusLabel(s) {
    if (s === 'active')   return 'Actif'
    if (s === 'archived') return 'Archivé'
    return 'Brouillon'
}
function stockClass(qty) {
    if (qty <= 0)  return 'bg-red-100 text-red-700'
    if (qty <= 5)  return 'bg-amber-100 text-amber-700'
    return 'bg-green-100 text-green-700'
}
function variantLabel(v) {
    if (!v.attribute_values?.length) return v.name || 'Variante'
    return v.attribute_values.map(av => av.value).join(' / ')
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ product.name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold" :class="statusClass(product.status)">
                        {{ statusLabel(product.status) }}
                    </span>
                    <span class="text-[12px] text-gray-400 font-mono">{{ product.sku }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('admin.products.edit', product.id)"
                    class="h-9 px-4 inline-flex items-center gap-2 bg-blue-600 text-white text-[13px] font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Modifier
                </a>
                <a :href="route('admin.products.index')"
                    class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- KPI strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Ventes totales</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmtN(totalSales) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Chiffre d'affaires</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(totalRevenue) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Stock total</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">
                    {{ product.has_variants
                        ? (product.variants ?? []).reduce((s, v) => s + (v.stock_quantity ?? 0), 0)
                        : product.stock_quantity }} pcs
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Prix de vente</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmt(product.sale_price) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Colonne gauche -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Galerie d'images -->
                <div v-if="product.images?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Images</h2>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                        <div v-for="img in product.images" :key="img.id" class="aspect-square">
                            <img :src="'/storage/' + img.path" class="w-full h-full object-cover rounded-lg border-2"
                                :class="img.is_primary ? 'border-blue-400' : 'border-gray-200'">
                        </div>
                    </div>
                </div>

                <!-- Variantes -->
                <div v-if="product.has_variants && product.variants?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Variantes</h2>
                        <span class="text-[12px] text-gray-400">{{ product.variants.length }} variante(s)</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Variante</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prix vente</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="v in product.variants" :key="v.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <img v-if="v.image" :src="'/storage/' + v.image" class="w-8 h-8 rounded-lg object-cover border border-gray-200">
                                            <span v-else class="w-8 h-8 rounded-lg bg-gray-100 border border-gray-200 inline-block"></span>
                                            <span class="font-medium text-gray-900">{{ variantLabel(v) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[12px] text-gray-700">{{ v.sku }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold" :class="stockClass(v.stock_quantity)">
                                            {{ v.stock_quantity <= 0 ? 'Rupture' : v.stock_quantity + ' pcs' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums text-gray-700">
                                        {{ v.sale_price ? fmt(v.sale_price) : '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mouvements de stock -->
                <div v-if="stockMovements?.length" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Derniers mouvements de stock</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Quantité</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="m in stockMovements" :key="m.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500 text-[12px]">{{ m.created_at_fmt }}</td>
                                    <td class="px-4 py-3">
                                        <span class="text-[12px] font-medium" :class="m.quantity > 0 ? 'text-green-700' : 'text-red-600'">
                                            {{ m.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold"
                                        :class="m.quantity > 0 ? 'text-green-700' : 'text-red-600'">
                                        {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-[12px] text-gray-500">{{ m.note ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Sidebar infos -->
            <div class="space-y-5">

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Informations</h2>
                    <div class="space-y-2.5 text-[13px]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Catégorie</span>
                            <span class="font-medium text-gray-900">{{ product.category?.name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-50 pt-2">
                            <span class="text-gray-500">Prix d'achat</span>
                            <span class="font-medium text-gray-900">{{ fmt(product.purchase_price) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Prix barré</span>
                            <span class="font-medium text-gray-900">{{ product.compare_price ? fmt(product.compare_price) : '—' }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-50 pt-2">
                            <span class="text-gray-500">TVA</span>
                            <span class="font-medium text-gray-900">{{ product.tax_rate }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Poids</span>
                            <span class="font-medium text-gray-900">{{ product.weight ? product.weight + ' kg' : '—' }}</span>
                        </div>
                        <div v-if="product.barcode" class="flex justify-between border-t border-gray-50 pt-2">
                            <span class="text-gray-500">Code-barres</span>
                            <span class="font-mono font-medium text-gray-900">{{ product.barcode }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Options</h2>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="text-gray-600">Mis en avant</span>
                            <span :class="product.is_featured ? 'text-green-600 font-medium' : 'text-gray-400'">{{ product.is_featured ? 'Oui' : 'Non' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="text-gray-600">Nouveauté</span>
                            <span :class="product.is_new ? 'text-green-600 font-medium' : 'text-gray-400'">{{ product.is_new ? 'Oui' : 'Non' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="text-gray-600">Suivi stock</span>
                            <span :class="product.track_stock ? 'text-green-600 font-medium' : 'text-gray-400'">{{ product.track_stock ? 'Oui' : 'Non' }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="product.short_description || product.description" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Description</h2>
                    <p v-if="product.short_description" class="text-[13px] text-gray-600 mb-2">{{ product.short_description }}</p>
                    <p v-if="product.description" class="text-[13px] text-gray-500 whitespace-pre-wrap">{{ product.description }}</p>
                </div>

            </div>
        </div>

    </div>
</template>
