<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed, ref, onUnmounted } from 'vue'
import { Tag, ChevronDown } from 'lucide-vue-next'

const page          = usePage()
const navCategories = computed(() => page.props.nav_categories || [])
const primaryColor  = computed(() => page.props.settings?.primary_color || '#2563EB')

// ─── Mega menu hover state ─────────────────────────────────────────────────────
const hoveredCatId = ref(null)
let   closeTimer   = null

const openCat = (id) => {
    clearTimeout(closeTimer)
    hoveredCatId.value = id
}

const scheduledClose = () => {
    closeTimer = setTimeout(() => { hoveredCatId.value = null }, 150)
}

const cancelClose = () => { clearTimeout(closeTimer) }

const activeCat = computed(() =>
    navCategories.value.find(c => c.id === hoveredCatId.value) ?? null
)

onUnmounted(() => clearTimeout(closeTimer))
</script>

<template>
    <!-- ── Desktop mega-menu bar ──────────────────────────────────────── -->
    <nav class="hidden md:block bg-white border-b border-slate-100 relative z-40"
         :style="{ '--cat-primary': primaryColor }"
         @mouseleave="scheduledClose">
        <div class="container mx-auto px-4">
            <ul class="flex items-stretch h-11 -mx-1">
                <li v-for="cat in navCategories"
                    :key="cat.id"
                    class="relative flex items-stretch"
                    @mouseenter="openCat(cat.id)"
                    @mouseleave="scheduledClose">
                    <a :href="`/categorie/${cat.slug}`"
                       class="cat-item flex items-center gap-1.5 px-3 text-sm font-medium whitespace-nowrap transition-colors duration-150"
                       :class="hoveredCatId === cat.id ? 'cat-item--active' : ''">
                        <Tag class="w-3.5 h-3.5 shrink-0 opacity-60" />
                        {{ cat.name }}
                        <ChevronDown v-if="cat.children?.length"
                                     class="w-3 h-3 opacity-40 transition-transform duration-200"
                                     :class="hoveredCatId === cat.id ? 'rotate-180' : ''" />
                    </a>
                </li>
            </ul>
        </div>

        <!-- Mega-menu panel (full-width, below the bar) -->
        <Transition name="mega">
            <div v-if="activeCat && activeCat.children?.length"
                 class="absolute left-0 right-0 top-full bg-white border-t border-slate-100 shadow-xl z-50"
                 @mouseenter="cancelClose"
                 @mouseleave="scheduledClose">
                <div class="container mx-auto px-4 py-5">

                    <!-- Header row -->
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">{{ activeCat.name }}</h3>
                        <a :href="`/categorie/${activeCat.slug}`"
                           class="cat-link text-xs font-medium transition-colors">
                            Voir tout →
                        </a>
                    </div>

                    <!-- Grid of subcategories — max 4 columns -->
                    <div class="grid gap-x-8 gap-y-0"
                         :style="{ gridTemplateColumns: `repeat(${Math.min(activeCat.children.length, 4)}, 1fr)` }">
                        <div v-for="sub in activeCat.children" :key="sub.id" class="mb-4">
                            <a :href="`/categorie/${sub.slug}`"
                               class="cat-link block font-semibold text-sm text-slate-900 mb-1.5 transition-colors duration-100">
                                {{ sub.name }}
                            </a>
                            <ul v-if="sub.children?.length" class="space-y-0.5">
                                <li v-for="gc in sub.children.slice(0, 6)" :key="gc.id">
                                    <a :href="`/categorie/${gc.slug}`"
                                       class="cat-sublink block text-xs text-slate-500 py-0.5 transition-colors duration-100">
                                        {{ gc.name }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </nav>

    <!-- ── Mobile scrollable chips ────────────────────────────────────── -->
    <nav class="md:hidden bg-white border-b border-slate-100">
        <div class="cat-chips-row flex gap-2 overflow-x-auto px-4 py-2 snap-x">
            <a v-for="cat in navCategories"
               :key="cat.id"
               :href="`/categorie/${cat.slug}`"
               class="flex-shrink-0 flex items-center gap-1.5 px-3 h-11 rounded-full bg-slate-100 text-slate-700 text-xs font-medium snap-start transition-colors whitespace-nowrap active:bg-slate-300 hover:bg-slate-200">
                <Tag class="w-3 h-3 shrink-0 opacity-60" />
                {{ cat.name }}
            </a>
        </div>
    </nav>
</template>

<style scoped>
/* ── Mega menu transition ───────────────────────────────────────── */
.mega-enter-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.mega-leave-active { transition: opacity 0.10s ease, transform 0.10s ease; }
.mega-enter-from,
.mega-leave-to     { opacity: 0; transform: translateY(-8px); }

/* ── Category item styles using CSS variable for primary ────────── */
.cat-item {
    color: #475569; /* slate-600 */
}
.cat-item:hover,
.cat-item--active {
    color: var(--cat-primary, #2563EB);
    background-color: #f8fafc; /* slate-50 */
}

.cat-link {
    color: var(--cat-primary, #2563EB);
}
.cat-link:hover { opacity: 0.75; }

.cat-sublink:hover { color: #0f172a; /* slate-900 */ }

/* ── Hide scrollbar on mobile chips ────────────────────────────── */
.cat-chips-row {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.cat-chips-row::-webkit-scrollbar { display: none; }
</style>
