<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { X, ChevronLeft, ChevronRight, ChevronDown, SlidersHorizontal, Inbox } from 'lucide-vue-next';

const props = defineProps({
    products:        Object,
    categories:      Array,
    filters:         Object,
    currentCategory: Object,
});

const { formatPrice } = useHelpers();

// ─── Mobile filters sidebar toggle ───────────────────────────────────────────
const sidebarOpen = ref(false);

// ─── Category tree expanded state (object for Vue reactivity) ────────────────
const expandedCategories = ref({});

const isCategoryExpanded = (cat) => {
    return expandedCategories.value[cat.id] ||
        cat.slug === props.filters?.category ||
        cat.children?.some(c => c.slug === props.filters?.category);
};

const toggleCategory = (catId) => {
    expandedCategories.value[catId] = !expandedCategories.value[catId];
};

// ─── Filtres locaux ───────────────────────────────────────────────────────────
const localFilters = ref({
    category:  props.filters?.category  || '',
    min_price: props.filters?.min_price || '',
    max_price: props.filters?.max_price || '',
    on_sale:   props.filters?.on_sale   || '',
    sort:      props.filters?.sort      || 'newest',
});

const navigateToCategory = (slug) => {
    const params = {};
    if (slug)                         params.category  = slug;
    if (localFilters.value.min_price) params.min_price = localFilters.value.min_price;
    if (localFilters.value.max_price) params.max_price = localFilters.value.max_price;
    if (localFilters.value.on_sale)   params.on_sale   = '1';
    if (localFilters.value.sort)      params.sort      = localFilters.value.sort;
    localFilters.value.category = slug;
    router.get('/boutique', params, { preserveScroll: true });
};

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

// ─── Parent category (for breadcrumb) ────────────────────────────────────────
const currentParentCategory = computed(() => {
    if (!props.currentCategory) return null;
    for (const cat of props.categories || []) {
        if (cat.children?.some(c => c.slug === props.currentCategory.slug)) return cat;
    }
    return null;
});

// ─── Chips filtres actifs ─────────────────────────────────────────────────────
const activeChips = computed(() => {
    const chips = [];
    if (props.filters?.category) {
        let catName = props.filters.category;
        for (const cat of props.categories || []) {
            if (cat.slug === props.filters.category) { catName = cat.name; break; }
            const child = cat.children?.find(c => c.slug === props.filters.category);
            if (child) { catName = child.name; break; }
        }
        chips.push({ key: 'category', label: catName });
    }
    if (props.filters?.min_price) chips.push({ key: 'min_price', label: `Min ${formatPrice(props.filters.min_price)}` });
    if (props.filters?.max_price) chips.push({ key: 'max_price', label: `Max ${formatPrice(props.filters.max_price)}` });
    if (props.filters?.on_sale)   chips.push({ key: 'on_sale',   label: 'En promotion' });
    return chips;
});

const pricePromoChips = computed(() => activeChips.value.filter(c => c.key !== 'category'));

// ─── Pagination ───────────────────────────────────────────────────────────────
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

// ─── Loading ──────────────────────────────────────────────────────────────────
const loading = ref(false);
let unsubStart, unsubFinish;
onMounted(() => {
    unsubStart  = router.on('start',  () => { loading.value = true; });
    unsubFinish = router.on('finish', () => { loading.value = false; });
});
onUnmounted(() => { unsubStart?.(); unsubFinish?.(); });
</script>

<template>
    <FrontLayout :title="currentCategory ? `${currentCategory.name} — Boutique` : 'Boutique'">
        <Head>
            <title>{{ currentCategory ? `${currentCategory.name} — Boutique` : 'Boutique' }}</title>
            <meta name="description" content="Découvrez notre sélection de produits. Livraison rapide en Côte d'Ivoire." />
        </Head>

        <!-- ─── Breadcrumb bar ─────────────────────────────────────────────── -->
        <div class="bg-white border-b border-slate-200">
            <div class="container mx-auto px-4 py-3 max-w-7xl">
                <nav class="flex items-center gap-1 text-sm text-slate-500 flex-wrap">
                    <Link href="/" class="hover:text-slate-700 transition">Accueil</Link>
                    <ChevronRight class="w-3.5 h-3.5 flex-shrink-0 text-slate-300" />
                    <Link href="/boutique" class="hover:text-slate-700 transition">Boutique</Link>
                    <template v-if="currentParentCategory">
                        <ChevronRight class="w-3.5 h-3.5 flex-shrink-0 text-slate-300" />
                        <button @click="navigateToCategory(currentParentCategory.slug)" class="hover:text-slate-700 transition">
                            {{ currentParentCategory.name }}
                        </button>
                    </template>
                    <template v-if="currentCategory">
                        <ChevronRight class="w-3.5 h-3.5 flex-shrink-0 text-slate-300" />
                        <span class="text-slate-900 font-medium">{{ currentCategory.name }}</span>
                    </template>
                </nav>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6 max-w-7xl">
            <div class="grid lg:grid-cols-[220px_1fr] gap-6">

                <!-- ─── LEFT SIDEBAR: category tree (desktop only) ─────────── -->
                <aside class="hidden lg:block">
                    <div class="sticky top-20 max-h-[calc(100vh-5rem)] overflow-y-auto pr-2">

                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Catégories</p>

                        <!-- Toutes les catégories -->
                        <button
                            @click="navigateToCategory('')"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition mb-0.5"
                            :class="!filters?.category ? 'text-primary-600 font-semibold bg-primary-50' : 'text-slate-700 hover:bg-slate-50'"
                        >
                            Toutes les catégories
                        </button>

                        <!-- Root categories -->
                        <div v-for="cat in categories" :key="cat.id" class="mb-0.5">
                            <div class="flex items-center gap-0.5">
                                <button
                                    @click="navigateToCategory(cat.slug)"
                                    class="flex-1 text-left px-3 py-2 rounded-lg text-sm transition"
                                    :class="filters?.category === cat.slug ? 'text-primary-600 font-semibold bg-primary-50' : 'text-slate-700 hover:bg-slate-50'"
                                >
                                    {{ cat.name }}
                                </button>
                                <button
                                    v-if="cat.children?.length"
                                    @click="toggleCategory(cat.id)"
                                    class="p-1.5 text-slate-400 hover:text-slate-600 transition flex-shrink-0"
                                    :aria-label="isCategoryExpanded(cat) ? 'Réduire' : 'Développer'"
                                >
                                    <ChevronDown v-if="isCategoryExpanded(cat)" class="w-3.5 h-3.5" />
                                    <ChevronRight v-else class="w-3.5 h-3.5" />
                                </button>
                            </div>

                            <!-- Sub-categories -->
                            <div v-if="cat.children?.length && isCategoryExpanded(cat)"
                                class="ml-3 pl-2 border-l border-slate-100 mt-0.5 mb-1 space-y-0.5">
                                <button
                                    v-for="child in cat.children"
                                    :key="child.id"
                                    @click="navigateToCategory(child.slug)"
                                    class="w-full text-left px-3 py-1.5 rounded-lg text-sm transition"
                                    :class="filters?.category === child.slug
                                        ? 'text-primary-600 font-semibold bg-primary-50'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'"
                                >
                                    {{ child.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Price filter -->
                        <div class="border-t border-slate-100 pt-4 mt-5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Fourchette de prix</p>
                            <div class="flex gap-2">
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
                        </div>

                        <!-- Promo filter -->
                        <div class="border-t border-slate-100 pt-4 mt-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Offres spéciales</p>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input
                                    v-model="localFilters.on_sale"
                                    type="checkbox" true-value="1" false-value=""
                                    class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500 cursor-pointer"
                                />
                                <span class="text-sm text-slate-700 group-hover:text-slate-900 transition select-none">En promotion</span>
                                <span class="ml-auto text-xs text-red-600 font-bold">PROMO</span>
                            </label>
                        </div>

                        <!-- Apply button -->
                        <button
                            @click="applyFilters"
                            class="w-full mt-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition"
                        >
                            Appliquer
                        </button>
                    </div>
                </aside>

                <!-- ─── RIGHT: Products area ────────────────────────────────── -->
                <div class="min-w-0">

                    <!-- Mobile: category chips strip -->
                    <div class="lg:hidden -mx-4 px-4 overflow-x-auto pb-3 mb-3">
                        <div class="flex gap-2 w-max">
                            <button
                                @click="navigateToCategory('')"
                                class="h-9 px-4 rounded-full text-sm font-medium whitespace-nowrap transition"
                                :class="!filters?.category ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'"
                            >
                                Tout
                            </button>
                            <button
                                v-for="cat in categories" :key="cat.id"
                                @click="navigateToCategory(cat.slug)"
                                class="h-9 px-4 rounded-full text-sm font-medium whitespace-nowrap transition"
                                :class="(filters?.category === cat.slug || currentParentCategory?.id === cat.id)
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'"
                            >
                                {{ cat.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Mobile toolbar: filters button + sort -->
                    <div class="flex items-center gap-3 mb-4 lg:hidden">
                        <button
                            @click="sidebarOpen = true"
                            class="flex items-center gap-2 border border-slate-200 rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                        >
                            <SlidersHorizontal class="w-4 h-4" />
                            Filtres
                            <span v-if="pricePromoChips.length"
                                class="bg-primary-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                {{ pricePromoChips.length }}
                            </span>
                        </button>
                        <select
                            :value="localFilters.sort"
                            @change="applySort($event.target.value)"
                            class="flex-1 text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none"
                        >
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>

                    <!-- Category header (when a category is active) -->
                    <div v-if="currentCategory" class="mb-5">
                        <h1 class="text-3xl font-bold text-slate-900 leading-tight">{{ currentCategory.name }}</h1>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ products.total }} produit{{ products.total !== 1 ? 's' : '' }}
                        </p>
                    </div>

                    <!-- Desktop toolbar: result count / active chips + sort -->
                    <div class="hidden lg:flex items-center justify-between gap-4 mb-5">
                        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                            <template v-if="activeChips.length">
                                <span class="text-xs text-slate-500 font-medium shrink-0">Filtres :</span>
                                <span
                                    v-for="chip in activeChips"
                                    :key="chip.key"
                                    class="inline-flex items-center gap-1.5 bg-slate-100 border border-slate-200 text-slate-700 text-xs font-medium px-3 py-1 rounded-full"
                                >
                                    {{ chip.label }}
                                    <button @click="removeFilter(chip.key)" class="text-slate-400 hover:text-slate-700 transition ml-0.5">
                                        <X class="w-3 h-3" />
                                    </button>
                                </span>
                                <button @click="clearAll" class="text-xs text-red-600 hover:text-red-700 underline underline-offset-2">
                                    Tout effacer
                                </button>
                            </template>
                            <span v-else-if="!currentCategory" class="text-sm text-slate-500">
                                {{ products.total }} résultat{{ products.total !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-slate-500">Trier par</span>
                            <select
                                :value="localFilters.sort"
                                @change="applySort($event.target.value)"
                                class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-600"
                            >
                                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mobile active chips (price/promo only) -->
                    <div v-if="pricePromoChips.length" class="flex flex-wrap items-center gap-2 mb-4 lg:hidden">
                        <span class="text-xs text-slate-500 font-medium">Filtres :</span>
                        <span
                            v-for="chip in pricePromoChips"
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

                    <!-- Skeleton loading -->
                    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="i in 9" :key="i" class="bg-white rounded-2xl border border-slate-100 overflow-hidden animate-pulse">
                            <div class="aspect-square bg-slate-100" />
                            <div class="p-3 space-y-2">
                                <div class="h-2.5 bg-slate-100 rounded w-3/4" />
                                <div class="h-4 bg-slate-100 rounded w-1/2" />
                            </div>
                        </div>
                    </div>

                    <!-- Products grid -->
                    <div v-else-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                    </div>

                    <!-- Empty state -->
                    <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                            <Inbox class="w-8 h-8 text-slate-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Aucun produit trouvé</h3>
                        <p class="text-sm text-slate-500 mb-5 max-w-xs">
                            <template v-if="activeChips.length">Aucun résultat pour ces filtres. Essayez de les modifier.</template>
                            <template v-else>Notre catalogue est vide pour le moment. Revenez bientôt !</template>
                        </p>
                        <button v-if="activeChips.length" @click="clearAll"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition">
                            <X class="w-4 h-4" />
                            Effacer les filtres
                        </button>
                        <Link v-else href="/"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition">
                            Retour à l'accueil
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.last_page > 1" class="mt-10 flex items-center justify-center gap-1.5">
                        <button
                            :disabled="products.current_page === 1"
                            @click="goToPage(products.current_page - 1)"
                            class="w-11 h-11 flex items-center justify-center border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <template v-for="(p, i) in pageNumbers" :key="i">
                            <span v-if="p === '...'" class="w-11 h-11 flex items-center justify-center text-slate-400 text-sm">…</span>
                            <button
                                v-else
                                @click="goToPage(p)"
                                class="w-11 h-11 flex items-center justify-center rounded-lg text-sm font-medium transition"
                                :class="p === products.current_page ? 'bg-primary-600 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'"
                            >
                                {{ p }}
                            </button>
                        </template>
                        <button
                            :disabled="products.current_page === products.last_page"
                            @click="goToPage(products.current_page + 1)"
                            class="w-11 h-11 flex items-center justify-center border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Mobile filters overlay ─────────────────────────────────────── -->
        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-40 lg:hidden" @click="sidebarOpen = false" />
        <div
            v-if="sidebarOpen"
            class="fixed left-0 top-0 h-full w-72 z-50 bg-white overflow-y-auto p-5 shadow-xl lg:hidden"
        >
            <div class="flex items-center justify-between mb-6">
                <p class="font-semibold text-slate-900">Filtres</p>
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-700 transition">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <div class="mb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Fourchette de prix</p>
                <div class="flex gap-2">
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
            </div>

            <div class="mb-6 border-t border-slate-100 pt-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Offres spéciales</p>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                        v-model="localFilters.on_sale"
                        type="checkbox" true-value="1" false-value=""
                        class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500 cursor-pointer"
                    />
                    <span class="text-sm text-slate-700 group-hover:text-slate-900 transition select-none">En promotion</span>
                    <span class="ml-auto text-xs text-red-600 font-bold">PROMO</span>
                </label>
            </div>

            <div class="mb-6 border-t border-slate-100 pt-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Trier par</p>
                <select
                    v-model="localFilters.sort"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-300"
                >
                    <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
            </div>

            <button
                @click="applyFilters"
                class="w-full py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition"
            >
                Appliquer les filtres
            </button>
        </div>
    </FrontLayout>
</template>
