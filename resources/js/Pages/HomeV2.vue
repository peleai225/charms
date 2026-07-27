<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useCartStore } from '@/Stores/cart';
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
const cartStore = useCartStore();

const siteName = computed(() => page.props.settings?.site_name || 'Chamse');
const logo     = computed(() => page.props.settings?.logo);

// Onglet actif produits
const activeTab = ref('featured');
const currentProducts = computed(() => {
    if (activeTab.value === 'new')  return props.new_products  || [];
    if (activeTab.value === 'sale') return props.sale_products || [];
    return props.featured_products || [];
});
</script>

<template>
    <FrontLayout title="Accueil">
        <Head>
            <title>{{ siteName }} — Boutique en ligne</title>
            <meta name="description" content="Découvrez notre sélection de produits. Livraison rapide en Côte d'Ivoire." />
        </Head>

        <!-- ══════════════════════════════════════════════════════════════════
             HERO — Full-width image avec overlay gradient + texte centré
        ══════════════════════════════════════════════════════════════════ -->
        <section class="relative min-h-[70vh] flex items-end overflow-hidden bg-slate-900">

            <!-- Image de fond (premier produit vedette) -->
            <template v-if="featured_products?.[0]?.primary_image">
                <img
                    :src="`/storage/${featured_products[0].primary_image}`"
                    :alt="featured_products[0].name"
                    class="absolute inset-0 w-full h-full object-cover opacity-40"
                />
            </template>
            <div v-else class="absolute inset-0 bg-gradient-to-br from-primary-900 via-primary-800 to-slate-900"></div>

            <!-- Overlay gradient bas → haut -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>

            <!-- Contenu héro -->
            <div class="relative w-full container mx-auto px-4 pb-16 pt-32">
                <div class="max-w-2xl">
                    <!-- Badge -->
                    <div class="flex items-center gap-2 mb-5">
                        <span class="w-8 h-px bg-primary-400"></span>
                        <span class="text-primary-400 text-xs font-bold uppercase tracking-widest">Nouvelle saison</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-black text-white leading-[1.02] tracking-tight mb-6">
                        Style &<br>
                        <em class="not-italic text-primary-400">Élégance</em>
                    </h1>

                    <p class="text-slate-300 text-lg leading-relaxed mb-8 max-w-lg">
                        Une sélection soignée livrée rapidement partout en Côte d'Ivoire.
                    </p>

                    <div class="flex flex-wrap gap-3 items-center">
                        <Link href="/boutique"
                            class="px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-full transition">
                            Découvrir la boutique
                        </Link>
                        <Link href="/boutique?on_sale=1"
                            class="px-8 py-3.5 bg-white/10 hover:bg-white/20 text-white text-sm font-semibold rounded-full border border-white/20 transition">
                            Voir les promos
                        </Link>
                    </div>
                </div>

                <!-- Stats flottantes bas-droite -->
                <div v-if="review_stats" class="absolute bottom-16 right-4 md:right-8 hidden md:flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-2xl font-black text-white">{{ review_stats.avg }}<span class="text-sm text-slate-400">/5</span></p>
                        <p class="text-xs text-slate-500">Note client</p>
                    </div>
                    <div class="w-px h-10 bg-white/10"></div>
                    <div class="text-right">
                        <p class="text-2xl font-black text-white">{{ review_stats.count }}<span class="text-sm text-primary-400">+</span></p>
                        <p class="text-xs text-slate-500">Clients satisfaits</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             BANDE TRUST — Icons horizontaux
        ══════════════════════════════════════════════════════════════════ -->
        <section class="bg-white border-b border-slate-100">
            <div class="container mx-auto px-4">
                <div class="flex overflow-x-auto gap-0 divide-x divide-slate-100 no-scrollbar">
                    <div v-for="item in [
                        { icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8',          label: 'Livraison rapide' },
                        { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', label: 'Paiement sécurisé' },
                        { icon: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z', label: 'Support 7j/7' },
                        { icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', label: 'Retours 7 jours' },
                    ]" :key="item.label"
                        class="flex-1 flex items-center justify-center gap-2.5 px-4 py-4 min-w-[130px]">
                        <svg class="w-5 h-5 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon"/>
                        </svg>
                        <span class="text-xs font-semibold text-slate-700 whitespace-nowrap">{{ item.label }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             CATÉGORIES — Scroll horizontal sur mobile, grille sur desktop
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="featured_categories?.length" class="py-14 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900">
                        <span class="text-primary-600">–</span> Collections
                    </h2>
                    <Link href="/boutique" class="text-sm text-primary-600 hover:text-primary-700 font-semibold transition">
                        Tout voir →
                    </Link>
                </div>

                <!-- Grille catégories — style pills horizontaux -->
                <div class="flex flex-wrap gap-3 mb-8">
                    <Link
                        v-for="cat in featured_categories"
                        :key="cat.id"
                        :href="`/categorie/${cat.slug}`"
                        class="group flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-4 py-3 hover:border-primary-300 hover:shadow-md transition-all"
                    >
                        <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                            <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="lazy"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 group-hover:text-primary-600 transition-colors">{{ cat.name }}</p>
                            <p v-if="cat.products_count" class="text-xs text-slate-400">{{ cat.products_count }} produits</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             BANNIÈRE HERO
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="banners?.home_hero?.length">
            <a v-for="b in banners.home_hero" :key="b.id"
               :href="b.link || '#'"
               class="block relative overflow-hidden"
               :style="b.background_color ? { backgroundColor: b.background_color } : {}">
                <img v-if="b.image" :src="b.image" :alt="b.title || ''" class="w-full max-h-80 object-cover"/>
                <div v-if="b.title" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6"
                     :style="{ color: b.text_color || '#fff' }">
                    <h2 class="text-2xl md:text-4xl font-black drop-shadow-lg">{{ b.title }}</h2>
                    <p v-if="b.subtitle" class="mt-2 text-lg drop-shadow">{{ b.subtitle }}</p>
                    <span v-if="b.button_text" class="mt-4 inline-block px-6 py-2.5 bg-white/20 hover:bg-white/30 rounded-full text-sm font-bold backdrop-blur transition">
                        {{ b.button_text }}
                    </span>
                </div>
            </a>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             PRODUITS — Layout magazine : grand produit + grille
        ══════════════════════════════════════════════════════════════════ -->
        <section class="py-14 bg-white">
            <div class="container mx-auto px-4">

                <!-- Header + tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900">
                        <span class="text-primary-600">–</span> Nos produits
                    </h2>
                    <div class="flex gap-1 bg-slate-100 rounded-xl p-1">
                        <button v-for="(label, key) in { featured: 'Sélection', new: 'Nouveautés', sale: 'Promotions' }"
                            :key="key" @click="activeTab = key"
                            class="px-4 py-2 text-xs font-semibold rounded-lg transition"
                            :class="activeTab === key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                            {{ label }}
                        </button>
                    </div>
                </div>

                <!-- Layout magazine : 1 grand + grille petits -->
                <div v-if="currentProducts.length" class="grid lg:grid-cols-3 gap-5">

                    <!-- Grand produit vedette -->
                    <Link v-if="currentProducts[0]"
                        :href="`/produit/${currentProducts[0].slug}`"
                        class="group lg:col-span-1 lg:row-span-2 relative bg-slate-900 rounded-3xl overflow-hidden flex flex-col justify-end min-h-[380px] lg:min-h-[500px] border border-white/5 hover:shadow-2xl transition-all duration-300">
                        <img v-if="currentProducts[0].primary_image"
                            :src="`/storage/${currentProducts[0].primary_image}`"
                            :alt="currentProducts[0].name"
                            class="absolute inset-0 w-full h-full object-cover opacity-55 group-hover:opacity-70 group-hover:scale-105 transition-all duration-500"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
                        <div class="relative p-6">
                            <span v-if="currentProducts[0].is_new" class="inline-block bg-primary-500 text-white text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase mb-2">Nouveau</span>
                            <span v-else-if="currentProducts[0].compare_price" class="inline-block bg-red-500 text-white text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase mb-2">
                                -{{ Math.round((1 - currentProducts[0].price / currentProducts[0].compare_price) * 100) }}%
                            </span>
                            <p class="text-xs text-slate-400 mb-1">{{ currentProducts[0].category_name }}</p>
                            <h3 class="text-white font-black text-xl leading-tight mb-3 line-clamp-2">{{ currentProducts[0].name }}</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-white font-black text-2xl">{{ formatPrice(currentProducts[0].price) }}</span>
                                    <span v-if="currentProducts[0].compare_price" class="text-slate-500 text-sm line-through ml-2">{{ formatPrice(currentProducts[0].compare_price) }}</span>
                                </div>
                                <span class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-primary-600 transition-colors">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- Grille 2×3 petits produits -->
                    <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-4">
                        <ProductCard
                            v-for="p in currentProducts.slice(1, 7)"
                            :key="p.id"
                            :product="p"
                        />
                    </div>
                </div>

                <div v-else class="py-16 text-center text-slate-400 text-sm">Aucun produit pour l'instant.</div>

                <div class="mt-8 text-center">
                    <Link href="/boutique"
                        class="inline-flex items-center gap-2 px-7 py-3.5 border-2 border-slate-900 text-slate-900 text-sm font-bold rounded-full hover:bg-slate-900 hover:text-white transition-all">
                        Voir tous les produits
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             BANNIÈRES MIDDLE
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="banners?.home_middle?.length" class="py-8 px-4">
            <div class="container mx-auto max-w-7xl">
                <div class="grid gap-4" :class="banners.home_middle.length > 1 ? 'md:grid-cols-2' : ''">
                    <a v-for="b in banners.home_middle" :key="b.id"
                       :href="b.link || '#'"
                       class="relative rounded-2xl overflow-hidden"
                       :style="{ backgroundColor: b.background_color || '#f1f5f9' }">
                        <img v-if="b.image" :src="b.image" :alt="b.title || ''" class="w-full h-48 object-cover"/>
                        <div v-if="b.title" class="absolute inset-0 flex flex-col justify-end p-5"
                             :style="{ color: b.text_color || '#fff' }">
                            <h3 class="text-xl font-bold drop-shadow">{{ b.title }}</h3>
                            <p v-if="b.subtitle" class="text-sm drop-shadow">{{ b.subtitle }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION PROMO — si produits en solde
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="sale_products?.length" class="py-12 bg-primary-600">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-5">
                    <div>
                        <p class="text-primary-200 text-xs font-bold uppercase tracking-widest mb-2">Offres spéciales</p>
                        <h2 class="text-2xl md:text-3xl font-black text-white">Jusqu'à -50% sur une sélection</h2>
                        <p class="text-primary-100 text-sm mt-1">Profitez-en avant la fin des stocks</p>
                    </div>
                    <Link href="/boutique?on_sale=1"
                        class="shrink-0 px-7 py-3.5 bg-white text-primary-700 text-sm font-black rounded-full hover:bg-primary-50 transition">
                        Voir les promotions →
                    </Link>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             AVIS CLIENTS — Style minimaliste liste
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="reviews?.length" class="py-14 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">
                        <span class="text-primary-600">–</span> Avis clients
                    </h2>
                    <div v-if="review_stats" class="inline-flex items-center gap-2">
                        <div class="flex gap-0.5">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4"
                                :class="i <= Math.round(review_stats.avg) ? 'text-amber-400' : 'text-slate-200'"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-slate-800">{{ review_stats.avg }}/5</span>
                        <span class="text-slate-400 text-sm">— {{ review_stats.count }} avis</span>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="review in reviews" :key="review.id"
                        class="bg-white rounded-2xl p-5 border border-slate-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center text-sm font-black text-primary-700 shrink-0">
                                {{ review.author[0] }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ review.author }}</p>
                                <p class="text-[10px] text-slate-400">{{ review.created_at }}</p>
                            </div>
                            <div class="ml-auto flex gap-0.5">
                                <svg v-for="i in 5" :key="i" class="w-3 h-3"
                                    :class="i <= review.rating ? 'text-amber-400' : 'text-slate-200'"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed line-clamp-3">"{{ review.body }}"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             BANNIÈRES BOTTOM
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="banners?.home_bottom?.length" class="py-8 px-4">
            <div class="container mx-auto max-w-7xl space-y-4">
                <a v-for="b in banners.home_bottom" :key="b.id"
                   :href="b.link || '#'"
                   class="block relative rounded-2xl overflow-hidden"
                   :style="{ backgroundColor: b.background_color || '#0f172a' }">
                    <img v-if="b.image" :src="b.image" :alt="b.title || ''" class="w-full max-h-60 object-cover opacity-70"/>
                    <div v-if="b.title" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6"
                         :style="{ color: b.text_color || '#fff' }">
                        <h2 class="text-2xl font-bold drop-shadow">{{ b.title }}</h2>
                        <p v-if="b.subtitle" class="mt-1 drop-shadow">{{ b.subtitle }}</p>
                        <span v-if="b.button_text" class="mt-3 inline-block px-5 py-2 bg-white/20 rounded-full text-sm font-semibold">{{ b.button_text }}</span>
                    </div>
                </a>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════
             CTA FINAL — WhatsApp
        ══════════════════════════════════════════════════════════════════ -->
        <section v-if="whatsapp_number" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="max-w-xl mx-auto text-center">
                    <div class="w-14 h-14 bg-green-500 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-3">Besoin d'aide ?</h2>
                    <p class="text-slate-500 mb-7">Notre équipe répond en quelques minutes sur WhatsApp, 7 jours sur 7.</p>
                    <a :href="`https://wa.me/${whatsapp_number}?text=${encodeURIComponent('Bonjour, j\'ai une question sur votre boutique.')}`"
                        target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2.5 px-7 py-3.5 bg-green-500 hover:bg-green-600 text-white text-sm font-bold rounded-full transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contacter sur WhatsApp
                    </a>
                </div>
            </div>
        </section>

    </FrontLayout>
</template>
