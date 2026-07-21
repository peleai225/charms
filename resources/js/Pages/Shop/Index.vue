<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
    currentCategory: Object,
});

const { formatPrice } = useHelpers();

const viewMode = ref('grid');

const sortOptions = [
    { value: 'latest', label: 'Plus récents' },
    { value: 'price_asc', label: 'Prix croissant' },
    { value: 'price_desc', label: 'Prix décroissant' },
    { value: 'popular', label: 'Meilleures ventes' },
];

const currentSort = computed(() => props.filters?.sort || 'latest');

const applyFilter = (key, value) => {
    router.get('/boutique', {
        ...props.filters,
        [key]: value,
        page: 1, // Reset page
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const removeFilter = (key) => {
    const filters = { ...props.filters };
    delete filters[key];
    router.get('/boutique', filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const activeFiltersCount = computed(() => {
    return Object.keys(props.filters || {}).filter(key => key !== 'sort' && key !== 'page').length;
});
</script>

<template>
    <FrontLayout :title="currentCategory ? `${currentCategory.name} — Boutique` : 'Boutique'">
        <Head>
            <title>{{ currentCategory ? `${currentCategory.name} — Boutique` : 'Boutique' }}</title>
            <meta name="description" content="Découvrez notre sélection de produits. Livraison rapide en Côte d'Ivoire." />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-white border-b border-slate-100 py-6">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-2 text-sm mb-3 text-slate-400">
                    <Link href="/" class="hover:text-slate-700 transition-colors">Accueil</Link>
                    <span class="text-slate-300">/</span>
                    <Link href="/boutique" class="hover:text-slate-700 transition-colors">Boutique</Link>
                    <template v-if="currentCategory">
                        <span class="text-slate-300">/</span>
                        <span class="text-slate-700">{{ currentCategory.name }}</span>
                    </template>
                </nav>
                <div class="flex items-baseline gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">
                        {{ currentCategory ? currentCategory.name : 'Boutique' }}
                    </h1>
                    <span class="text-sm text-slate-500">
                        {{ products.total }} produit{{ products.total > 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div v-if="categories?.length" class="bg-white border-b border-slate-100 sticky top-0 z-30">
            <div class="container mx-auto px-4 py-2.5 flex items-center gap-2 overflow-x-auto scrollbar-hide">
                <Link
                    href="/boutique"
                    class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors"
                    :class="!filters?.category ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                >
                    Tout voir
                </Link>
                <Link
                    v-for="cat in categories"
                    :key="cat.id"
                    :href="`/boutique?category=${cat.slug}`"
                    class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors"
                    :class="filters?.category === cat.slug ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                >
                    {{ cat.name }}
                </Link>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6 lg:py-10">
            <!-- Toolbar -->
            <div class="flex items-center gap-3 mb-5">
                <p class="text-sm text-slate-500">
                    <span class="font-semibold text-slate-900">{{ products.total }}</span> résultat{{ products.total > 1 ? 's' : '' }}
                </p>

                <!-- Active filters -->
                <div v-if="activeFiltersCount > 0" class="flex items-center gap-2">
                    <span class="w-5 h-5 bg-primary-600 text-white text-xs font-bold rounded-full inline-flex items-center justify-center">
                        {{ activeFiltersCount }}
                    </span>
                </div>

                <!-- Sort -->
                <div class="ml-auto flex items-center gap-2">
                    <div class="hidden sm:flex border border-slate-200 rounded-lg overflow-hidden">
                        <button
                            @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-slate-900 text-white' : 'bg-white text-slate-400 hover:bg-slate-50'"
                            class="p-2 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-slate-900 text-white' : 'bg-white text-slate-400 hover:bg-slate-50'"
                            class="p-2 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>

                    <select
                        :value="currentSort"
                        @change="applyFilter('sort', $event.target.value)"
                        class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div
                class="grid gap-6"
                :class="{
                    'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4': viewMode === 'grid',
                    'grid-cols-1': viewMode === 'list',
                }"
            >
                <Link
                    v-for="product in products.data"
                    :key="product.id"
                    :href="`/produit/${product.slug}`"
                    class="group bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-lg transition"
                >
                    <div class="aspect-square bg-slate-100 overflow-hidden relative">
                        <img
                            v-if="product.primary_image"
                            :src="`/storage/${product.primary_image}`"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        />
                        <div v-if="product.compare_price" class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                            -{{ Math.round((1 - product.price / product.compare_price) * 100) }}%
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-slate-900 mb-2 line-clamp-2">{{ product.name }}</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <span v-if="product.compare_price" class="text-xs text-slate-400 line-through mr-2">
                                    {{ formatPrice(product.compare_price) }}
                                </span>
                                <span class="text-lg font-bold text-primary-600">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                            <span
                                v-if="product.stock > 0"
                                class="text-xs text-success-600 font-medium"
                            >
                                En stock
                            </span>
                            <span v-else class="text-xs text-red-600 font-medium">
                                Rupture
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div v-if="products.data.length === 0" class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Aucun produit trouvé</h3>
                <p class="text-sm text-slate-500 mb-4">Essayez de modifier vos filtres</p>
                <Link href="/boutique" class="text-primary-600 hover:text-primary-700 font-medium">
                    Voir tous les produits
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                <Link
                    v-if="products.prev_page_url"
                    :href="products.prev_page_url"
                    class="px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 transition"
                >
                    Précédent
                </Link>
                <span class="text-sm text-slate-500">
                    Page {{ products.current_page }} sur {{ products.last_page }}
                </span>
                <Link
                    v-if="products.next_page_url"
                    :href="products.next_page_url"
                    class="px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 transition"
                >
                    Suivant
                </Link>
            </div>
        </div>
    </FrontLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
