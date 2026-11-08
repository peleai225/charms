<script setup>
import { computed } from 'vue'
import HeroBanners from './HeroBanners.vue'
import HomeCategoryMenu from './HomeCategoryMenu.vue'
import SideBannerCard from './SideBannerCard.vue'

// Haut de l'accueil (ordinateur) : catégories à gauche, grande bannière au centre, petites cartes à droite.
// Tablette / téléphone : la grande bannière, puis les petites cartes dessous ; les catégories passent par l'en-tête.
const props = defineProps({
    heroBanners: { type: Array, default: () => [] },
    sideBanners: { type: Array, default: () => [] }, // bannières position « home_side »
    menuCategories: { type: Array, default: () => [] }, // [{ id, name, slug, icon, children }]
    siteName: { type: String, default: '' },
    featuredProducts: { type: Array, default: () => [] },
    whatsappUrl: { type: String, default: null },
})

// Deux cartes au plus ; une carte sans titre ni image n'aurait rien à montrer
const cards = computed(() => props.sideBanners.filter((b) => b.title || b.image).slice(0, 2))
const hasMenu = computed(() => props.menuCategories.length > 0)

// Classes écrites en entier pour que Tailwind les génère
const layout = computed(() => {
    if (hasMenu.value && cards.value.length) {
        return {
            grid: 'lg:grid-cols-[15rem_minmax(0,1fr)] xl:grid-cols-[15rem_minmax(0,1fr)_17rem]',
            side: 'lg:col-span-2 xl:col-span-1 xl:col-start-3 xl:row-start-1',
        }
    }
    if (hasMenu.value) return { grid: 'lg:grid-cols-[15rem_minmax(0,1fr)]', side: '' }
    if (cards.value.length) return { grid: 'xl:grid-cols-[minmax(0,1fr)_17rem]', side: 'xl:col-start-2 xl:row-start-1' }

    return { grid: '', side: '' }
})
</script>

<template>
    <section class="bg-white lg:bg-slate-50 lg:py-5" aria-label="À la une">
        <div class="lg:container lg:mx-auto lg:px-4">
            <div class="lg:grid lg:gap-4" :class="layout.grid">
                <HomeCategoryMenu v-if="hasMenu" :categories="menuCategories" class="hidden lg:flex" />

                <div class="min-w-0">
                    <HeroBanners :banners="heroBanners" :site-name="siteName" :featured-products="featuredProducts"
                        :whatsapp-url="whatsappUrl" framed />
                </div>

                <div v-if="cards.length" class="grid grid-cols-1 items-start gap-3 px-4 py-4 lg:gap-4 lg:p-0 xl:items-stretch"
                    :class="[cards.length > 1 ? 'sm:grid-cols-2 xl:grid-cols-1 xl:grid-rows-2' : '', layout.side]">
                    <SideBannerCard v-for="b in cards" :key="b.id" :banner="b" />
                </div>
            </div>
        </div>
    </section>
</template>
