<script setup>
import { computed } from 'vue'
import {
    Truck, ShieldCheck, RotateCcw, Headset, Banknote, Gift, MessageCircle, Package, Clock, MapPin,
} from 'lucide-vue-next'

// Engagements sous la grande bannière : saisis dans Admin → Paramètres → Accueil,
// sinon infos réelles tirées des réglages (livraison, paiement, WhatsApp). Rien d'inventé.
const props = defineProps({
    facts: { type: Array, default: () => [] }, // [{ key, title, desc, href }]
})

// Clés automatiques (HomeController::shopFacts) + icônes au choix (App\Support\HomeEngagements::ICONS)
const ICONS = {
    delivery: Truck, cod: Banknote, whatsapp: MessageCircle,
    truck: Truck, 'shield-check': ShieldCheck, 'rotate-ccw': RotateCcw, headset: Headset, banknote: Banknote,
    gift: Gift, 'message-circle': MessageCircle, package: Package, clock: Clock, 'map-pin': MapPin,
}

// 2 colonnes sur téléphone, jusqu'à 4 sur ordinateur (5 ou 6 engagements : 2 lignes de 3)
const LG_COLS = { 1: 'lg:grid-cols-1', 2: 'lg:grid-cols-2', 3: 'lg:grid-cols-3', 4: 'lg:grid-cols-4' }
const gridClass = computed(() => [
    props.facts.length === 1 ? 'grid-cols-1' : 'grid-cols-2',
    LG_COLS[props.facts.length] ?? 'lg:grid-cols-3',
])

const isExternal = (href) => /^https?:\/\//i.test(href || '')
</script>

<template>
    <section v-if="facts.length" class="bg-white border-b border-slate-100" aria-label="Nos engagements">
        <ul class="container mx-auto px-4 py-4 md:py-5 grid gap-3 md:gap-4" :class="gridClass">
            <li v-for="(f, i) in facts" :key="`${f.key}-${i}`">
                <component :is="f.href ? 'a' : 'div'" :href="f.href || undefined"
                    :target="isExternal(f.href) ? '_blank' : undefined" :rel="isExternal(f.href) ? 'noopener' : undefined"
                    class="flex h-full min-h-11 items-start gap-2.5 rounded-xl border border-slate-200 p-3 sm:gap-3 md:p-4"
                    :class="[facts.length > 1 ? 'flex-col sm:flex-row' : '', f.href ? 'transition-colors hover:border-primary-300 hover:bg-slate-50' : '']">
                    <!-- Téléphone, 2 colonnes : icône au-dessus du texte pour lui laisser toute la largeur -->
                    <span class="flex w-9 h-9 sm:w-10 sm:h-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                        <component :is="ICONS[f.key] ?? Package" class="w-5 h-5" aria-hidden="true" />
                    </span>
                    <span class="min-w-0 pt-0.5">
                        <span class="block text-[13px] md:text-sm font-semibold leading-snug text-slate-900">{{ f.title }}</span>
                        <span v-if="f.desc" class="mt-0.5 block text-xs md:text-[13px] leading-snug text-slate-500">{{ f.desc }}</span>
                    </span>
                </component>
            </li>
        </ul>
    </section>
</template>
