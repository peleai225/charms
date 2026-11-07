<script setup>
import { ArrowRight } from 'lucide-vue-next'
import PromoDeadline from './PromoDeadline.vue'

// Bannières promotionnelles de l'accueil (milieu, bas de page).
// Sans couleur propre à la bannière, on suit le thème du back-office (couleur principale).
defineProps({
    banners: { type: Array, default: () => [] },
})

const hasCountdown = (b) => b.show_countdown && b.ends_at
const themed = (b) => !b.image && !b.background_color
</script>

<template>
    <section v-if="banners.length" class="bg-slate-50 py-6">
        <div class="container mx-auto px-4 grid gap-4" :class="banners.length > 1 ? 'md:grid-cols-2' : ''">
            <component :is="b.link ? 'a' : 'div'" v-for="b in banners" :key="b.id" :href="b.link || undefined"
                class="group relative block overflow-hidden rounded-2xl"
                :class="[!b.image ? 'min-h-40' : '', themed(b) ? 'bg-primary-600 text-primary-contrast' : '']"
                :style="!b.image && b.background_color ? { backgroundColor: b.background_color, color: b.text_color || '#ffffff' } : null">

                <!-- Bannière visuelle : l'image porte le message -->
                <template v-if="b.image">
                    <picture>
                        <source v-if="b.image_mobile" media="(max-width: 767px)" :srcset="b.image_mobile">
                        <img :src="b.image" :alt="b.title || 'Promotion'" loading="lazy"
                            class="w-full h-auto block">
                    </picture>
                    <div v-if="b.title || hasCountdown(b)"
                        class="absolute inset-x-0 bottom-0 p-4 sm:p-6"
                        style="background: linear-gradient(to top, rgba(0,0,0,0.65), rgba(0,0,0,0))">
                        <h3 v-if="b.title" class="text-lg sm:text-2xl font-bold leading-tight text-white">{{ b.title }}</h3>
                        <p v-if="b.subtitle" class="mt-1 text-sm text-white/85">{{ b.subtitle }}</p>
                        <div v-if="hasCountdown(b)" class="mt-3 text-white"><PromoDeadline :ends-at="b.ends_at" tone="dark" /></div>
                    </div>
                </template>

                <!-- Bannière sans image : bloc de couleur -->
                <div v-else class="relative px-6 sm:px-8 py-8 max-w-xl">
                    <h3 class="text-2xl font-bold leading-tight mb-2">{{ b.title }}</h3>
                    <p v-if="b.subtitle" class="text-sm opacity-80 mb-4">{{ b.subtitle }}</p>
                    <div v-if="hasCountdown(b)" class="mb-5"><PromoDeadline :ends-at="b.ends_at" tone="inherit" /></div>
                    <span v-if="b.link"
                        class="inline-flex h-11 items-center gap-2 rounded-lg px-5 text-sm font-semibold"
                        :class="themed(b) ? 'bg-primary-contrast text-primary-700' : 'bg-white'"
                        :style="!themed(b) ? { color: b.background_color } : null">
                        {{ b.button_text || 'Découvrir' }}
                        <ArrowRight class="w-4 h-4" />
                    </span>
                </div>
            </component>
        </div>
    </section>
</template>
