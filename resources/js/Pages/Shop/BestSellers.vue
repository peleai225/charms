<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { TrendingUp, ArrowRight } from 'lucide-vue-next'

// Meilleures ventes : classement calculé par Laravel sur les ventes réelles des derniers jours.
const props = defineProps({
    products: { type: Array, default: () => [] },
    days: { type: Number, default: 90 },
})

const siteName = computed(() => usePage().props.settings?.site_name || '')
</script>

<template>
    <FrontLayout title="Meilleures ventes">
        <Head>
            <meta name="description" :content="`Les articles les plus commandés sur ${siteName || 'notre boutique'} ces ${days} derniers jours.`" />
        </Head>

        <div class="min-h-[60vh] bg-slate-50">
            <div class="container mx-auto px-4 py-8 md:py-10">
                <nav class="mb-3 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Fil d'Ariane">
                    <Link href="/" class="hover:text-slate-900">Accueil</Link>
                    <span aria-hidden="true">/</span>
                    <span class="text-slate-900">Meilleures ventes</span>
                </nav>

                <header class="mb-6 md:mb-8">
                    <h1 class="text-2xl font-bold text-slate-900 md:text-[32px] md:leading-tight">Meilleures ventes</h1>
                    <p class="mt-1 text-sm text-slate-500">Les articles les plus commandés ces {{ days }} derniers jours.</p>
                </header>

                <div v-if="products.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <ProductCard v-for="p in products" :key="p.id" :product="p" />
                </div>

                <!-- Aucune vente sur la période -->
                <div v-else class="rounded-2xl border border-slate-200 bg-white px-4 py-16 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-700">
                        <TrendingUp class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <h2 class="text-lg font-semibold text-slate-900">Pas encore de classement</h2>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                        Les meilleures ventes apparaîtront ici dès les premières commandes. En attendant, découvrez toute la boutique.
                    </p>
                    <Link href="/boutique"
                        class="mt-6 inline-flex h-11 items-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-semibold text-primary-contrast hover:bg-primary-700">
                        Voir la boutique
                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
