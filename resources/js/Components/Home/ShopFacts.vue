<script setup>
import { Truck, Banknote, MessageCircle } from 'lucide-vue-next'

// Infos réelles de la boutique (réglages) : livraison, paiement, contact. Rien d'inventé.
defineProps({
    facts: { type: Array, default: () => [] }, // [{ key, title, desc, href }]
})

const ICONS = { delivery: Truck, cod: Banknote, whatsapp: MessageCircle }
</script>

<template>
    <section v-if="facts.length" class="bg-white border-b border-slate-100">
        <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row sm:flex-wrap gap-x-10 gap-y-3">
            <component :is="f.href ? 'a' : 'div'" v-for="f in facts" :key="f.key" :href="f.href || undefined"
                :target="f.href ? '_blank' : undefined" :rel="f.href ? 'noopener' : undefined"
                class="flex items-center gap-3 min-h-11" :class="f.href ? 'hover:text-slate-900' : ''">
                <component :is="ICONS[f.key]" class="w-5 h-5 text-primary-600 shrink-0" aria-hidden="true" />
                <span class="text-sm leading-snug">
                    <span class="font-semibold text-slate-900">{{ f.title }}</span>
                    <span class="text-slate-500"> · {{ f.desc }}</span>
                </span>
            </component>
        </div>
    </section>
</template>
