<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';

const props = defineProps({
    products:        Object,
    categories:      Array,
    filters:         Object,
    currentCategory: Object,
});

const { formatPrice } = useHelpers();

// ─── Sidebar mobile toggle ────────────────────────────────────────────────────
const sidebarOpen = ref(false);

// ─── Filtres locaux (réactifs) ────────────────────────────────────────────────
const localFilters = ref({
    category:  props.filters?.category  || '',
    min_price: props.filters?.min_price || '',
    max_price: props.filters?.max_price || '',
    on_sale:   props.filters?.on_sale   || '',
    sort:      props.filters?.sort      || 'newest',
});

const applyFilters = () => {
    const params = {};
    if (localFilters.value.category)  params.category  = localFilters.value.category;
    if (localFilters.value.min_price) params.min_price = localFilters.value.min_price;
    if (localFilters.value.max_price) params.max_price = localFilters.value.max_price;
    if (localFilters.value.on_sale)   params.on_sale   = '1';
    if (localFilters.value.sort)      params.sort      = localFilters.value.sort;
    router.get('/boutique', params, { preserveScroll: true });
    sidebarOpen.value = false;
};

const applySort = (val) => {
    localFilters.value.sort = val;
    applyFilters();
};

const removeFilter = (key) => {
    localFilters.value[key] = '';
    applyFilters();
};

const clearAll = () => {
    localFilters.value = { category: '', min_price: '', max_price: '', on_sale: '', sort: 'newest' };
    router.get('/boutique', {}, { preserveScroll: true });
};

// ─── Chips filtres actifs ─────────────────────────────────────────────────────
const activeChips = computed(() => {
    const chips = [];
    if (props.filters?.category) {
        const cat = props.categories?.find(c => c.slug === props.filters.category);
        chips.push({ key: 'category', label: cat?.name || props.filters.category });
    }
    if (props.filters?.min_price) chips.push({ key: 'min_price', label: `Min ${formatPrice(props.filters.min_price)}` });
    if (props.filters?.max_price) chips.push({ key: 'max_price', label: `Max ${formatPrice(props.filters.max_price)}` });
    if (props.filters?.on_sale)   chips.push({ key: 'on_sale', label: 'En promotion' });
    return chips;
});

// ─── Pagination numérotée ─────────────────────────────────────────────────────
const pageNumbers = computed(() => {
    const current = props.products.current_page;
    const last    = props.products.last_page;
    const pages   = [];
    const delta   = 2;
    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
});

const goToPage = (p) => {
    if (p === '...') return;
    router.get('/boutique', { ...props.filters, page: p }, { preserveScroll: true });
};

const sortOptions = [
    { value: 'newest',     label: 'Plus récents' },
    { value: 'price_asc',  label: 'Prix croissant' },
    { value: 'price_desc', label: 'Prix décroissant' },
    { value: 'popular',    label: 'Meilleures ventes' },
    { value: 'name',       label: 'Nom A-Z' },
];
</script>

<template>
    <FrontLayout :title="currentCategory ? `${currentCategory.name} — Boutique` : 'Boutique'">
        <Head>
            <title>{{ currentCategory ? `${currentCategory.name} — Boutique` : 'Boutique' }}</title>
            <meta name="description" content="Découvrez notre sélection de produits. Livraison rapide en Côte d'Ivoire." />
        </Head>

        <!-- Hero / breadcrumb band -->
        <div class="bg-slate-900 text-white py-8">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
                    <Link href="/" class="hover:text-white transition">Accueil</Link>
                    <span>/</span>
                    <Link href="/boutique" class="hover:text-white transition">Boutique</Link>
                    <template v-if="currentCategory">
                        <span>/</span>
                        <span class="text-white">{{ currentCategory.name }}</span>
                    </template>
                </nav>
                <div class="flex items-end justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">
                            {{ currentCategory ? currentCategory.name : 'Tous les produits' }}
                        </h1>
                        <p class="text-sm text-slate-400 mt-1">{{ products.total }} produit{{ products.total > 1 ? 's' : '' }}</p>
                    </div>
                    <!-- Sort — desktop header right -->
                    <div class="hidden md:flex items-center gap-2">
                        <span class="text-xs text-slate-400">Trier par</span>
                        <select
                            :value="localFilters.sort"
                            @change="applySort($event.target.value)"
                            class="text-sm bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        >
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6 max-w-7xl">

            <!-- ─── Chips filtres actifs ─────────────────────────── -->
            <div v-if="activeChips.length" class="flex flex-wrap items-center gap-2 mb-5">
                <span class="text-xs text-slate-500 font-medium">Filtres :</span>
                <span
                    v-for="chip in activeChips"
                    :key="chip.key"
                    class="inline-flex items-center gap-1.5 bg-slate-100 border border-slate-200 text-slate-700 text-xs font-medium px-3 py-1 rounded-full"
                >
                    {{ chip.label }}
                    <button @click="removeFilter(chip.key)" class="text-slate-400 hover:text-slate-700 transition ml-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
                <button @click="clearAll" class="text-xs text-red-600 hover:text-red-700 underline underline-offset-2">Tout effacer</button>
            </div>

            <div class="flex gap-6">

                <!-- ─── Sidebar filtres ─────────────────────────── -->
                <!-- Mobile overlay -->
                <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-40 md:hidden" @click="sidebarOpen = false"></div>

                <aside
                    class="w-64 shrink-0 md:block"
                    :class="sidebarOpen ? 'fixed left-0 top-0 h-full z-50 bg-white overflow-y-auto p-5 shadow-xl block' : 'hidden'"
                >
                    <!-- Mobile close -->
                    <div class="flex items-center justify-between mb-6 md:hidden">
                        <p class="font-semibold text-slate-900">Filtres</p>
                        <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Catégories -->
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Catégories</p>
                        <ul class="space-y-1">
                            <li>
                                <button
                                    @click="localFilters.category = ''; applyFilters()"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm transition"
                                    :class="!localFilters.category ? 'bg-slate-900 text-white font-medium' : 'text-slate-700 hover:bg-slate-50'"
                                >
                                    Tous les produits
                                </button>
                            </li>
                            <li v-for="cat in categories" :key="cat.id">
                                <button
                                    @click="localFilters.category = cat.slug; applyFilters()"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm transition"
                                    :class="localFilters.category === cat.slug ? 'bg-slate-900 text-white font-medium' : 'text-slate-700 hover:bg-slate-50'"
                                >
                                    {{ cat.name }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Fourchette de prix -->
                    <div class="mb-6 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Fourchette de prix</p>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="text-xs text-slate-400 block mb-1">Min</label>
                                <input
                                    v-model="localFilters.min_price"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                />
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-slate-400 block mb-1">Max</label>
                                <input
                                    v-model="localFilters.max_price"
                                    type="number"
                                    min="0"
                                    placeholder="∞"
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Promotions -->
                    <div class="mb-6 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Offres spéciales</p>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input
                                v-model="localFilters.on_sale"
                                type="checkbox"
                                true-value="1"
                                false-value=""
                                class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500 cursor-pointer"
                            />
                            <span class="text-sm text-slate-700 group-hover:text-slate-900 transition select-none">En promotion</span>
                            <span class="ml-auto text-xs text-red-600 font-bold">PROMO</span>
                        </label>
                    </div>

                    <!-- Sort mobile -->
                    <div class="mb-6 border-t border-slate-100 pt-5 md:hidden">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Trier par</p>
                        <select
                            v-model="localFilters.sort"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400"
                        >
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>

                    <!-- Bouton appliquer -->
                    <button
                        @click="applyFilters"
                        class="w-full py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition"
                    >
                        Appliquer les filtres
                    </button>
                </aside>

                <!-- ─── Contenu principal ────────────────────────── -->
                <div class="flex-1 min-w-0">

                    <!-- Mobile toolbar -->
                    <div class="flex items-center gap-3 mb-4 md:hidden">
                        <button
                            @click="sidebarOpen = true"
                            class="flex items-center gap-2 border border-slate-200 rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 110 2H4a1 1 0 01-1-1zm0 6a1 1 0 011-1h10a1 1 0 110 2H4a1 1 0 01-1-1zm0 6a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z"/></svg>
                            Filtres
                            <span v-if="activeChips.length" class="bg-slate-900 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">{{ activeChips.length }}</span>
                        </button>
                        <select
                            :value="localFilters.sort"
                            @change="applySort($event.target.value)"
                            class="flex-1 text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none"
                        >
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>

                    <!-- Grille produits -->
                    <div v-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                    </div>

                    <!-- Empty state -->
                    <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Aucun produit trouvé</h3>
                        <p class="text-sm text-slate-500 mb-5">Essayez de modifier vos filtres ou effacez-les tous.</p>
                        <button @click="clearAll" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                            Voir tous les produits
                        </button>
                    </div>

                    <!-- ─── Pagination numérotée ──────────────────── -->
                    <div v-if="products.last_page > 1" class="mt-10 flex items-center justify-center gap-1.5">
                        <!-- Précédent -->
                        <button
                            :disabled="products.current_page === 1"
                            @click="goToPage(products.current_page - 1)"
                            class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <!-- Numéros -->
                        <template v-for="(p, i) in pageNumbers" :key="i">
                            <span v-if="p === '...'" class="w-9 h-9 flex items-center justify-center text-slate-400 text-sm">…</span>
                            <button
                                v-else
                                @click="goToPage(p)"
                                class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-medium transition"
                                :class="p === products.current_page
                                    ? 'bg-slate-900 text-white'
                                    : 'border border-slate-200 text-slate-700 hover:bg-slate-50'"
                            >
                                {{ p }}
                            </button>
                        </template>

                        <!-- Suivant -->
                        <button
                            :disabled="products.current_page === products.last_page"
                            @click="goToPage(products.current_page + 1)"
                            class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
