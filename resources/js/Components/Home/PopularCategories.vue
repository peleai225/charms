<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronRight } from 'lucide-vue-next'

// « Nos univers populaires » : catégories mises en avant (admin → Catégories → « En vedette »).
const props = defineProps({
    categories: { type: Array, default: () => [] }, // [{ id, name, slug, image, icon, products_count }]
})

// Grille : autant de colonnes que de catégories (jusqu'à 6), sans case vide
const categoryColumns = computed(() => ({
    1: 'sm:grid-cols-1 sm:max-w-xs', 2: 'sm:grid-cols-2 sm:max-w-xl', 3: 'sm:grid-cols-3', 4: 'sm:grid-cols-4',
    5: 'sm:grid-cols-3 lg:grid-cols-5', 6: 'sm:grid-cols-3 lg:grid-cols-6',
})[Math.min(props.categories.length, 6)] || 'sm:grid-cols-3')
</script>

<template>
        <section v-if="categories.length" class="py-12 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black text-slate-900">Catégories</h2>
                    <Link href="/boutique"
                          class="h-11 text-sm font-semibold flex items-center gap-1 text-primary-700 hover:underline">
                        Tout voir
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>
                <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 snap-x sm:grid sm:overflow-visible sm:pb-0 scrollbar-none"
                     :class="categoryColumns">
                    <Link v-for="cat in categories" :key="cat.id"
                          :href="`/categorie/${cat.slug}`"
                          class="group flex-shrink-0 w-32 sm:w-auto snap-start">
                        <div class="relative rounded-xl overflow-hidden bg-slate-200" style="aspect-ratio: 1/1">
                            <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover"/>
                            <div v-else class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-slate-400">
                                {{ cat.name[0] }}
                            </div>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-slate-900 leading-tight group-hover:underline">{{ cat.name }}</p>
                        <p v-if="cat.products_count" class="text-xs text-slate-500 mt-0.5">
                            {{ cat.products_count }} article{{ cat.products_count > 1 ? 's' : '' }}
                        </p>
                    </Link>
                </div>
            </div>
        </section>
</template>
