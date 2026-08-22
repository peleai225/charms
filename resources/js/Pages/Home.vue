<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { useHelpers } from '@/Composables/useHelpers'
import { ref, computed } from 'vue'

const props = defineProps({
    featured_categories: Array,
    featured_products:   Array,
    new_products:        Array,
    sale_products:       Array,
    reviews:             Array,
    review_stats:        Object,
    whatsapp_number:     String,
    banners:             Object,
})

const page = usePage()
const { formatPrice } = useHelpers()

const settings  = computed(() => page.props.settings || {})
const primary   = computed(() => settings.value.primary_color  || '#2563EB')
const accent    = computed(() => settings.value.accent_color   || '#f59e0b')
const siteName  = computed(() => settings.value.site_name      || 'Notre Boutique')
const waEnabled = computed(() => settings.value.whatsapp_order_enabled !== '0')

const activeTab = ref('featured')
const tabs = [
    { key: 'featured', label: 'Sélection' },
    { key: 'new',      label: 'Nouveautés' },
    { key: 'sale',     label: 'Promotions' },
]
const currentProducts = computed(() => ({
    featured: props.featured_products || [],
    new:      props.new_products      || [],
    sale:     props.sale_products     || [],
})[activeTab.value] || [])

const promoBanner = computed(() => props.banners?.home_middle?.[0] || null)

const waUrl = computed(() => props.whatsapp_number
    ? `https://wa.me/${props.whatsapp_number}?text=${encodeURIComponent('Bonjour, j\'ai une question sur un article.')}`
    : null
)

const pct = (p) => p.compare_price ? Math.round((1 - p.price / p.compare_price) * 100) : null
const stars = (n) => Array.from({ length: 5 }, (_, i) => i < Math.round(n))
const initials = (name) => name?.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'C'

const trustItems = [
    { path: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', title: 'Livraison rapide', desc: "Partout en Côte d'Ivoire" },
    { path: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', title: 'Paiement sécurisé', desc: 'Transactions SSL cryptées' },
    { path: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z', title: 'Support 7j/7', desc: 'Réponse rapide via WhatsApp' },
    { path: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', title: 'Retours faciles', desc: '7 jours, sans frais' },
]
</script>

<template>
    <FrontLayout :title="siteName">
        <Head>
            <title>{{ siteName }} — Boutique en ligne</title>
            <meta name="description" content="Découvrez notre sélection. Livraison rapide en Côte d'Ivoire." />
        </Head>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [1] HERO                                                -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section class="relative bg-slate-950 text-white overflow-hidden min-h-[92vh] flex items-center">

            <!-- Accent blob subtil -->
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full opacity-[0.07] blur-3xl"
                     :style="{ backgroundColor: primary }"/>
                <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full opacity-[0.04] blur-2xl"
                     :style="{ backgroundColor: accent }"/>
            </div>

            <div class="relative w-full container mx-auto px-4 sm:px-6 py-20 lg:py-28">
                <div class="grid lg:grid-cols-[1fr_420px] xl:grid-cols-[1fr_480px] gap-10 xl:gap-16 items-center">

                    <!-- Texte -->
                    <div class="max-w-xl">
                        <!-- Badge pill -->
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 mb-7">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"/>
                            <span class="text-[11px] font-semibold tracking-widest uppercase text-white/80">Nouvelles arrivées</span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-[3.4rem] font-black leading-[1.04] tracking-tight mb-5">
                            Tout ce que<br>
                            vous aimez,<br>
                            <span :style="{ color: primary }">livré chez vous.</span>
                        </h1>

                        <p class="text-slate-400 text-base sm:text-lg leading-relaxed mb-9 max-w-sm">
                            Mode, maison, enfants… Une sélection soignée livrée rapidement partout en Côte d'Ivoire.
                        </p>

                        <div class="flex flex-wrap gap-3 mb-10">
                            <Link href="/boutique"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100 active:scale-95">
                                Voir la boutique
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </Link>
                            <a v-if="whatsapp_number && waEnabled"
                                :href="waUrl" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#25D366] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#1ebe5d] active:scale-95">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                        </div>

                        <!-- Stats -->
                        <div v-if="review_stats" class="flex items-center gap-6 pt-7 border-t border-white/[0.08]">
                            <div>
                                <p class="text-2xl font-black tabular-nums">{{ review_stats.count }}+</p>
                                <p class="text-[11px] text-slate-500 mt-0.5 uppercase tracking-wider">clients satisfaits</p>
                            </div>
                            <div class="w-px h-8 bg-white/10"/>
                            <div>
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <div class="flex gap-0.5">
                                        <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5"
                                             :class="i <= Math.round(review_stats.avg) ? 'text-amber-400' : 'text-slate-700'"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold">{{ review_stats.avg }}/5</span>
                                </div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider">note moyenne</p>
                            </div>
                            <div class="w-px h-8 bg-white/10"/>
                            <div>
                                <p class="text-2xl font-black">7j/7</p>
                                <p class="text-[11px] text-slate-500 mt-0.5 uppercase tracking-wider">support</p>
                            </div>
                        </div>
                    </div>

                    <!-- Grille produits vedettes -->
                    <div v-if="featured_products?.length" class="hidden lg:grid grid-cols-2 gap-3">
                        <!-- Grand card -->
                        <Link :href="`/produit/${featured_products[0].slug}`"
                            class="group col-span-2 relative rounded-2xl overflow-hidden bg-slate-800 border border-white/10 hover:border-white/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
                            style="min-height:220px">
                            <img v-if="featured_products[0].primary_image"
                                :src="`/storage/${featured_products[0].primary_image}`"
                                :alt="featured_products[0].name"
                                class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-65 group-hover:scale-105 transition-all duration-500"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent"/>
                            <div class="relative p-5 flex flex-col justify-between h-full" style="min-height:220px">
                                <div class="flex justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full"
                                          :style="{ backgroundColor: accent + '33', color: accent }">Coup de cœur</span>
                                    <span v-if="pct(featured_products[0])" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        -{{ pct(featured_products[0]) }}%
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">{{ featured_products[0].category_name }}</p>
                                    <p class="text-white font-bold text-sm leading-snug line-clamp-2 mb-2">{{ featured_products[0].name }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-white font-black">{{ formatPrice(featured_products[0].price) }}</span>
                                        <span class="text-xs text-slate-300 group-hover:text-white flex items-center gap-1 transition">
                                            Voir
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                        <!-- Petits cards -->
                        <Link v-for="p in featured_products.slice(1, 3)" :key="p.id"
                            :href="`/produit/${p.slug}`"
                            class="group relative rounded-xl overflow-hidden bg-slate-800 border border-white/10 hover:border-white/20 transition-all duration-300 hover:-translate-y-1"
                            style="min-height:140px">
                            <img v-if="p.primary_image" :src="`/storage/${p.primary_image}`" :alt="p.name"
                                class="absolute inset-0 w-full h-full object-cover opacity-45 group-hover:opacity-60 group-hover:scale-105 transition-all duration-500"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent"/>
                            <div class="relative p-4 flex flex-col justify-end h-full" style="min-height:140px">
                                <p class="text-white font-semibold text-xs line-clamp-2 mb-1">{{ p.name }}</p>
                                <p class="font-black text-sm" :style="{ color: accent }">{{ formatPrice(p.price) }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [2] RÉASSURANCE                                         -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section class="bg-white border-y border-slate-100">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-slate-100">
                    <div v-for="item in trustItems" :key="item.title"
                        class="flex flex-col sm:flex-row items-center sm:items-start gap-3 px-5 py-5 text-center sm:text-left">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                             :style="{ backgroundColor: primary + '15' }">
                            <svg class="w-5 h-5" :style="{ color: primary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.path"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-slate-900">{{ item.title }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [3] CATÉGORIES                                          -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="featured_categories?.length" class="py-16 bg-slate-50">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest mb-1" :style="{ color: primary }">Explorer</p>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Nos catégories</h2>
                    </div>
                    <Link href="/boutique" class="text-sm font-semibold flex items-center gap-1 transition hover:opacity-70"
                          :style="{ color: primary }">
                        Tout voir
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>

                <!-- Scroll mobile / grille desktop -->
                <div class="flex gap-4 overflow-x-auto pb-3 -mx-4 px-4 sm:mx-0 sm:px-0 snap-x sm:grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 sm:overflow-visible sm:pb-0">
                    <Link v-for="cat in featured_categories" :key="cat.id"
                        :href="`/categorie/${cat.slug}`"
                        class="group relative flex-shrink-0 w-40 sm:w-auto rounded-2xl overflow-hidden aspect-[3/4] bg-slate-200 snap-start transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                        <div v-else class="absolute inset-0 flex items-center justify-center text-2xl font-black text-slate-400">
                            {{ cat.name[0] }}
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"/>
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <p class="text-white font-bold text-sm leading-tight">{{ cat.name }}</p>
                            <p v-if="cat.products_count" class="text-white/60 text-[11px] mt-0.5">{{ cat.products_count }} articles</p>
                        </div>
                        <!-- Hover border -->
                        <div class="absolute inset-0 rounded-2xl ring-2 ring-transparent group-hover:ring-white/30 transition-all duration-300"/>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [4] PRODUITS — ONGLETS                                  -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section class="py-16 bg-white">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest mb-1" :style="{ color: primary }">Catalogue</p>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Nos produits</h2>
                    </div>
                    <!-- Tabs -->
                    <div class="flex bg-slate-100 rounded-xl p-1 gap-1 self-start sm:self-auto">
                        <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
                            class="px-4 py-2 text-[13px] font-semibold rounded-lg transition-all"
                            :class="activeTab === t.key ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'">
                            {{ t.label }}
                        </button>
                    </div>
                </div>

                <!-- Grille -->
                <div v-if="currentProducts.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                    <ProductCard v-for="p in currentProducts" :key="p.id" :product="p"/>
                </div>
                <div v-else class="text-center py-16 text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-sm font-medium">Aucun produit dans cette sélection</p>
                </div>

                <div class="text-center mt-10">
                    <Link href="/boutique"
                        class="inline-flex items-center gap-2 rounded-xl border-2 px-7 py-3 text-sm font-bold transition hover:text-white"
                        :style="{ borderColor: primary, color: primary }"
                        @mouseenter="e => { e.target.style.backgroundColor = primary }"
                        @mouseleave="e => { e.target.style.backgroundColor = 'transparent' }">
                        Voir tous les produits
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [5] BANNIÈRE PROMO (optionnelle, depuis DB)             -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="promoBanner" class="py-6 bg-slate-50">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="relative rounded-3xl overflow-hidden min-h-[180px] flex items-center"
                     :style="{ backgroundColor: promoBanner.background_color || primary }">
                    <img v-if="promoBanner.image" :src="promoBanner.image" alt=""
                        class="absolute inset-0 w-full h-full object-cover opacity-20"/>
                    <div class="relative px-8 py-10 sm:px-12 max-w-xl">
                        <p class="text-[11px] font-bold uppercase tracking-widest opacity-70 mb-2"
                           :style="{ color: promoBanner.text_color || '#fff' }">Offre spéciale</p>
                        <h3 class="text-2xl sm:text-3xl font-black leading-tight mb-3"
                            :style="{ color: promoBanner.text_color || '#fff' }">{{ promoBanner.title }}</h3>
                        <p v-if="promoBanner.subtitle" class="text-sm opacity-80 mb-5"
                           :style="{ color: promoBanner.text_color || '#fff' }">{{ promoBanner.subtitle }}</p>
                        <a v-if="promoBanner.link" :href="promoBanner.link"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold transition hover:opacity-90"
                            :style="{ color: promoBanner.background_color || primary }">
                            {{ promoBanner.button_text || 'Découvrir' }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [6] SOCIAL PROOF                                        -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="reviews?.length" class="py-16 bg-slate-50">
            <div class="container mx-auto px-4 sm:px-6">
                <!-- Header + stats -->
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest mb-1" :style="{ color: primary }">Témoignages</p>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Ce que disent nos clients</h2>
                    </div>
                    <div v-if="review_stats" class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-3xl font-black text-slate-900 tabular-nums">{{ review_stats.count }}+</p>
                            <p class="text-[11px] text-slate-500 uppercase tracking-wider mt-0.5">commandes</p>
                        </div>
                        <div class="w-px h-10 bg-slate-200"/>
                        <div class="text-center">
                            <div class="flex items-center gap-1 justify-center mb-0.5">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-2xl font-black text-slate-900">{{ review_stats.avg }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 uppercase tracking-wider">sur 5</p>
                        </div>
                    </div>
                </div>

                <!-- Cards -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="r in reviews" :key="r.id"
                        class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col gap-4">
                        <!-- Étoiles -->
                        <div class="flex gap-0.5">
                            <svg v-for="(filled, i) in stars(r.rating)" :key="i"
                                class="w-4 h-4" :class="filled ? 'text-amber-400' : 'text-slate-200'"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <!-- Texte -->
                        <p class="text-sm text-slate-700 leading-relaxed line-clamp-4 flex-1">"{{ r.body }}"</p>
                        <!-- Auteur -->
                        <div class="flex items-center gap-3 pt-2 border-t border-slate-50">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                 :style="{ backgroundColor: primary + '20', color: primary }">
                                {{ initials(r.author) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ r.author }}</p>
                                <p v-if="r.product_name" class="text-[11px] text-slate-400 line-clamp-1">{{ r.product_name }}</p>
                                <p v-else class="text-[11px] text-slate-400">{{ r.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [7] CTA WHATSAPP                                        -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="whatsapp_number && waEnabled" class="py-16 bg-slate-950">
            <div class="container mx-auto px-4 sm:px-6 text-center max-w-2xl">
                <div class="inline-flex w-14 h-14 rounded-2xl bg-[#25D366]/15 items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-3">
                    Une question sur un article ?
                </h2>
                <p class="text-slate-400 mb-8 leading-relaxed">
                    Notre équipe répond en moins de 5 minutes. Sans engagement, sans prise de tête.
                </p>
                <a :href="waUrl" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-3 rounded-xl bg-[#25D366] px-8 py-4 text-base font-bold text-white transition hover:bg-[#1ebe5d] active:scale-95 shadow-lg shadow-[#25D366]/20">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Contacter sur WhatsApp
                </a>
                <div class="flex items-center justify-center gap-6 mt-6">
                    <span class="text-[12px] text-slate-600 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>Réponse rapide
                    </span>
                    <span class="text-[12px] text-slate-600 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>7j/7
                    </span>
                    <span class="text-[12px] text-slate-600 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>Sans engagement
                    </span>
                </div>
            </div>
        </section>

    </FrontLayout>
</template>
