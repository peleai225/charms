<script setup>
import { computed } from 'vue'
import { ArrowRight } from 'lucide-vue-next'

// Petite carte à côté de la grande bannière (bannière position « home_side »).
// Avec un titre : texte sur la couleur de fond choisie (sinon couleurs du thème), l'image en illustration.
// Sans titre : l'image porte déjà son message, elle s'affiche seule et en entier.
const props = defineProps({
    banner: { type: Object, required: true },
})

const b = computed(() => props.banner)

// Texte lisible sur le fond choisi quand aucune couleur de texte n'est saisie (luminance WCAG)
function readableOn(hex) {
    const m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || '')
    if (!m) return null
    const [r, g, bl] = m.slice(1).map((h) => {
        const c = parseInt(h, 16) / 255
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4
    })
    const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * bl
    return 1.05 / (luminance + 0.05) >= (luminance + 0.05) / 0.0588 ? '#FFFFFF' : '#0F172A'
}

const colorStyle = computed(() => b.value.background_color
    ? { backgroundColor: b.value.background_color, color: b.value.text_color || readableOn(b.value.background_color) }
    : null)
</script>

<template>
    <component :is="b.link ? 'a' : 'div'" :href="b.link || undefined"
        class="group relative block h-full overflow-hidden rounded-xl"
        :class="[b.title ? 'min-h-36 p-4 sm:aspect-[3/2] xl:aspect-auto' : '', b.title && !b.background_color ? 'bg-primary-50 text-primary-900' : '', !b.title ? 'bg-slate-100' : '']"
        :style="b.title ? colorStyle : null">

        <!-- Visuel seul, en entier -->
        <picture v-if="!b.title && b.image" class="block h-full">
            <source v-if="b.image_mobile" media="(max-width: 767px)" :srcset="b.image_mobile">
            <img :src="b.image" :alt="b.subtitle || b.description || 'Bannière'" loading="lazy" class="block h-full w-full object-contain">
        </picture>

        <!-- Texte + illustration -->
        <template v-else-if="b.title">
            <img v-if="b.image" :src="b.image" alt="" loading="lazy" aria-hidden="true"
                class="pointer-events-none absolute bottom-0 right-0 h-full w-1/2 object-contain object-right-bottom">
            <div class="relative flex h-full max-w-[60%] flex-col">
                <p v-if="b.subtitle" class="text-xs font-medium opacity-80">{{ b.subtitle }}</p>
                <p class="mt-0.5 line-clamp-3 text-base font-bold leading-snug">{{ b.title }}</p>
                <span v-if="b.link" class="mt-auto inline-flex items-center gap-1.5 pt-3 text-sm font-semibold underline-offset-4 group-hover:underline">
                    {{ b.button_text || 'Découvrir' }}
                    <ArrowRight class="h-4 w-4" aria-hidden="true" />
                </span>
            </div>
        </template>
    </component>
</template>
