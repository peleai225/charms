<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Zap } from 'lucide-vue-next'
import ProductCard from '@/Components/ProductCard.vue'
import PromoDeadline from '@/Components/Promo/PromoDeadline.vue'

// Page /ventes-flash : chaque vente en cours avec son heure de fin et ses produits au prix flash.
// Un produit couvert par plusieurs ventes n'apparaît que dans celle qui fixe son prix.
defineProps({
    sales: { type: Array, default: () => [] }, // [{ id, name, discount_percent, ends_at, products: [...] }]
})

// Une vente se termine pendant la visite : on relit la liste au serveur
const onEnded = () => router.reload({ only: ['sales'] })
</script>

<template>
    <FrontLayout title="Ventes flash">
        <Head>
            <title>Ventes flash</title>
            <meta name="description" content="Les ventes flash en cours : remises limitées dans le temps." />
        </Head>

        <div class="bg-accent-600 text-accent-contrast">
            <div class="container mx-auto px-4 py-8">
                <h1 class="text-2xl md:text-3xl font-bold">Ventes Flash</h1>
                <p class="mt-1 text-sm opacity-90">
                    Offres limitées dans le temps : le prix remisé s'applique au panier jusqu'à la fin de la vente.
                </p>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div v-if="sales.length" class="space-y-12">
                <section v-for="sale in sales" :key="sale.id" :aria-labelledby="`flash-sale-${sale.id}`">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 :id="`flash-sale-${sale.id}`" class="text-xl font-bold text-slate-900">{{ sale.name }}</h2>
                            <span class="inline-flex items-center rounded-full border border-accent-200 bg-accent-50 px-2.5 py-0.5 text-xs font-semibold text-accent-800">
                                -{{ sale.discount_percent }} %
                            </span>
                        </div>
                        <PromoDeadline :ends-at="sale.ends_at" label="Se termine dans" variant="inline"
                            class="text-sm text-slate-700" @ended="onEnded" />
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <ProductCard v-for="product in sale.products" :key="product.id" :product="product" />
                    </div>
                </section>
            </div>

            <!-- Aucune vente en cours -->
            <div v-else class="max-w-md mx-auto text-center py-16">
                <Zap class="w-10 h-10 mx-auto text-slate-300" />
                <h2 class="mt-4 text-lg font-semibold text-slate-900">Aucune vente flash en cours</h2>
                <p class="mt-1 text-sm text-slate-500">Les prochaines offres limitées s'afficheront ici.</p>
                <Link href="/boutique"
                    class="mt-6 inline-flex h-11 items-center justify-center rounded-lg bg-primary-600 px-5 text-sm font-semibold text-primary-contrast hover:bg-primary-700 transition-colors">
                    Voir la boutique
                </Link>
            </div>
        </div>
    </FrontLayout>
</template>
