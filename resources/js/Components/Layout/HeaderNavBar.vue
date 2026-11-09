<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { SHOP_LINKS, isActiveLink } from './shopLinks'

// Deuxième ligne de l'en-tête (tablette et ordinateur) : rubriques de la boutique.
const page = usePage()
const links = computed(() => SHOP_LINKS.map((l) => ({ ...l, active: isActiveLink(l, page.url) })))
</script>

<template>
    <nav class="hidden border-t border-slate-100 bg-white md:block" aria-label="Rubriques de la boutique">
        <div class="container mx-auto flex h-11 items-stretch justify-between gap-6 px-4">
            <ul class="flex items-stretch gap-1">
                <li v-for="l in links" :key="l.href" class="flex">
                    <Link :href="l.href" class="relative flex items-center px-3 text-sm font-medium transition-colors"
                        :class="l.active ? 'text-primary-700' : 'text-slate-600 hover:text-slate-900'"
                        :aria-current="l.active ? 'page' : undefined">
                        {{ l.label }}
                        <span v-if="l.active" class="absolute inset-x-3 bottom-0 h-0.5 rounded-full bg-primary-600" aria-hidden="true" />
                    </Link>
                </li>
            </ul>
            <Link href="/suivi-commande" class="hidden items-center text-sm text-slate-500 hover:text-slate-900 lg:flex">
                Suivi de commande
            </Link>
        </div>
    </nav>
</template>
