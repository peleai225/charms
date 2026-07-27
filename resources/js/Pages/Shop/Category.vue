<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';

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

const activeSort = ref(props.filters?.sort || 'newest');

const applySort = (val) => {
    activeSort.value = val;
    router.get(`/categorie/${props.category.slug}`, { sort: val }, { preserveState: true, preserveScroll: true });
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
                <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
                    <Link href="/" class="hover:text-slate-200 transition">Accueil</Link>
                    <span>/</span>
                    <Link href="/boutique" class="hover:text-slate-200 transition">Boutique</Link>
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
                <div class="flex items-center justify-between mb-6 gap-4">
                    <p class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-900">{{ products.total }}</span> produit{{ products.total > 1 ? 's' : '' }}
                    </p>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-slate-500 shrink-0">Trier :</label>
                        <select
                            :value="activeSort"
                            @change="applySort($event.target.value)"
                            class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400"
                        >
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
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
                    <h2 class="text-lg font-bold text-slate-900 mb-2">Aucun produit dans cette catégorie</h2>
                    <p class="text-sm text-slate-500 mb-6">Revenez bientôt, de nouveaux articles arrivent régulièrement.</p>
                    <Link href="/boutique" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
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
