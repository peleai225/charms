<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    banners:   Object,   // paginated
    positions: Object,
    types:     Object,
    filters:   Object,
})

const page = usePage()

// ── Filtres ───────────────────────────────────────────────────────────────────
const filterPosition = ref(props.filters?.position ?? '')
const filterStatus   = ref(props.filters?.status ?? '')

function applyFilters() {
    router.get(route('admin.banners.index'), {
        position: filterPosition.value || undefined,
        status:   filterStatus.value || undefined,
    }, { preserveState: true, replace: true })
}

function clearFilters() {
    filterPosition.value = ''
    filterStatus.value   = ''
    router.get(route('admin.banners.index'), {}, { replace: true })
}

const hasFilters = computed(() => filterPosition.value || filterStatus.value)

// ── Toggle actif/inactif ──────────────────────────────────────────────────────
const togglingId = ref(null)

function toggleBanner(banner) {
    togglingId.value = banner.id
    router.patch(route('admin.banners.toggle', banner.id), {}, {
        preserveScroll: true,
        onFinish: () => { togglingId.value = null },
    })
}

// ── Suppression ───────────────────────────────────────────────────────────────
function deleteBanner(banner) {
    if (!confirm('Supprimer cette bannière ?')) return
    router.delete(route('admin.banners.destroy', banner.id), { preserveScroll: true })
}

// ── Groupement par position ───────────────────────────────────────────────────
const groupedByPosition = computed(() => {
    const groups = {}
    for (const key of Object.keys(props.positions)) {
        groups[key] = (props.banners.data ?? []).filter(b => b.position === key)
    }
    return groups
})

const positionColors = {
    announcement_bar: { bg: 'bg-amber-100',   text: 'text-amber-700',   dot: 'bg-amber-500' },
    popup_center:     { bg: 'bg-purple-100',  text: 'text-purple-700',  dot: 'bg-purple-500' },
    home_hero:        { bg: 'bg-blue-100',    text: 'text-blue-700',    dot: 'bg-blue-500' },
    home_middle:      { bg: 'bg-green-100',   text: 'text-green-700',   dot: 'bg-green-500' },
    home_bottom:      { bg: 'bg-teal-100',    text: 'text-teal-700',    dot: 'bg-teal-500' },
    category_top:     { bg: 'bg-indigo-100',  text: 'text-indigo-700',  dot: 'bg-indigo-500' },
    product_sidebar:  { bg: 'bg-rose-100',    text: 'text-rose-700',    dot: 'bg-rose-500' },
    cart_bottom:      { bg: 'bg-orange-100',  text: 'text-orange-700',  dot: 'bg-orange-500' },
    checkout_top:     { bg: 'bg-cyan-100',    text: 'text-cyan-700',    dot: 'bg-cyan-500' },
}

function positionColor(key) {
    return positionColors[key] ?? { bg: 'bg-gray-100', text: 'text-gray-600', dot: 'bg-gray-400' }
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Bannières</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ banners.total }} bannière(s) au total</p>
            </div>
            <a :href="route('admin.banners.create')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle bannière
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap gap-3 items-center">
                <select v-model="filterPosition" @change="applyFilters"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Toutes les positions</option>
                    <option v-for="(label, key) in positions" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="filterStatus" @change="applyFilters"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actives</option>
                    <option value="inactive">Inactives</option>
                </select>
                <button v-if="hasFilters" type="button" @click="clearFilters"
                    class="h-9 px-3 inline-flex items-center text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Effacer
                </button>
            </div>
        </div>

        <!-- Bannières groupées par position -->
        <template v-if="banners.data.length > 0">
            <template v-for="(posLabel, posKey) in positions" :key="posKey">
                <div v-if="groupedByPosition[posKey]?.length > 0" class="space-y-3">

                    <!-- Titre position -->
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full" :class="positionColor(posKey).dot"></span>
                        <h3 class="text-[13px] font-semibold text-gray-900">{{ posLabel }}</h3>
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-medium"
                            :class="[positionColor(posKey).bg, positionColor(posKey).text]">
                            {{ groupedByPosition[posKey].length }} ·
                            {{ groupedByPosition[posKey].filter(b => b.is_active).length }} active(s)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="banner in groupedByPosition[posKey].slice().sort((a,b) => a.order - b.order)"
                            :key="banner.id"
                            class="bg-white rounded-xl border shadow-sm overflow-hidden group"
                            :class="banner.is_active ? 'border-gray-200' : 'border-gray-100 opacity-60'">

                            <!-- Image -->
                            <div class="relative aspect-[16/7] bg-gray-100 overflow-hidden">
                                <img v-if="banner.image"
                                    :src="'/storage/' + banner.image"
                                    :alt="banner.title ?? ''"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 gap-2">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span v-if="banner.title" class="text-[12px] font-medium text-gray-500">{{ banner.title }}</span>
                                </div>

                                <!-- Overlay actions -->
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <a :href="route('admin.banners.edit', banner.id)"
                                        class="p-2 bg-white rounded-lg text-gray-700 hover:text-blue-600 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a v-if="banner.link" :href="banner.link" target="_blank"
                                        class="p-2 bg-white rounded-lg text-gray-700 hover:text-green-600 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                    <button type="button" @click="deleteBanner(banner)"
                                        class="p-2 bg-white rounded-lg text-gray-700 hover:text-red-600 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Badge ordre -->
                                <div class="absolute top-2 left-2">
                                    <span class="px-1.5 py-0.5 bg-black/50 text-white text-[10px] font-bold rounded">#{{ banner.order }}</span>
                                </div>
                            </div>

                            <!-- Infos + toggle -->
                            <div class="p-3">
                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                    <div class="min-w-0">
                                        <h4 class="text-[13px] font-medium text-gray-900 truncate">{{ banner.title ?? 'Sans titre' }}</h4>
                                        <p v-if="banner.subtitle" class="text-[11px] text-gray-400 truncate">{{ banner.subtitle }}</p>
                                    </div>
                                    <!-- Toggle -->
                                    <button type="button"
                                        :disabled="togglingId === banner.id"
                                        @click="toggleBanner(banner)"
                                        :class="banner.is_active ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 hover:bg-gray-400'"
                                        class="relative w-9 h-5 rounded-full flex-shrink-0 transition-colors duration-200 disabled:opacity-60">
                                        <span
                                            :class="banner.is_active ? 'translate-x-4' : 'translate-x-0.5'"
                                            class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200">
                                        </span>
                                    </button>
                                </div>
                                <p v-if="banner.starts_at || banner.ends_at" class="text-[10px] text-gray-400">
                                    <span v-if="banner.starts_at">Du {{ banner.starts_at }}</span>
                                    <span v-if="banner.ends_at"> au {{ banner.ends_at }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Ajouter dans cette position -->
                        <a :href="route('admin.banners.create') + '?position=' + posKey"
                            class="border-2 border-dashed border-gray-200 hover:border-blue-400 rounded-xl flex flex-col items-center justify-center gap-2 py-8 text-gray-400 hover:text-blue-500 transition-colors min-h-[120px]">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-[12px] font-medium">Ajouter ici</span>
                        </a>
                    </div>
                </div>
            </template>
        </template>

        <!-- Empty state -->
        <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm p-16 text-center">
            <p class="text-[13px] text-gray-400 mb-1">Aucune bannière</p>
            <p class="text-[12px] text-gray-300 mb-5">Créez votre première bannière pour personnaliser votre boutique</p>
            <a :href="route('admin.banners.create')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mx-auto">
                Créer une bannière
            </a>
        </div>

        <!-- Pagination -->
        <div v-if="banners.last_page > 1" class="flex items-center justify-center gap-1">
            <template v-for="link in banners.links" :key="link.label">
                <a v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-1.5 text-[13px] rounded-lg border transition-colors"
                    :class="link.active
                        ? 'bg-blue-600 border-blue-600 text-white'
                        : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                </a>
                <span v-else v-html="link.label"
                    class="px-3 py-1.5 text-[13px] text-gray-300 rounded-lg border border-gray-100 cursor-default">
                </span>
            </template>
        </div>

    </div>
</template>
