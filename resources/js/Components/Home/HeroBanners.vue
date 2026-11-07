<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'
import PromoDeadline from '@/Components/Promo/PromoDeadline.vue'

// Grande bannière de l'accueil. L'image s'affiche en entier, sans rien par-dessus quand elle porte
// déjà son message (titre vide) ; toute la bannière mène au lien. La fin de l'offre est dans une bande dessous.
const props = defineProps({
    banners: { type: Array, default: () => [] },
    siteName: { type: String, default: '' },
    featuredProducts: { type: Array, default: () => [] },
    whatsappUrl: { type: String, default: null },
})

const index = ref(0)
let timer = null

function start() {
    clearInterval(timer)
    if (props.banners.length > 1) {
        timer = setInterval(() => { index.value = (index.value + 1) % props.banners.length }, 6000)
    }
}

function show(i) {
    index.value = i
    start()
}

onMounted(start)
onUnmounted(() => clearInterval(timer))

const imageUrl = (path) => (path.startsWith('http') ? path : `/storage/${path}`)
</script>

<template>
    <section v-if="banners.length" class="bg-white">
        <div v-for="(b, i) in banners" v-show="index === i" :key="b.id">
            <component :is="b.link ? 'a' : 'div'" :href="b.link || undefined"
                class="relative block" :class="!b.image && !b.background_color ? 'bg-primary-600 text-primary-contrast' : ''"
                :style="!b.image && b.background_color ? { backgroundColor: b.background_color, color: b.text_color || '#ffffff' } : null">

                <!-- Visuel en entier, jamais recadré -->
                <picture v-if="b.image" class="block bg-slate-100">
                    <source v-if="b.image_mobile" media="(max-width: 767px)" :srcset="b.image_mobile">
                    <!-- Hauteur plafonnée : une image carrée posée en version ordinateur ne prend pas tout l'écran -->
                    <img :src="b.image" :alt="b.title || siteName" class="block w-full h-auto md:max-h-[640px] md:object-contain"
                        :loading="i === 0 ? 'eager' : 'lazy'" :fetchpriority="i === 0 ? 'high' : 'auto'">
                </picture>

                <!-- Texte : sur l'image (ordinateur) seulement si un titre est saisi -->
                <div v-if="b.title"
                    :class="b.image ? 'md:absolute md:inset-y-0 md:left-0 md:flex md:items-center md:w-1/2 bg-primary-950 md:bg-transparent' : ''"
                    :style="b.image ? { backgroundImage: 'linear-gradient(to right, rgba(15,23,42,.75), rgba(15,23,42,0))' } : null">
                    <div class="container mx-auto px-4 py-6 md:py-12" :class="b.image ? 'text-white' : ''">
                        <p v-if="b.subtitle" class="text-xs md:text-sm font-semibold uppercase tracking-wider opacity-70 mb-2">{{ b.subtitle }}</p>
                        <h1 class="text-2xl md:text-4xl font-bold leading-tight max-w-xl">{{ b.title }}</h1>
                        <p v-if="b.description" class="mt-3 text-sm md:text-base opacity-80 max-w-md">{{ b.description }}</p>
                        <span v-if="b.link" class="mt-5 inline-flex h-11 items-center gap-2 rounded-lg px-5 text-sm font-semibold"
                            :class="!b.image && !b.background_color ? 'bg-primary-contrast text-primary-700' : 'bg-primary-600 text-primary-contrast'">
                            {{ b.button_text || "Voir l'offre" }}
                            <ArrowRight class="w-4 h-4" />
                        </span>
                    </div>
                </div>
            </component>

            <!-- Fin de l'offre -->
            <div v-if="b.show_countdown && b.ends_at" class="bg-accent-600 text-accent-contrast">
                <div class="container mx-auto px-4 py-2.5 flex justify-center text-sm">
                    <PromoDeadline :ends-at="b.ends_at" label="Fin de l'offre dans" variant="inline" tone="dark" />
                </div>
            </div>
        </div>

        <!-- Choix de la bannière -->
        <div v-if="banners.length > 1" class="flex justify-center gap-1 py-1">
            <button v-for="(b, i) in banners" :key="b.id" type="button" class="w-11 h-11 flex items-center justify-center"
                :aria-label="`Bannière ${i + 1}`" :aria-current="index === i" @click="show(i)">
                <span class="block h-1.5 rounded-full" :class="index === i ? 'w-6 bg-primary-600' : 'w-1.5 bg-slate-300'" />
            </button>
        </div>
    </section>

    <!-- Aucune bannière : accueil simple -->
    <section v-else class="bg-primary-950">
        <div class="container mx-auto px-4 py-12 md:py-16 flex flex-col lg:flex-row items-center gap-10">
            <div class="flex-1 max-w-lg">
                <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight mb-4">Bienvenue sur {{ siteName }}</h1>
                <p class="text-white/70 mb-8">Découvrez notre sélection de produits.</p>
                <div class="flex flex-wrap gap-3">
                    <Link href="/boutique" class="inline-flex h-11 items-center gap-2 rounded-lg px-6 text-sm font-semibold bg-primary-600 text-primary-contrast">
                        Voir la boutique
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                    <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener"
                        class="inline-flex h-11 items-center rounded-lg bg-[#25D366] hover:bg-[#1ebe5d] px-6 text-sm font-semibold text-white">
                        WhatsApp
                    </a>
                </div>
            </div>
            <div v-if="featuredProducts.length >= 6" class="hidden lg:grid grid-cols-3 gap-2 w-[380px] h-[320px] rounded-xl overflow-hidden">
                <Link v-for="p in featuredProducts.slice(0, 6)" :key="p.id" :href="`/produit/${p.slug}`" class="relative bg-slate-700">
                    <img v-if="p.primary_image" :src="imageUrl(p.primary_image)" :alt="p.name" class="absolute inset-0 w-full h-full object-cover">
                </Link>
            </div>
        </div>
    </section>
</template>
