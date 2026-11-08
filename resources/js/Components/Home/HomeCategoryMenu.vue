<script setup>
import { computed, ref, onUnmounted } from 'vue'
import { ChevronRight, ArrowRight } from 'lucide-vue-next'
import CategoryIcon from '@/Components/Storefront/CategoryIcon.vue'

// Colonne de gauche de l'accueil (ordinateur) : catégories principales, sous-catégories au survol ou au clavier.
const props = defineProps({
    categories: { type: Array, default: () => [] }, // [{ id, name, slug, icon, children: [{ id, name, slug }] }]
    max: { type: Number, default: 10 },
})

const visible = computed(() => props.categories.slice(0, props.max))

const openId = ref(null)
let timer = null

function open(id) {
    clearTimeout(timer)
    openId.value = id
}
function closeSoon() {
    clearTimeout(timer)
    timer = setTimeout(() => { openId.value = null }, 120)
}
function onFocusOut(e) {
    if (!e.currentTarget.contains(e.relatedTarget)) openId.value = null
}

onUnmounted(() => clearTimeout(timer))
</script>

<template>
    <nav aria-labelledby="home-categories-title" class="flex flex-col rounded-xl border border-slate-200 bg-white"
        @keydown.esc="openId = null">
        <h2 id="home-categories-title" class="flex h-11 items-center border-b border-slate-100 px-4 text-sm font-semibold text-slate-900">
            Toutes les catégories
        </h2>

        <ul class="flex-1 py-1">
            <li v-for="cat in visible" :key="cat.id" class="relative"
                @mouseenter="open(cat.id)" @mouseleave="closeSoon" @focusin="open(cat.id)" @focusout="onFocusOut">
                <a :href="`/categorie/${cat.slug}`"
                    class="flex h-9 items-center gap-3 px-4 text-sm transition-colors"
                    :class="openId === cat.id ? 'bg-slate-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50'"
                    :aria-expanded="cat.children?.length ? String(openId === cat.id) : undefined">
                    <CategoryIcon :icon="cat.icon" class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                    <span class="min-w-0 flex-1 truncate">{{ cat.name }}</span>
                    <ChevronRight v-if="cat.children?.length" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
                </a>

                <!-- Sous-catégories -->
                <div v-if="cat.children?.length && openId === cat.id"
                    class="absolute left-full top-0 z-30 ml-2 max-h-[70vh] w-64 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                    <a :href="`/categorie/${cat.slug}`"
                        class="flex h-10 items-center rounded-lg px-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 hover:text-primary-700">
                        Tout « {{ cat.name }} »
                    </a>
                    <a v-for="child in cat.children" :key="child.id" :href="`/categorie/${child.slug}`"
                        class="flex h-10 items-center rounded-lg px-3 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary-700">
                        <span class="truncate">{{ child.name }}</span>
                    </a>
                </div>
            </li>
        </ul>

        <a href="/boutique"
            class="flex h-11 items-center justify-between border-t border-slate-100 px-4 text-sm font-medium text-primary-700 hover:bg-slate-50">
            Voir toute la boutique
            <ArrowRight class="h-4 w-4" aria-hidden="true" />
        </a>
    </nav>
</template>
