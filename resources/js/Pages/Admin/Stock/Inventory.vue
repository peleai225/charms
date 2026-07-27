<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    products: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

let searchTimer = null
watch(search, val => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get(route('admin.stock.inventory'), {
            search: val || undefined,
        }, { preserveState: true, replace: true })
    }, 300)
})

// Reason form
const reason = ref('')
const reasonError = ref('')

// Build adjustments as reactive map { productId_variantId: newQty }
// We track local edited quantities
const editedQty = ref({})

function getKey(productId, variantId) {
    return variantId ? `${productId}_${variantId}` : `${productId}`
}

function getQty(productId, variantId, original) {
    const key = getKey(productId, variantId)
    if (editedQty.value[key] === undefined) {
        editedQty.value[key] = original
    }
    return editedQty.value[key]
}

function setQty(productId, variantId, val) {
    const key = getKey(productId, variantId)
    editedQty.value[key] = Number(val)
}

function diff(productId, variantId, original) {
    const key = getKey(productId, variantId)
    const nq = editedQty.value[key] ?? original
    return nq - original
}

function diffLabel(productId, variantId, original) {
    const d = diff(productId, variantId, original)
    if (d === 0) return '—'
    return d > 0 ? `+${d}` : String(d)
}

function diffClass(productId, variantId, original) {
    const d = diff(productId, variantId, original)
    if (d > 0) return 'text-green-600'
    if (d < 0) return 'text-red-600'
    return 'text-gray-400'
}

const changesCount = computed(() => {
    let count = 0
    for (const [key, newQty] of Object.entries(editedQty.value)) {
        // Find original
        for (const product of (props.products?.data ?? [])) {
            const pid = String(product.id)
            if (key === pid && newQty !== product.stock_quantity) { count++; break }
            for (const v of (product.variants ?? [])) {
                if (key === `${pid}_${v.id}` && newQty !== v.stock_quantity) { count++; break }
            }
        }
    }
    return count
})

function submitAdjustments() {
    if (!reason.value.trim()) {
        reasonError.value = 'La raison est obligatoire'
        return
    }
    reasonError.value = ''

    const adjustments = []
    for (const product of (props.products?.data ?? [])) {
        const newQty = editedQty.value[String(product.id)]
        if (newQty !== undefined && newQty !== product.stock_quantity) {
            adjustments.push({ product_id: product.id, variant_id: null, new_quantity: newQty })
        }
        for (const v of (product.variants ?? [])) {
            const key = `${product.id}_${v.id}`
            const vQty = editedQty.value[key]
            if (vQty !== undefined && vQty !== v.stock_quantity) {
                adjustments.push({ product_id: product.id, variant_id: v.id, new_quantity: vQty })
            }
        }
    }

    if (adjustments.length === 0) return

    router.post(route('admin.stock.adjust-inventory'), {
        adjustments,
        reason: reason.value,
    })
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a :href="route('admin.stock.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Inventaire des stocks</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Ajustez les quantités réelles</p>
                </div>
            </div>
        </div>

        <!-- Recherche -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="search"
                        placeholder="Rechercher par nom, SKU ou code-barres..."
                        class="w-full h-9 pl-9 pr-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Raison d'ajustement + bouton Enregistrer -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="flex-1">
                    <input v-model="reason" type="text"
                        placeholder="Raison de l'ajustement (ex: Inventaire mensuel) *"
                        :class="reasonError ? 'border-red-300' : 'border-gray-200'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <p v-if="reasonError" class="mt-1 text-xs text-red-600">{{ reasonError }}</p>
                </div>
                <button @click="submitAdjustments"
                    :disabled="changesCount === 0"
                    class="h-9 px-4 flex items-center gap-2 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                    <span v-if="changesCount > 0"
                        class="bg-white/20 text-white text-[11px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ changesCount }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Tableau -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock système</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Stock réel</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Écart</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template v-if="products.data.length === 0">
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <p class="text-[13px] font-medium text-gray-500">Aucun produit trouvé</p>
                                    <p class="text-[12px] text-gray-400 mt-1">Modifiez votre recherche</p>
                                </td>
                            </tr>
                        </template>
                        <template v-for="product in products.data" :key="product.id">
                            <!-- Ligne produit -->
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img v-if="product.primary_image_url"
                                            :src="product.primary_image_url"
                                            :alt="product.name"
                                            class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                        <div v-else
                                            class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ product.name }}</p>
                                            <p v-if="product.variants_count > 0" class="text-[11px] text-gray-400">
                                                {{ product.variants_count }} variante(s)
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-400 font-mono text-[12px]">{{ product.sku ?? '—' }}</td>
                                <td class="px-5 py-3 text-center font-semibold text-gray-900">{{ product.stock_quantity }}</td>
                                <td class="px-5 py-3 text-center">
                                    <input
                                        type="number"
                                        :value="getQty(product.id, null, product.stock_quantity)"
                                        @input="setQty(product.id, null, $event.target.value)"
                                        min="0"
                                        class="w-24 h-8 px-2 text-center text-[13px] font-semibold border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        :class="diff(product.id, null, product.stock_quantity) !== 0
                                            ? 'border-amber-400 bg-amber-50'
                                            : 'border-gray-200'"
                                    >
                                </td>
                                <td class="px-5 py-3 text-center font-bold"
                                    :class="diffClass(product.id, null, product.stock_quantity)">
                                    {{ diffLabel(product.id, null, product.stock_quantity) }}
                                </td>
                            </tr>

                            <!-- Variantes -->
                            <tr v-for="variant in product.variants" :key="`v-${variant.id}`"
                                class="hover:bg-gray-50/50 transition-colors bg-gray-50/30">
                                <td class="px-5 py-2.5 pl-14">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <span class="text-[12px] text-gray-600">{{ variant.sku }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-2.5 text-gray-400 font-mono text-[11px]">{{ variant.sku }}</td>
                                <td class="px-5 py-2.5 text-center font-medium text-gray-700">{{ variant.stock_quantity }}</td>
                                <td class="px-5 py-2.5 text-center">
                                    <input
                                        type="number"
                                        :value="getQty(product.id, variant.id, variant.stock_quantity)"
                                        @input="setQty(product.id, variant.id, $event.target.value)"
                                        min="0"
                                        class="w-20 h-7 px-2 text-center text-[12px] font-medium border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        :class="diff(product.id, variant.id, variant.stock_quantity) !== 0
                                            ? 'border-amber-400 bg-amber-50'
                                            : 'border-gray-200'"
                                    >
                                </td>
                                <td class="px-5 py-2.5 text-center font-medium text-[12px]"
                                    :class="diffClass(product.id, variant.id, variant.stock_quantity)">
                                    {{ diffLabel(product.id, variant.id, variant.stock_quantity) }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1"
                class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ products.current_page }} / {{ products.last_page }}
                    &nbsp;·&nbsp; {{ products.total }} produits
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="products.prev_page_url" :href="products.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in products.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition">
                            {{ link.label }}
                        </a>
                    </template>
                    <a v-if="products.next_page_url" :href="products.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>
