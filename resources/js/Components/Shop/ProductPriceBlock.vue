<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Countdown from '@/Components/Promo/Countdown.vue'
import PromoDeadline from '@/Components/Promo/PromoDeadline.vue'
import { clockOffset, isEndingSoon, endDateText } from '@/Components/Promo/countdown.js'
import { useHelpers } from '@/Composables/useHelpers'

// Prix de la fiche produit : prix facturé, prix barré, vente flash en cours et offres par lot.
// En vente flash, `price` est déjà le prix flash (calculé par le serveur, comme au panier)
// et `regularPrice` le prix normal à barrer.
const props = defineProps({
    product: { type: Object, required: true },
    price: { type: [Number, String], required: true },
    regularPrice: { type: [Number, String], default: null },
    promotions: { type: Array, default: () => [] },
})
defineEmits(['flash-ended'])

const { formatPrice } = useHelpers()
const page = usePage()

const flashSale = computed(() => props.product.flash_sale ?? null)
const struckPrice = computed(() => flashSale.value ? props.regularPrice : props.product.compare_price)

// ─── Offres par lot ──────────────────────────────────────────────────────────
// Les offres viennent du serveur. Une variante peut avoir son propre prix et
// sortir de la fourchette de l'offre : on le dit, au lieu de masquer en silence.
const inBand = (offer, price) =>
    (offer.price_min == null || price >= offer.price_min)
    && (offer.price_max == null || price <= offer.price_max)

const activePromotions = computed(() =>
    props.promotions
        .filter(offer => inBand(offer, props.price))
        // L'économie est recalculée au prix de la variante choisie, pas au prix de base.
        .map(offer => ({
            ...offer,
            saving: Math.max(0, props.price * offer.lot_qty - offer.lot_price),
        })),
)

const offerNow = Date.now() + clockOffset(page.props.server_now, Date.now())

const variantLeavesOffer = computed(
    () => props.promotions.length > 0 && activePromotions.value.length === 0,
)
</script>

<template>
    <div>
        <!-- Prix -->
        <div class="flex items-baseline gap-3 mb-2">
            <span class="text-3xl font-bold text-slate-900">{{ formatPrice(price) }}</span>
            <span v-if="struckPrice && Number(struckPrice) > Number(price)" class="text-xl text-slate-400 line-through">{{ formatPrice(struckPrice) }}</span>
        </div>

        <!-- Vente flash -->
        <div v-if="flashSale" class="mb-4 rounded-lg border border-accent-200 bg-accent-50 px-3 py-2.5 text-accent-800">
            <p class="text-sm font-semibold">
                {{ flashSale.name }} · -{{ flashSale.discount_percent }} %
            </p>
            <PromoDeadline :ends-at="flashSale.ends_at" label="Fin de la vente flash dans" variant="inline"
                tone="inherit" class="mt-0.5 text-sm" @ended="$emit('flash-ended')" />
        </div>

        <!-- Offres par lot -->
        <div v-if="activePromotions.length" class="mb-4 border border-primary-600/20 rounded-lg overflow-hidden">
            <div class="bg-primary-600/5 px-3 py-2">
                <p class="text-xs font-semibold text-primary-600">Offres en cours</p>
            </div>
            <div class="divide-y divide-slate-100">
                <div v-for="offer in activePromotions" :key="offer.name" class="px-3 py-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium text-slate-900">{{ offer.name }}</span>
                        <span class="font-semibold text-primary-600 whitespace-nowrap">
                            {{ formatPrice(offer.unit_price_in_lot) }} / unité
                        </span>
                    </div>
                    <p v-if="offer.saving > 0" class="text-xs text-green-600 mt-0.5">
                        Économisez {{ formatPrice(offer.saving) }} sur {{ offer.lot_qty }} articles
                    </p>
                    <p v-if="offer.description" class="text-xs text-slate-500 mt-0.5">
                        {{ offer.description }}
                    </p>
                    <Countdown v-if="isEndingSoon(offer.ends_at, offerNow)" :ends-at="offer.ends_at"
                        label="Se termine dans" variant="inline" class="mt-1 text-xs text-red-600" />
                    <p v-else-if="offer.ends_at" class="text-xs text-slate-500 mt-0.5">
                        Jusqu'au {{ endDateText(offer.ends_at) }}
                    </p>
                </div>
            </div>
        </div>

        <p v-else-if="variantLeavesOffer" class="mb-4 text-sm text-slate-500">
            Cette option n'entre pas dans les offres par lot en cours.
        </p>
    </div>
</template>
