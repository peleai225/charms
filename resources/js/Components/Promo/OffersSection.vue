<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'
import ProductCard from '@/Components/ProductCard.vue'
import PromoDeadline from './PromoDeadline.vue'

// Offres par lot en cours (« 3 pour 10 000 F CFA »), avec leurs produits sur une ligne qui défile.
// Une seule offre : son nom sert de titre de section, sans répéter « Offres du moment ».
const props = defineProps({
    offers: { type: Array, default: () => [] },
})

const single = computed(() => props.offers.length === 1)
</script>

<template>
    <section v-if="offers.length" class="py-10 bg-white">
        <div class="container mx-auto px-4 space-y-10">
            <h2 v-if="!single" class="text-xl font-bold text-slate-900">Offres du moment</h2>

            <div v-for="offer in offers" :key="offer.id" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div class="min-w-0">
                        <component :is="single ? 'h2' : 'h3'" class="font-bold text-slate-900" :class="single ? 'text-xl' : 'text-base'">
                            {{ offer.name }}
                        </component>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ offer.description || "Mélangez les articles de l'offre : le prix du lot s'applique au panier." }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-slate-600 shrink-0">
                        <PromoDeadline v-if="offer.ends_at" :ends-at="offer.ends_at" label="Se termine dans" variant="inline" />
                        <Link v-if="offer.category_slug" :href="`/categorie/${offer.category_slug}`"
                            class="inline-flex h-11 items-center gap-1 text-sm font-semibold text-slate-900 hover:underline">
                            Tout voir
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <div class="flex gap-4 overflow-x-auto snap-x pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
                    <div v-for="p in offer.products" :key="p.id" class="w-44 sm:w-56 shrink-0 snap-start">
                        <ProductCard :product="p" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
