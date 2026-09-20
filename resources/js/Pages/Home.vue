<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

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

const settings  = computed(() => page.props.settings || {})
const primary   = computed(() => settings.value.primary_color || '#2563EB')
const siteName  = computed(() => settings.value.site_name     || 'Notre Boutique')
const waEnabled = computed(() => settings.value.whatsapp_order_enabled !== '0')

// ─── Hero banners DB ─────────────────────────────────────────────────────────
const heroBanners = computed(() => props.banners?.home_hero || [])
const heroIndex   = ref(0)
let heroTimer     = null

const setSlide = (i) => {
    heroIndex.value = i;
    clearInterval(heroTimer);
    if (heroBanners.value.length > 1) {
        heroTimer = setInterval(() => {
            heroIndex.value = (heroIndex.value + 1) % heroBanners.value.length;
        }, 5000);
    }
};

onMounted(() => {
    if (heroBanners.value.length > 1) {
        heroTimer = setInterval(() => {
            heroIndex.value = (heroIndex.value + 1) % heroBanners.value.length;
        }, 5000);
    }
});
onUnmounted(() => { if (heroTimer) clearInterval(heroTimer); });

// ─── Bannière milieu ─────────────────────────────────────────────────────────
const promoBanner = computed(() => props.banners?.home_middle?.[0] || null)

// ─── Produits onglets ─────────────────────────────────────────────────────────
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

// ─── WhatsApp ─────────────────────────────────────────────────────────────────
const waUrl = computed(() => props.whatsapp_number
    ? `https://wa.me/${props.whatsapp_number}?text=${encodeURIComponent('Bonjour, j\'ai une question sur un article.')}`
    : null
)

// ─── Reviews ─────────────────────────────────────────────────────────────────
const stars    = (n) => Array.from({ length: 5 }, (_, i) => i < Math.round(n))
const initials = (name) => name?.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'C'

// ─── Trust items ─────────────────────────────────────────────────────────────
const trustItems = [
    { path: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', title: 'Livraison rapide', desc: "Partout en Côte d'Ivoire" },
    { path: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', title: 'Paiement sécurisé', desc: 'Transactions cryptées' },
    { path: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z', title: 'Support 7j/7', desc: 'Réponse rapide via WhatsApp' },
    { path: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', title: 'Retours faciles', desc: '7 jours, sans frais' },
]
</script>

<template>
    <FrontLayout :title="siteName">
        <Head>
            <title>{{ siteName }} — Boutique en ligne</title>
            <meta name="description" :content="`Découvrez ${siteName}. Livraison rapide partout en Côte d'Ivoire.`" />
        </Head>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [1] HERO — bannières DB ou fallback propre              -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section class="relative overflow-hidden bg-slate-900 hero-section">

            <!-- Bannières depuis DB (position home_hero) -->
            <template v-if="heroBanners.length">
                <div v-for="(b, i) in heroBanners" :key="b.id"
                     class="absolute inset-0 transition-opacity duration-700"
                     :class="heroIndex === i ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                    <img v-if="b.image" :src="b.image" :alt="b.title || siteName"
                         class="absolute inset-0 w-full h-full object-cover" />
                    <div class="absolute inset-0"
                         :style="b.image
                            ? 'background: linear-gradient(to right, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.3) 55%, transparent 100%)'
                            : ''"
                         :class="!b.image ? 'bg-slate-900' : ''"
                    />
                    <div class="relative container mx-auto px-4 hero-section flex items-center">
                        <div class="py-16 max-w-xl">
                            <p v-if="b.subtitle" class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wider">
                                {{ b.subtitle }}
                            </p>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                                {{ b.title }}
                            </h1>
                            <p v-if="b.description" class="text-white/70 text-base mb-8 max-w-md leading-relaxed">
                                {{ b.description }}
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a v-if="b.link" :href="b.link"
                                   class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white transition active:scale-95"
                                   :style="{ backgroundColor: primary }">
                                    {{ b.button_text || 'Découvrir' }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                                <Link href="/boutique"
                                      class="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-6 py-3 text-sm font-bold text-white transition backdrop-blur-sm">
                                    Voir la boutique
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation dots (plusieurs bannières) -->
                <div v-if="heroBanners.length > 1" class="absolute bottom-5 left-0 right-0 flex justify-center gap-2 z-10">
                    <button v-for="(_, i) in heroBanners" :key="i"
                            @click="setSlide(i)"
                            class="h-2 rounded-full transition-all duration-300 bg-white"
                            :class="heroIndex === i ? 'w-6 opacity-100' : 'w-2 opacity-50'" />
                </div>
            </template>

            <!-- Fallback propre (aucune bannière DB) -->
            <div v-else class="relative container mx-auto px-4 hero-section flex items-center">
                <div class="py-16 max-w-xl">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                        Bienvenue sur<br>{{ siteName }}
                    </h1>
                    <p class="text-white/60 text-base mb-8 leading-relaxed max-w-md">
                        Découvrez notre sélection de produits. Livraison rapide partout en Côte d'Ivoire.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <Link href="/boutique"
                              class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white transition"
                              :style="{ backgroundColor: primary }">
                            Voir la boutique
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </Link>
                        <a v-if="whatsapp_number && waEnabled" :href="waUrl" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-xl bg-[#25D366] hover:bg-[#1ebe5d] px-6 py-3 text-sm font-bold text-white transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [2] RÉASSURANCE                                         -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section class="bg-white border-y border-slate-100">
            <div class="container mx-auto px-4">
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
        <section v-if="featured_categories?.length" class="py-12 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black text-slate-900">Catégories</h2>
                    <Link href="/boutique"
                          class="text-sm font-semibold flex items-center gap-1 hover:opacity-70 transition"
                          :style="{ color: primary }">
                        Tout voir
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 snap-x sm:grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 sm:overflow-visible sm:pb-0 scrollbar-none">
                    <Link v-for="cat in featured_categories" :key="cat.id"
                          :href="`/categorie/${cat.slug}`"
                          class="group relative flex-shrink-0 w-36 sm:w-auto rounded-2xl overflow-hidden bg-slate-200 snap-start transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                          style="aspect-ratio: 3/4">
                        <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                        <div v-else class="absolute inset-0 flex items-center justify-center text-2xl font-black text-slate-400">
                            {{ cat.name[0] }}
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/15 to-transparent"/>
                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <p class="text-white font-bold text-sm leading-tight">{{ cat.name }}</p>
                            <p v-if="cat.products_count" class="text-white/60 text-[11px] mt-0.5">{{ cat.products_count }} articles</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [4] PRODUITS — ONGLETS                                  -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section class="py-12 bg-white" :style="{ '--primary': primary }">
            <div class="container mx-auto px-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
                    <h2 class="text-xl font-black text-slate-900">Nos produits</h2>
                    <div class="flex bg-slate-100 rounded-xl p-1 gap-1 self-start">
                        <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
                                class="px-4 py-1.5 text-[13px] font-semibold rounded-lg transition-all"
                                :class="activeTab === t.key
                                    ? 'bg-white shadow-sm text-slate-900'
                                    : 'text-slate-500 hover:text-slate-700'">
                            {{ t.label }}
                        </button>
                    </div>
                </div>
                <div v-if="currentProducts.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <ProductCard v-for="p in currentProducts" :key="p.id" :product="p"/>
                </div>
                <div v-else class="text-center py-14 text-slate-400">
                    <p class="text-sm">Aucun produit dans cette sélection.</p>
                </div>
                <div class="text-center mt-8">
                    <Link href="/boutique" class="inline-flex items-center gap-2 rounded-xl border-2 px-6 py-3 text-sm font-bold transition cta-outline">
                        Voir tous les produits
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [5] BANNIÈRE MILIEU (optionnelle, depuis DB)            -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="promoBanner" class="bg-slate-50 py-6">
            <div class="container mx-auto px-4">
                <div class="relative rounded-2xl overflow-hidden flex items-center"
                     style="min-height: 160px"
                     :style="{ backgroundColor: promoBanner.background_color || primary }">
                    <img v-if="promoBanner.image" :src="promoBanner.image" alt=""
                         class="absolute inset-0 w-full h-full object-cover opacity-20"/>
                    <div class="relative px-8 py-8 max-w-xl">
                        <h3 class="text-2xl font-black leading-tight mb-2"
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
        <!-- [6] AVIS CLIENTS                                        -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="reviews?.length" class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-8">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Avis clients</h2>
                        <p v-if="review_stats" class="text-sm text-slate-500 mt-1">
                            {{ review_stats.avg }}/5 · {{ review_stats.count }} commandes vérifiées
                        </p>
                    </div>
                    <div v-if="review_stats" class="flex items-center gap-1">
                        <svg v-for="i in 5" :key="i" class="w-4 h-4"
                             :class="i <= Math.round(review_stats.avg) ? 'text-amber-400' : 'text-slate-200'"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="r in reviews.slice(0, 6)" :key="r.id"
                         class="bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col gap-3">
                        <div class="flex gap-0.5">
                            <svg v-for="(filled, i) in stars(r.rating)" :key="i" class="w-4 h-4"
                                 :class="filled ? 'text-amber-400' : 'text-slate-200'"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed line-clamp-4 flex-1">"{{ r.body }}"</p>
                        <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                 :style="{ backgroundColor: primary + '20', color: primary }">
                                {{ initials(r.author) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ r.author }}</p>
                                <p class="text-[11px] text-slate-400">{{ r.product_name || r.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [7] CTA WHATSAPP                                        -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="whatsapp_number && waEnabled" class="py-14 bg-slate-950">
            <div class="container mx-auto px-4 text-center max-w-2xl">
                <div class="inline-flex w-12 h-12 rounded-2xl bg-[#25D366]/15 items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h2 class="text-2xl font-black text-white mb-3">Une question sur un article ?</h2>
                <p class="text-slate-400 mb-7 leading-relaxed">Notre équipe répond en moins de 5 minutes. Sans engagement.</p>
                <a :href="waUrl" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-3 rounded-xl bg-[#25D366] hover:bg-[#1ebe5d] px-7 py-3.5 text-sm font-bold text-white transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Contacter sur WhatsApp
                </a>
                <div class="flex items-center justify-center gap-6 mt-5">
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

<style scoped>
.hero-section {
    min-height: 420px;
}

.cta-outline {
    border-color: var(--primary, #2563EB);
    color: var(--primary, #2563EB);
}
.cta-outline:hover {
    background-color: var(--primary, #2563EB);
    color: white;
}

.scrollbar-none {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
