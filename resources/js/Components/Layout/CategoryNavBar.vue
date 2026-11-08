<script setup>
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { onClickOutside } from '@vueuse/core'
import { LayoutGrid, ChevronDown, ChevronRight, ArrowRight } from 'lucide-vue-next'
import CategoryIcon from '@/Components/Storefront/CategoryIcon.vue'

// Bouton « Catégories » de l'en-tête (ordinateur) : catégories principales à gauche,
// sous-catégories de celle survolée ou ciblée au clavier à droite. Données : nav_categories (partagées).
const page = usePage()
const categories = computed(() => page.props.nav_categories || [])

const open = ref(false)
const activeId = ref(null)
const root = ref(null)

const active = computed(() => categories.value.find((c) => c.id === activeId.value) ?? categories.value[0] ?? null)

function toggle() {
    open.value = !open.value
    if (open.value) activeId.value = categories.value[0]?.id ?? null
}
function close() {
    open.value = false
}

onClickOutside(root, close)
watch(() => page.url, close)
</script>

<template>
    <div v-if="categories.length" ref="root" class="relative" @keydown.esc="close">
        <button type="button" class="flex h-11 items-center gap-2 rounded-lg px-3 text-sm font-semibold transition-colors"
            :class="open ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-100'"
            :aria-expanded="String(open)" aria-controls="header-categories-panel" @click="toggle">
            <LayoutGrid class="h-5 w-5" aria-hidden="true" />
            Catégories
            <ChevronDown class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" aria-hidden="true" />
        </button>

        <div v-if="open" id="header-categories-panel"
            class="absolute left-0 top-full z-50 mt-2 flex w-[min(52rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
            <!-- Catégories principales -->
            <ul class="max-h-[70vh] w-60 shrink-0 overflow-y-auto border-r border-slate-100 py-2">
                <li v-for="cat in categories" :key="cat.id">
                    <a :href="`/categorie/${cat.slug}`"
                        class="flex h-10 items-center gap-3 px-4 text-sm transition-colors"
                        :class="active?.id === cat.id ? 'bg-slate-50 font-medium text-primary-700' : 'text-slate-700 hover:bg-slate-50'"
                        @mouseenter="activeId = cat.id" @focus="activeId = cat.id">
                        <CategoryIcon v-if="cat.icon" :icon="cat.icon" class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                        <span class="min-w-0 flex-1 truncate">{{ cat.name }}</span>
                        <ChevronRight v-if="cat.children?.length" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
                    </a>
                </li>
            </ul>

            <!-- Sous-catégories -->
            <div v-if="active" class="max-h-[70vh] min-w-0 flex-1 overflow-y-auto p-5">
                <div class="mb-4 flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                    <p class="truncate text-base font-semibold text-slate-900">{{ active.name }}</p>
                    <a :href="`/categorie/${active.slug}`" class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-primary-700 hover:underline">
                        Tout voir
                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>

                <div v-if="active.children?.length" class="grid grid-cols-2 gap-x-6 gap-y-4 xl:grid-cols-3">
                    <div v-for="sub in active.children" :key="sub.id" class="min-w-0">
                        <a :href="`/categorie/${sub.slug}`" class="block truncate text-sm font-semibold text-slate-800 hover:text-primary-700">{{ sub.name }}</a>
                        <ul v-if="sub.children?.length" class="mt-1 space-y-0.5">
                            <li v-for="gc in sub.children.slice(0, 6)" :key="gc.id">
                                <a :href="`/categorie/${gc.slug}`" class="block truncate py-0.5 text-[13px] text-slate-500 hover:text-slate-900">{{ gc.name }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <a v-else :href="`/categorie/${active.slug}`"
                    class="inline-flex h-11 items-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-semibold text-primary-contrast hover:bg-primary-700">
                    Voir les produits
                    <ArrowRight class="h-4 w-4" aria-hidden="true" />
                </a>
            </div>
        </div>
    </div>
</template>
