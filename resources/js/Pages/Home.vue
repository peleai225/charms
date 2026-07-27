<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';

const props = defineProps({
    featured_categories: Array,
    featured_products:   Array,
    new_products:        Array,
    sale_products:       Array,
    reviews:             Array,
    review_stats:        Object,
    whatsapp_number:     String,
    banners:             Object,
});

const page = usePage();
const { formatPrice } = useHelpers();

const siteName = computed(() => page.props.settings?.site_name || 'Chamse');

const trustItems = [
    {
        icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
        title: 'Livraison rapide',
        desc: "Partout en Côte d'Ivoire",
    },
    {
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        title: 'Paiement sécurisé',
        desc: 'Transactions cryptées SSL',
    },
    {
        icon: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
        title: 'Support 7j/7',
        desc: 'Via WhatsApp, réponse rapide',
    },
    {
        icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        title: 'Retours faciles',
        desc: 'Sous 7 jours, sans frais',
    },
];

const starsFull = (n) => Array.from({ length: 5 }, (_, i) => i < Math.round(n));

// Tab sections pour produits
const productTabs = [
    { key: 'featured', label: 'Sélection' },
    { key: 'new',      label: 'Nouveautés' },
    { key: 'sale',     label: 'Promotions' },
];
const activeProductTab = ref('featured');

const currentProducts = computed(() => {
    if (activeProductTab.value === 'featured') return props.featured_products || [];
    if (activeProductTab.value === 'new')      return props.new_products      || [];
    if (activeProductTab.value === 'sale')     return props.sale_products     || [];
    return [];
});
</script>

<template>
    <FrontLayout title="Accueil">
        <Head>
            <title>{{ siteName }} — Boutique en ligne</title>
            <meta name="description" content="Découvrez notre sélection de produits. Livraison rapide en Côte d'Ivoire." />
        </Head>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- HERO — Split layout avec produits vedettes                        -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section class="bg-slate-900 text-white overflow-hidden relative min-h-[580px] flex items-center">
            <!-- Motif géométrique fond -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
                <div class="absolute -top-40 -right-40 w-[500px] h-[500px] rounded-full bg-white/[0.025]"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-white/[0.02]"></div>
                <div class="absolute top-0 right-0 w-1/2 h-full bg-white/[0.015]" style="clip-path:polygon(30% 0,100% 0,100% 100%,0% 100%)"></div>
            </div>

            <div class="relative w-full container mx-auto px-4 py-16 md:py-24">
                <div class="grid lg:grid-cols-2 gap-12 items-center">

                    <!-- Colonne texte -->
                    <div>
                        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            Nouvelle collection disponible
                        </div>

                        <h1 class="text-4xl md:text-5xl lg:text-[3.2rem] font-black leading-[1.08] tracking-tight mb-5">
                            Découvrez nos<br>
                            <span class="text-slate-400">produits</span> vedettes
                        </h1>

                        <p class="text-slate-400 text-base leading-relaxed mb-8 max-w-md">
                            Une sélection soignée, livrée rapidement partout en Côte d'Ivoire. Qualité garantie.
                        </p>

                        <div class="flex flex-wrap gap-3 mb-10">
                            <Link
                                href="/boutique"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 text-sm font-bold rounded-xl hover:bg-slate-100 transition"
                            >
                                Voir la boutique
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </Link>
                            <a
                                v-if="whatsapp_number"
                                :href="`https://wa.me/${whatsapp_number}?text=${encodeURIComponent('Bonjour, je souhaite en savoir plus sur vos produits.')}`"
                                target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                        </div>

                        <!-- Stats -->
                        <div v-if="review_stats" class="flex items-center gap-5 pt-6 border-t border-white/10">
                            <div>
                                <p class="text-xl font-black text-white tabular-nums">{{ review_stats.count }}+</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">clients satisfaits</p>
                            </div>
                            <div class="w-px h-7 bg-white/10"></div>
                            <div>
                                <div class="flex items-center gap-1 mb-0.5">
                                    <div class="flex gap-0.5">
                                        <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.round(review_stats.avg) ? 'text-amber-400' : 'text-slate-700'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </div>
                                    <span class="text-white font-bold text-xs">{{ review_stats.avg }}/5</span>
                                </div>
                                <p class="text-[11px] text-slate-500">note moyenne</p>
                            </div>
                            <div class="w-px h-7 bg-white/10"></div>
                            <div>
                                <p class="text-xl font-black text-white">7j/7</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">support</p>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne produits vedettes -->
                    <div v-if="featured_products?.length" class="hidden lg:block">
                        <!-- Produit principal (le premier) -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Grand card — produit 1 -->
                            <Link
                                :href="`/produit/${featured_products[0].slug}`"
                                class="group col-span-2 relative bg-slate-800 rounded-2xl overflow-hidden border border-white/10 hover:border-white/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl"
                                style="min-height: 200px"
                            >
                                <img
                                    v-if="featured_products[0].primary_image"
                                    :src="`/storage/${featured_products[0].primary_image}`"
                                    :alt="featured_products[0].name"
                                    class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-60 group-hover:scale-105 transition-all duration-500"
                                />
                                <div class="relative p-5 flex flex-col justify-between h-full" style="min-height:200px">
                                    <div class="flex items-start justify-between">
                                        <span class="inline-flex items-center gap-1 bg-amber-400 text-slate-900 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            Coup de cœur
                                        </span>
                                        <span v-if="featured_products[0].compare_price" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                            -{{ Math.round((1 - featured_products[0].price / featured_products[0].compare_price) * 100) }}%
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 mb-1">{{ featured_products[0].category_name }}</p>
                                        <h3 class="text-white font-bold text-base leading-snug mb-2 line-clamp-2">{{ featured_products[0].name }}</h3>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-white font-black text-lg">{{ formatPrice(featured_products[0].price) }}</span>
                                                <span v-if="featured_products[0].compare_price" class="text-slate-500 text-xs line-through">{{ formatPrice(featured_products[0].compare_price) }}</span>
                                            </div>
                                            <span class="text-xs text-slate-300 group-hover:text-white transition flex items-center gap-1">
                                                Voir
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>

                            <!-- Petits cards — produits 2 & 3 -->
                            <Link
                                v-for="p in featured_products.slice(1, 3)"
                                :key="p.id"
                                :href="`/produit/${p.slug}`"
                                class="group relative bg-slate-800 rounded-xl overflow-hidden border border-white/10 hover:border-white/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl"
                                style="min-height: 150px"
                            >
                                <img
                                    v-if="p.primary_image"
                                    :src="`/storage/${p.primary_image}`"
                                    :alt="p.name"
                                    class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 group-hover:scale-105 transition-all duration-500"
                                />
                                <div class="relative p-4 flex flex-col justify-end h-full" style="min-height:150px">
                                    <p class="text-[10px] text-slate-400 truncate mb-0.5">{{ p.category_name }}</p>
                                    <h3 class="text-white font-bold text-sm leading-tight line-clamp-2 mb-2">{{ p.name }}</h3>
                                    <span class="text-white font-black text-sm">{{ formatPrice(p.price) }}</span>
                                </div>
                            </Link>
                        </div>

                        <!-- Lien voir tout -->
                        <div class="mt-3 text-center">
                            <Link href="/boutique" class="text-xs text-slate-500 hover:text-slate-300 transition">
                                Voir tous les produits vedettes →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- BANNIÈRES HOME HERO                                              -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="banners?.home_hero?.length" class="w-full">
            <a v-for="b in banners.home_hero" :key="b.id"
               :href="b.link || '#'"
               class="block relative overflow-hidden"
               :style="b.background_color ? { backgroundColor: b.background_color } : {}">
                <img v-if="b.image" :src="b.image" :alt="b.title || ''" class="w-full max-h-80 object-cover" />
                <div v-if="b.title" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6"
                     :style="{ color: b.text_color || '#fff' }">
                    <h2 class="text-2xl md:text-3xl font-bold drop-shadow">{{ b.title }}</h2>
                    <p v-if="b.subtitle" class="mt-2 text-lg drop-shadow">{{ b.subtitle }}</p>
                    <span v-if="b.button_text" class="mt-4 inline-block px-6 py-2 bg-white/20 hover:bg-white/30 rounded-full text-sm font-semibold backdrop-blur transition">
                        {{ b.button_text }}
                    </span>
                </div>
            </a>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- BANDE DE CONFIANCE                                               -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section class="bg-white border-b border-slate-100">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-slate-100">
                    <div
                        v-for="item in trustItems"
                        :key="item.title"
                        class="flex items-center gap-3 px-4 py-4 md:py-5"
                    >
                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">{{ item.title }}</p>
                            <p class="text-xs text-slate-500 hidden sm:block mt-0.5">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- CATÉGORIES VEDETTES                                              -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="featured_categories?.length" class="py-14 md:py-16 bg-slate-50">
            <div class="container mx-auto px-4">
                <!-- Titre section -->
                <div class="flex items-end justify-between mb-7">
                    <div>
                        <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1.5">Collections</p>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900">Nos catégories</h2>
                    </div>
                    <Link href="/boutique" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition hidden sm:block">
                        Tout voir →
                    </Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <Link
                        v-for="cat in featured_categories"
                        :key="cat.id"
                        :href="`/categorie/${cat.slug}`"
                        class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg hover:border-slate-300 transition-all duration-200"
                    >
                        <!-- Image -->
                        <div class="aspect-[4/3] bg-slate-100 overflow-hidden">
                            <img
                                v-if="cat.image"
                                :src="`/storage/${cat.image}`"
                                :alt="cat.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-9 h-9 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <!-- Infos -->
                        <div class="px-3 py-3">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ cat.name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <span v-if="cat.min_product_price">Dès {{ formatPrice(cat.min_product_price) }}</span>
                                <span v-else-if="cat.products_count">{{ cat.products_count }} produits</span>
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="mt-5 flex justify-center sm:hidden">
                    <Link href="/boutique" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition underline underline-offset-4">
                        Voir toutes les catégories
                    </Link>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- BANNIÈRES HOME MIDDLE                                            -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="banners?.home_middle?.length" class="py-8 px-4">
            <div class="container mx-auto max-w-7xl">
                <div class="grid gap-4" :class="banners.home_middle.length > 1 ? 'md:grid-cols-2' : 'grid-cols-1'">
                    <a v-for="b in banners.home_middle" :key="b.id"
                       :href="b.link || '#'"
                       class="relative rounded-2xl overflow-hidden"
                       :style="{ backgroundColor: b.background_color || '#f1f5f9' }">
                        <img v-if="b.image" :src="b.image" :alt="b.title || ''" class="w-full h-48 object-cover" />
                        <div v-if="b.title" class="absolute inset-0 flex flex-col justify-end p-5"
                             :style="{ color: b.text_color || '#fff' }">
                            <h3 class="text-xl font-bold drop-shadow">{{ b.title }}</h3>
                            <p v-if="b.subtitle" class="text-sm drop-shadow">{{ b.subtitle }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- PRODUITS — ONGLETS (Sélection / Nouveautés / Promotions)        -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section class="py-14 md:py-16 bg-white">
            <div class="container mx-auto px-4">

                <!-- Titre + tabs -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
                    <div>
                        <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1.5">Catalogue</p>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900">Nos produits</h2>
                    </div>
                    <!-- Onglets -->
                    <div class="flex bg-slate-100 rounded-xl p-1 self-start sm:self-auto">
                        <button
                            v-for="tab in productTabs"
                            :key="tab.key"
                            @click="activeProductTab = tab.key"
                            class="px-4 py-2 text-xs font-semibold rounded-lg transition-all"
                            :class="activeProductTab === tab.key
                                ? 'bg-white text-slate-900 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- Grille produits -->
                <div v-if="currentProducts.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <ProductCard
                        v-for="p in currentProducts.slice(0, 8)"
                        :key="p.id"
                        :product="p"
                    />
                </div>

                <!-- Empty tab state -->
                <div v-else class="py-12 text-center">
                    <p class="text-sm text-slate-400">Aucun produit dans cette catégorie pour l'instant.</p>
                </div>

                <!-- CTA -->
                <div class="mt-8 text-center">
                    <Link
                        href="/boutique"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition"
                    >
                        Voir tous les produits
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- BANNIÈRE PROMO (si sale_products existent)                       -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="sale_products?.length" class="bg-slate-900 py-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Offres limitées
                        </span>
                        <h2 class="text-2xl md:text-3xl font-black text-white">Profitez de nos promotions</h2>
                        <p class="text-slate-400 text-sm mt-2">Des réductions exclusives sur une sélection de produits</p>
                    </div>
                    <Link
                        href="/boutique?on_sale=1"
                        class="shrink-0 inline-flex items-center gap-2 px-6 py-3 bg-red-500 text-white text-sm font-bold rounded-xl hover:bg-red-600 transition"
                    >
                        Voir les promotions
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- POURQUOI NOUS CHOISIR                                            -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section class="py-14 md:py-16 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1.5">Notre engagement</p>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900">Pourquoi nous choisir ?</h2>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="item in [
                            {icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', title:'Qualité garantie', desc:'Chaque produit est soigneusement sélectionné et contrôlé avant d\'être mis en vente.'},
                            {icon:'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', title:'Livraison express', desc:'Commandez aujourd\'hui, recevez rapidement. Livraison sur toute la Côte d\'Ivoire.'},
                            {icon:'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', title:'Paiement flexible', desc:'Payez en ligne, à la livraison ou par Mobile Money. Toutes options disponibles.'},
                            {icon:'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', title:'Support réactif', desc:'Notre équipe répond en quelques minutes sur WhatsApp, 7 jours sur 7.'},
                            {icon:'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', title:'Retours sans stress', desc:'Produit non conforme ? Retour et remboursement sous 7 jours, sans question.'},
                            {icon:'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', title:'Programme fidélité', desc:'Cumulez des points à chaque achat et profitez de remises exclusives.'},
                        ]"
                        :key="item.title"
                        class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-md hover:border-slate-300 transition-all"
                    >
                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mb-2">{{ item.title }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- AVIS CLIENTS                                                      -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="reviews?.length" class="py-14 md:py-16 bg-white">
            <div class="container mx-auto px-4">
                <!-- En-tête -->
                <div class="text-center mb-10">
                    <p class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-1.5">Témoignages</p>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-3">Ce que disent nos clients</h2>
                    <div v-if="review_stats" class="inline-flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-full px-5 py-2">
                        <div class="flex gap-0.5">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(review_stats.avg) ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-900">{{ review_stats.avg }}/5</span>
                        <span class="text-sm text-slate-400">·</span>
                        <span class="text-sm text-slate-500">{{ review_stats.count }} avis vérifiés</span>
                    </div>
                </div>

                <!-- Grille avis -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="review in reviews"
                        :key="review.id"
                        class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col"
                    >
                        <!-- Étoiles -->
                        <div class="flex gap-0.5 mb-3">
                            <svg v-for="(full, i) in starsFull(review.rating)" :key="i"
                                class="w-4 h-4" :class="full ? 'text-amber-400' : 'text-slate-200'"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <!-- Texte -->
                        <p class="text-sm text-slate-700 leading-relaxed flex-1 mb-4">"{{ review.body }}"</p>
                        <!-- Auteur -->
                        <div class="flex items-center gap-2.5 pt-3 border-t border-slate-100">
                            <div class="w-7 h-7 bg-slate-200 rounded-full flex items-center justify-center text-xs font-bold text-slate-600 shrink-0">
                                {{ review.author[0] }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-900">{{ review.author }}</p>
                                <p class="text-xs text-slate-400">{{ review.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- BANNIÈRES HOME BOTTOM                                            -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="banners?.home_bottom?.length" class="py-8 px-4">
            <div class="container mx-auto max-w-7xl space-y-4">
                <a v-for="b in banners.home_bottom" :key="b.id"
                   :href="b.link || '#'"
                   class="block relative rounded-2xl overflow-hidden"
                   :style="{ backgroundColor: b.background_color || '#0f172a' }">
                    <img v-if="b.image" :src="b.image" :alt="b.title || ''" class="w-full max-h-60 object-cover opacity-80" />
                    <div v-if="b.title" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6"
                         :style="{ color: b.text_color || '#fff' }">
                        <h2 class="text-2xl font-bold drop-shadow">{{ b.title }}</h2>
                        <p v-if="b.subtitle" class="mt-1 drop-shadow">{{ b.subtitle }}</p>
                        <span v-if="b.button_text" class="mt-3 inline-block px-5 py-2 bg-white/20 rounded-full text-sm font-semibold">{{ b.button_text }}</span>
                    </div>
                </a>
            </div>
        </section>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- CTA WHATSAPP FINAL                                               -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <section v-if="whatsapp_number" class="bg-slate-900 py-14">
            <div class="container mx-auto px-4 text-center">
                <!-- Icône -->
                <div class="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-white mb-3">Une question ? Besoin d'aide ?</h2>
                <p class="text-slate-400 text-base mb-8 max-w-md mx-auto">
                    Notre équipe répond en quelques minutes sur WhatsApp, 7 jours sur 7.
                </p>
                <a
                    :href="`https://wa.me/${whatsapp_number}?text=${encodeURIComponent('Bonjour, j\'ai une question sur votre boutique.')}`"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2.5 px-7 py-3.5 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Démarrer une conversation
                </a>
            </div>
        </section>

    </FrontLayout>
</template>
