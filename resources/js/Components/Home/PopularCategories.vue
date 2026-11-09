<script setup>
import { Link } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'
import CategoryIcon from '@/Components/Storefront/CategoryIcon.vue'

// « Nos univers populaires » : catégories « Mise en avant » de l'admin (sinon les catégories
// principales qui ont des produits) — App\Services\Storefront\PopularCategories, 12 au plus.
// Mobile et tablette : une rangée qui défile ; grand écran : 6 par rangée, dernière rangée centrée
// (largeur d'une tuile = (100 % − 5 espaces de 1rem) / 6).
defineProps({
    categories: { type: Array, default: () => [] }, // [{ id, name, slug, image, icon, products_count }]
})

const count = new Intl.NumberFormat('fr-FR')
const articles = (n) => `${count.format(n)} article${n > 1 ? 's' : ''}`
</script>

<template>
    <section v-if="categories.length" class="py-10 bg-white" aria-labelledby="popular-categories-title">
        <div class="container mx-auto px-4">
            <div class="flex items-end justify-between gap-4 mb-5">
                <h2 id="popular-categories-title" class="text-xl sm:text-2xl font-bold text-slate-900">Nos univers populaires</h2>
                <Link href="/boutique" aria-label="Voir toutes les catégories"
                      class="h-11 shrink-0 inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:underline">
                    <span class="sm:hidden">Tout voir</span>
                    <span class="hidden sm:inline">Voir toutes les catégories</span>
                    <ArrowRight class="w-4 h-4" aria-hidden="true" />
                </Link>
            </div>

            <ul class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-px-4 -mx-4 px-4 pb-2
                       lg:flex-wrap lg:justify-center lg:gap-x-4 lg:gap-y-6 lg:overflow-visible lg:mx-0 lg:px-0 lg:pb-0
                       [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <li v-for="cat in categories" :key="cat.id"
                    class="w-28 sm:w-32 shrink-0 snap-start lg:w-[calc(16.6666%-0.8333rem)]">
                    <Link :href="`/categorie/${cat.slug}`"
                          class="group block rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                        <span class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl bg-primary-50 p-3 transition-colors group-hover:bg-primary-100">
                            <img v-if="cat.image" :src="`/storage/${cat.image}`" alt="" loading="lazy"
                                 class="h-full w-full object-contain" />
                            <CategoryIcon v-else :icon="cat.icon" class="w-10 h-10 lg:w-12 lg:h-12 text-primary-700" />
                        </span>
                        <span class="mt-2 block text-center text-sm font-semibold leading-snug text-slate-900 line-clamp-2 group-hover:text-primary-700">
                            {{ cat.name }}
                        </span>
                        <span v-if="cat.products_count" class="mt-0.5 block text-center text-xs text-slate-500">
                            {{ articles(cat.products_count) }}
                        </span>
                    </Link>
                </li>
            </ul>
        </div>
    </section>
</template>
