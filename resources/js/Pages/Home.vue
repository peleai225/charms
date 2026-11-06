<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import PromoBanners from '@/Components/Promo/PromoBanners.vue'
import OffersSection from '@/Components/Promo/OffersSection.vue'
import HeroBanners from '@/Components/Home/HeroBanners.vue'
import ShopFacts from '@/Components/Home/ShopFacts.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { ArrowRight, ChevronRight, Star } from 'lucide-vue-next'

const props = defineProps({
    featured_categories: Array,
    featured_products:   Array,
    new_products:        Array,
    sale_products:       Array,
    reviews:             Array,
    review_stats:        Object,
    whatsapp_number:     String,
    banners:             Object,
    offers:              { type: Array, default: () => [] },
    shop_facts:          { type: Array, default: () => [] },
})

const page = usePage()

const settings  = computed(() => page.props.settings || {})
const primary   = computed(() => settings.value.primary_color || '#2563EB')
const siteName  = computed(() => settings.value.site_name     || 'Notre Boutique')
const waEnabled = computed(() => settings.value.whatsapp_order_enabled !== '0')

const heroBanners = computed(() => props.banners?.home_hero || [])

// Grille des catégories : autant de colonnes que de catégories (jusqu'à 6), sans case vide
const categoryColumns = computed(() => ({
    1: 'sm:grid-cols-1 sm:max-w-xs', 2: 'sm:grid-cols-2 sm:max-w-xl', 3: 'sm:grid-cols-3', 4: 'sm:grid-cols-4',
    5: 'sm:grid-cols-3 lg:grid-cols-5', 6: 'sm:grid-cols-3 lg:grid-cols-6',
})[Math.min(props.featured_categories?.length || 0, 6)] || 'sm:grid-cols-3')

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
</script>

<template>
    <FrontLayout :title="siteName">
        <Head>
            <title>{{ siteName }} — Boutique en ligne</title>
            <meta name="description" :content="settings.site_description || `Découvrez ${siteName}.`" />
            <!-- Préchargement de la première bannière (LCP) : la bonne image selon l'écran -->
            <template v-if="heroBanners[0]?.image">
                <link v-if="heroBanners[0].image_mobile" rel="preload" as="image" :href="heroBanners[0].image_mobile" media="(max-width: 767px)" />
                <link rel="preload" as="image" :href="heroBanners[0].image" :media="heroBanners[0].image_mobile ? '(min-width: 768px)' : undefined" />
            </template>
        </Head>

        <!-- [1] Grande bannière (image entière, fin de l'offre dessous) -->
        <HeroBanners :banners="heroBanners" :site-name="siteName" :primary="primary"
            :featured-products="featured_products || []" :whatsapp-url="whatsapp_number && waEnabled ? waUrl : null" />

        <!-- [2] Infos réelles de la boutique : livraison, paiement, contact -->
        <ShopFacts :facts="shop_facts" />

        <!-- Offres par lot en cours (« 3 pour 10 000 F CFA ») -->
        <OffersSection :offers="offers" />

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
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>
                <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 snap-x sm:grid sm:overflow-visible sm:pb-0 scrollbar-none"
                     :class="categoryColumns">
                    <Link v-for="cat in featured_categories" :key="cat.id"
                          :href="`/categorie/${cat.slug}`"
                          class="group flex-shrink-0 w-32 sm:w-auto snap-start">
                        <div class="relative rounded-xl overflow-hidden bg-slate-200" style="aspect-ratio: 1/1">
                            <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover"/>
                            <div v-else class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-slate-400">
                                {{ cat.name[0] }}
                            </div>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-slate-900 leading-tight group-hover:underline">{{ cat.name }}</p>
                        <p v-if="cat.products_count" class="text-xs text-slate-500 mt-0.5">
                            {{ cat.products_count }} article{{ cat.products_count > 1 ? 's' : '' }}
                        </p>
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
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [5] BANNIÈRES MILIEU (position home_middle)            -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <PromoBanners :banners="banners?.home_middle || []" :primary="primary" />

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
                        <Star v-for="i in 5" :key="i" class="w-4 h-4"
                             :class="i <= Math.round(review_stats.avg) ? 'text-amber-400' : 'text-slate-200'"
                             fill="currentColor" />
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="r in reviews.slice(0, 6)" :key="r.id"
                         class="bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col gap-3">
                        <div class="flex gap-0.5">
                            <Star v-for="(filled, i) in stars(r.rating)" :key="i" class="w-4 h-4"
                                 :class="filled ? 'text-amber-400' : 'text-slate-200'"
                                 fill="currentColor" />
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

        <!-- Bannières bas de page (position home_bottom) -->
        <PromoBanners :banners="banners?.home_bottom || []" :primary="primary" />

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- [7] CTA WHATSAPP                                        -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <section v-if="whatsapp_number && waEnabled" class="py-14 bg-slate-950">
            <div class="container mx-auto px-4 text-center max-w-2xl">
                <div class="inline-flex w-12 h-12 rounded-2xl bg-[#25D366]/15 items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h2 class="text-2xl font-black text-white mb-3">Une question sur un article ?</h2>
                <p class="text-slate-400 mb-7 leading-relaxed">Taille, couleur, disponibilité : écrivez-nous sur WhatsApp.</p>
                <a :href="waUrl" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-3 rounded-xl bg-[#25D366] hover:bg-[#1ebe5d] px-7 py-3.5 text-sm font-bold text-white transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Contacter sur WhatsApp
                </a>
            </div>
        </section>

    </FrontLayout>
</template>

<style scoped>
.hero-desktop {
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
