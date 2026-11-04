<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'
import ProductCard from '@/Components/ProductCard.vue'
import Countdown from './Countdown.vue'
import { clockOffset, isEndingSoon, endDateText } from './countdown.js'

// Offres par lot en cours (« 3 pour 10 000 F CFA »), avec leurs produits
defineProps({
    offers: { type: Array, default: () => [] },
})

const page = usePage()
const now = Date.now() + clockOffset(page.props.server_now, Date.now())
</script>

<template>
    <section v-if="offers.length" class="py-10 bg-white">
        <div class="container mx-auto px-4 space-y-10">
            <div class="flex items-end justify-between gap-3">
                <h2 class="text-xl font-black text-slate-900">Offres du moment</h2>
            </div>

            <div v-for="offer in offers" :key="offer.id" class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-lg bg-slate-900 px-3 py-1 text-sm font-bold text-white">{{ offer.label }}</span>
                            <h3 class="text-base font-bold text-slate-900">{{ offer.name }}</h3>
                        </div>
                        <p v-if="offer.description" class="mt-1 text-sm text-slate-600">{{ offer.description }}</p>
                        <p v-else class="mt-1 text-sm text-slate-600">Mélangez les articles de l'offre : le prix du lot s'applique automatiquement au panier.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 md:justify-end">
                        <Countdown v-if="isEndingSoon(offer.ends_at, now)" :ends-at="offer.ends_at" label="Se termine dans" />
                        <p v-else-if="offer.ends_at" class="text-sm text-slate-600">Jusqu'au {{ endDateText(offer.ends_at) }}</p>
                        <Link v-if="offer.category_slug" :href="`/categorie/${offer.category_slug}`"
                            class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-800 hover:bg-slate-100">
                            Voir tout
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <ProductCard v-for="p in offer.products" :key="p.id" :product="p" />
                </div>
            </div>
        </div>
    </section>
</template>
