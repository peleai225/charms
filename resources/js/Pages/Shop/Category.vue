<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';
import { SlidersHorizontal, X } from 'lucide-vue-next';

const props = defineProps({
    category:      Object,
    subcategories: Array,
    products:      Object,
    filters:       Object,
});

const { formatPrice } = useHelpers();

const sortOptions = [
    { value: 'newest',     label: 'Plus récents' },
    { value: 'price_asc',  label: 'Prix croissant' },
    { value: 'price_desc', label: 'Prix décroissant' },
    { value: 'name',       label: 'Nom A–Z' },
    { value: 'popular',    label: 'Populaires' },
];

const localFilters = ref({
    min_price: props.filters?.min_price || '',
    max_price: props.filters?.max_price || '',
    on_sale:   props.filters?.on_sale   || '',
    sort:      props.filters?.sort      || 'newest',
});

const filterPanelOpen = ref(false);

const applyFilters = () => {
    const params = {};
    if (localFilters.value.min_price) params.min_price = localFilters.value.min_price;
    if (localFilters.value.max_price) params.max_price = localFilters.value.max_price;
    if (localFilters.value.on_sale)   params.on_sale   = '1';
    if (localFilters.value.sort)      params.sort      = localFilters.value.sort;
    router.get(`/categorie/${props.category.slug}`, params, { preserveState: true, preserveScroll: true });
    filterPanelOpen.value = false;
};

const applySort = (val) => {
    localFilters.value.sort = val;
    applyFilters();
};

const activeChips = computed(() => {
    const chips = [];
    if (props.filters?.min_price) chips.push({ key: 'min_price', label: `Min ${formatPrice(props.filters.min_price)}` });
    if (props.filters?.max_price) chips.push({ key: 'max_price', label: `Max ${formatPrice(props.filters.max_price)}` });
    if (props.filters?.on_sale)   chips.push({ key: 'on_sale',   label: 'En promotion' });
    return chips;
});

const removeFilter = (key) => {
    localFilters.value[key] = '';
    applyFilters();
};

const clearAll = () => {
    localFilters.value.min_price = '';
    localFilters.value.max_price = '';
    localFilters.value.on_sale   = '';
    applyFilters();
};

const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveScroll: true });
};
</script>

<template>
    <FrontLayout :title="`${category.name} — Boutique`">
        <Head>
            <title>{{ category.name }}</title>
            <meta v-if="category.description" name="description" :content="category.description" />
        </Head>

        <!-- Hero catégorie -->
        <div class="relative bg-slate-900 overflow-hidden">
            <img
                v-if="category.image"
                :src="`/storage/${category.image}`"
                :alt="category.name"
                class="absolute inset-0 w-full h-full object-cover opacity-30"
            />
            <div class="relative container mx-auto px-4 py-14">
                <!-- Breadcrumb -->
                <nav class="flex items-center flex-wrap gap-1.5 text-xs text-slate-400 mb-4">
                    <Link href="/" class="hover:text-slate-200 transition">Accueil</Link>
                    <span>/</span>
                    <Link href="/boutique" class="hover:text-slate-200 transition">Boutique</Link>
                    <template v-for="ancestor in (category.ancestors ?? [])" :key="ancestor.id">
                        <span>/</span>
                        <Link :href="`/categorie/${ancestor.slug}`" class="hover:text-slate-200 transition">{{ ancestor.name }}</Link>
                    </template>
                    <span>/</span>
                    <span class="text-slate-200">{{ category.name }}</span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black text-white mb-2">{{ category.name }}</h1>
                <p v-if="category.description" class="text-slate-300 max-w-xl text-sm leading-relaxed">{{ category.description }}</p>
                <p class="text-slate-400 text-sm mt-2">{{ products.total }} produit{{ products.total > 1 ? 's' : '' }}</p>
            </div>
        </div>

        <div class="bg-slate-50 min-h-screen">
            <div class="container mx-auto px-4 py-8">

                <!-- Sous-catégories -->
                <div v-if="subcategories?.length" class="flex flex-wrap gap-2 mb-8">
                    <Link
                        v-for="sub in subcategories"
                        :key="sub.id"
                        :href="`/categorie/${sub.slug}`"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all"
                    >
                        <img v-if="sub.image" :src="`/storage/${sub.image}`" :alt="sub.name" class="w-5 h-5 rounded-full object-cover" />
                        {{ sub.name }}
                    </Link>
                </div>

                <!-- Barre filtre/tri -->
                <div class="mb-6">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <p class="text-sm text-slate-500">
                            <span class="font-semibold text-slate-900">{{ products.total }}</span> produit{{ products.total > 1 ? 's' : '' }}
                        </p>
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <button
                                    @click="filterPanelOpen = !filterPanelOpen"
                                    class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition"
                                >
                                    <SlidersHorizontal class="w-4 h-4" />
                                    Filtres
                                    <span v-if="activeChips.length" class="bg-primary-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                        {{ activeChips.length }}
                                    </span>
                                </button>

                                <div v-if="filterPanelOpen" class="fixed inset-0 z-20" @click="filterPanelOpen = false" />

                                <!-- Panneau filtres -->
                                <div v-if="filterPanelOpen" class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-4 z-30">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Fourchette de prix</p>
                                    <div class="flex gap-2 mb-4">
                                        <div class="flex-1">
                                            <label class="text-xs text-slate-400 block mb-1">Min</label>
                                            <input
                                                v-model="localFilters.min_price"
                                                type="number" min="0" placeholder="0"
                                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-xs text-slate-400 block mb-1">Max</label>
                                            <input
                                                v-model="localFilters.max_price"
                                                type="number" min="0" placeholder="∞"
                                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"
                                            />
                                        </div>
                                    </div>
                                    <label class="flex items-center gap-3 cursor-pointer group mb-4">
                                        <input
                                            v-model="localFilters.on_sale"
                                            type="checkbox" true-value="1" false-value=""
                                            class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500 cursor-pointer"
                                        />
                                        <span class="text-sm text-slate-700 group-hover:text-slate-900 transition select-none">En promotion</span>
                                        <span class="ml-auto text-xs text-red-600 font-bold">PROMO</span>
                                    </label>
                                    <button
                                        @click="applyFilters"
                                        class="w-full py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition"
                                    >
                                        Appliquer
                                    </button>
                                </div>
                            </div>

                            <label class="text-xs text-slate-500 shrink-0">Trier :</label>
                            <select
                                :value="localFilters.sort"
                                @change="applySort($event.target.value)"
                                class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400"
                            >
                                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Chips filtres actifs -->
                    <div v-if="activeChips.length" class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="text-xs text-slate-500 font-medium">Filtres :</span>
                        <span
                            v-for="chip in activeChips"
                            :key="chip.key"
                            class="inline-flex items-center gap-1.5 bg-slate-100 border border-slate-200 text-slate-700 text-xs font-medium px-3 py-1 rounded-full"
                        >
                            {{ chip.label }}
                            <button @click="removeFilter(chip.key)" class="text-slate-400 hover:text-slate-700 transition">
                                <X class="w-3 h-3" />
                            </button>
                        </span>
                        <button @click="clearAll" class="text-xs text-red-600 hover:text-red-700 underline underline-offset-2">
                            Tout effacer
                        </button>
                    </div>
                </div>

                <!-- Grille produits -->
                <div v-if="products.data?.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                    <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
                </div>

                <!-- État vide -->
                <div v-else class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 mb-2">
                        {{ activeChips.length ? 'Aucun produit ne correspond à ces filtres' : 'Aucun produit dans cette catégorie' }}
                    </h2>
                    <p class="text-sm text-slate-500 mb-6">
                        {{ activeChips.length ? 'Essayez d\'élargir votre fourchette de prix ou de retirer un filtre.' : 'Revenez bientôt, de nouveaux articles arrivent régulièrement.' }}
                    </p>
                    <button v-if="activeChips.length" @click="clearAll" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                        <X class="w-4 h-4" />
                        Effacer les filtres
                    </button>
                    <Link v-else href="/boutique" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                        Voir toute la boutique
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
                    <button
                        @click="goToPage(products.prev_page_url)"
                        :disabled="!products.prev_page_url"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium border rounded-lg transition"
                        :class="products.prev_page_url ? 'border-slate-300 text-slate-700 hover:bg-slate-50 bg-white' : 'border-slate-200 text-slate-300 bg-white cursor-not-allowed'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Précédent
                    </button>
                    <span class="text-sm text-slate-500 px-2">
                        Page {{ products.current_page }} / {{ products.last_page }}
                    </span>
                    <button
                        @click="goToPage(products.next_page_url)"
                        :disabled="!products.next_page_url"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium border rounded-lg transition"
                        :class="products.next_page_url ? 'border-slate-300 text-slate-700 hover:bg-slate-50 bg-white' : 'border-slate-200 text-slate-300 bg-white cursor-not-allowed'"
                    >
                        Suivant
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
