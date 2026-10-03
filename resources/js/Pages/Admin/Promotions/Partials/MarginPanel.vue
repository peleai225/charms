<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import axios from 'axios'
import { AlertTriangle, ChevronDown, ChevronUp, Info, RefreshCw } from 'lucide-vue-next'

const props = defineProps({
    categoryIds: { type: Array, default: () => [] },
    productIds: { type: Array, default: () => [] },
    includeDescendants: { type: Boolean, default: true },
    priceMin: { type: [Number, String], default: null },
    priceMax: { type: [Number, String], default: null },
    lotQty: { type: [Number, String], default: null },
    lotPrice: { type: [Number, String], default: null },
})

const loading = ref(false)
const failed = ref(false)
const result = ref(null)
const listOpen = ref(false)

const formatPrice = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + ' F CFA'

/** Sans lot valide et sans cible, il n'y a rien à calculer : on ne sollicite pas le serveur. */
const canQuery = computed(() =>
    Number(props.lotQty) >= 2
    && props.lotPrice !== null && props.lotPrice !== ''
    && (props.categoryIds.length > 0 || props.productIds.length > 0),
)

async function fetchPreview() {
    if (!canQuery.value) {
        result.value = null
        failed.value = false
        return
    }

    loading.value = true
    failed.value = false

    try {
        const { data } = await axios.post(route('admin.promotions.margin-preview'), {
            category_ids: props.categoryIds,
            product_ids: props.productIds,
            include_descendants: props.includeDescendants,
            price_min: props.priceMin === '' ? null : props.priceMin,
            price_max: props.priceMax === '' ? null : props.priceMax,
            lot_qty: props.lotQty,
            lot_price: props.lotPrice,
        })
        result.value = data
    } catch {
        failed.value = true
        result.value = null
    } finally {
        loading.value = false
    }
}

const refresh = useDebounceFn(fetchPreview, 400)

watch(
    () => [props.categoryIds, props.productIds, props.includeDescendants, props.priceMin, props.priceMax, props.lotQty, props.lotPrice],
    refresh,
    { deep: true },
)

onMounted(fetchPreview)
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-[15px] font-semibold text-gray-900">Impact sur la marge</h2>
            <button v-if="!loading && canQuery" type="button" @click="fetchPreview"
                class="h-8 px-2.5 inline-flex items-center gap-1.5 text-[12px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <RefreshCw class="w-3.5 h-3.5" />
                Recalculer
            </button>
        </div>

        <!-- Rien à calculer -->
        <p v-if="!canQuery" class="text-[13px] text-gray-500">
            Renseignez le lot et au moins une catégorie ou un produit pour voir l’impact sur la marge.
        </p>

        <!-- Chargement -->
        <div v-else-if="loading" class="space-y-3">
            <div class="h-4 w-40 bg-gray-100 rounded animate-pulse"></div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div v-for="n in 4" :key="n" class="h-16 bg-gray-100 rounded-lg animate-pulse"></div>
            </div>
        </div>

        <!-- Erreur -->
        <div v-else-if="failed" class="rounded-lg border border-red-200 bg-red-50 p-3">
            <p class="text-[13px] text-red-700">Le calcul de marge n’a pas abouti.</p>
            <button type="button" @click="fetchPreview"
                class="mt-2 h-8 px-3 inline-flex items-center gap-1.5 text-[12px] font-medium text-red-700 border border-red-300 rounded-lg hover:bg-red-100 transition">
                <RefreshCw class="w-3.5 h-3.5" />
                Réessayer
            </button>
        </div>

        <!-- Aucun produit concerné -->
        <div v-else-if="result && result.eligible_count === 0" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-[13px] font-medium text-gray-700">Aucun produit ne correspond à cette fourchette</p>
            <p class="text-[12px] text-gray-500 mt-1">
                Élargissez la fourchette de prix, ou ciblez une autre catégorie : en l’état, l’offre ne
                s’appliquerait à rien.
            </p>
        </div>

        <!-- Résultat -->
        <template v-else-if="result">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Produits concernés</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5 tabular-nums">{{ result.eligible_count }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Prix unitaire en lot</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5 tabular-nums">{{ formatPrice(result.unit_price_in_lot) }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Marge catalogue</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5 tabular-nums">
                        <template v-if="result.catalog_margin !== null">{{ formatPrice(result.catalog_margin) }}</template>
                        <span v-else class="text-[13px] font-medium text-gray-400">Non calculable</span>
                    </p>
                </div>
                <div class="rounded-lg border p-3"
                    :class="result.lot_margin !== null && result.lot_margin < 0 ? 'border-red-200 bg-red-50' : 'border-gray-200'">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Marge en lot</p>
                    <p class="text-lg font-bold mt-0.5 tabular-nums"
                        :class="result.lot_margin !== null && result.lot_margin < 0 ? 'text-red-700' : 'text-gray-900'">
                        <template v-if="result.lot_margin !== null">{{ formatPrice(result.lot_margin) }}</template>
                        <span v-else class="text-[13px] font-medium text-gray-400">Non calculable</span>
                    </p>
                </div>
            </div>

            <!-- Prix du lot sous le coût -->
            <div v-if="result.below_cost" class="rounded-lg border border-red-200 bg-red-50 p-3 flex items-start gap-2.5">
                <AlertTriangle class="w-4 h-4 text-red-600 mt-0.5 shrink-0" />
                <p class="text-[13px] text-red-700">
                    Le prix du lot passe sous le coût d’achat d’au moins un produit. Chaque lot vendu
                    ferait perdre de l’argent sur ce produit.
                </p>
            </div>

            <!-- Prix d'achat manquants -->
            <div v-if="result.without_purchase_price > 0"
                class="rounded-lg border border-amber-200 bg-amber-50 p-3 flex items-start gap-2.5">
                <Info class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" />
                <p class="text-[13px] text-amber-800">
                    {{ result.without_purchase_price }} produit(s) n’ont pas de prix d’achat renseigné — leur
                    marge ne peut pas être calculée, et ils ne sont pas comptés dans les moyennes ci-dessus.
                </p>
            </div>

            <!-- Détail par produit -->
            <div>
                <button type="button" @click="listOpen = !listOpen"
                    class="inline-flex items-center gap-1.5 text-[13px] font-medium text-blue-600 hover:text-blue-700 transition">
                    <component :is="listOpen ? ChevronUp : ChevronDown" class="w-4 h-4" />
                    {{ listOpen ? 'Masquer le détail' : 'Voir le détail par produit' }}
                </button>

                <div v-if="listOpen" class="mt-3 border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto max-h-72 overflow-y-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prix de vente</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prix d’achat</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Marge en lot</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="product in result.products" :key="product.id">
                                    <td class="px-3 py-2 text-gray-800">{{ product.name }}</td>
                                    <td class="px-3 py-2 text-right text-gray-600 tabular-nums">{{ formatPrice(product.sale_price) }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">
                                        <template v-if="product.purchase_price !== null">
                                            <span class="text-gray-600">{{ formatPrice(product.purchase_price) }}</span>
                                        </template>
                                        <span v-else class="text-gray-400">Non renseigné</span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold tabular-nums"
                                        :class="product.lot_margin === null ? 'text-gray-400'
                                            : product.lot_margin < 0 ? 'text-red-700' : 'text-gray-900'">
                                        <template v-if="product.lot_margin !== null">{{ formatPrice(product.lot_margin) }}</template>
                                        <template v-else>Non calculable</template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
