<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'
import ProductCard from '@/Components/ProductCard.vue'
import Countdown from '@/Components/Promo/Countdown.vue'

// « Ventes Flash » : la vente en cours qui se termine le plus tôt (FlashSaleShowcase::current).
// Panneau aux couleurs d'accent du thème + compte à rebours, et ses produits sur une ligne qui défile.
// Rien ne s'affiche sans vente en cours ni produit actif.
defineProps({
    sale: { type: Object, default: null }, // { id, name, discount_percent, ends_at, products: [...] }
})

// La vente se termine pendant la visite : le serveur rend la suivante, ou plus rien.
const onEnded = () => router.reload({ only: ['flash_sale'] })
</script>

<template>
    <section v-if="sale && sale.products?.length" class="py-8 bg-white" aria-labelledby="flash-sale-title">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
                <!-- Panneau -->
                <div class="lg:w-72 shrink-0 rounded-2xl bg-accent-600 text-accent-contrast p-5 sm:p-6 flex flex-col gap-5">
                    <div>
                        <h2 id="flash-sale-title" class="text-2xl font-bold leading-tight">Ventes Flash</h2>
                        <p class="mt-1 text-sm opacity-90">Offres limitées dans le temps</p>
                    </div>

                    <Countdown :ends-at="sale.ends_at" label="Se termine dans" tone="inherit" @ended="onEnded" />

                    <Link href="/ventes-flash"
                        class="lg:mt-auto inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-accent-contrast px-4 text-sm font-semibold text-accent-600 hover:opacity-90 transition-opacity">
                        Voir toutes les offres
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>

                <!-- Produits -->
                <div class="min-w-0 flex-1">
                    <div class="flex gap-4 overflow-x-auto snap-x pb-2 -mx-4 px-4 lg:mx-0 lg:px-0">
                        <div v-for="product in sale.products" :key="product.id" class="w-44 sm:w-52 shrink-0 snap-start">
                            <ProductCard :product="product" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
