<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ProductCard from '@/Components/ProductCard.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed } from 'vue';

const props = defineProps({
    product:          Object,
    related_products: Array,
    whatsapp_number:  String,
});

const { formatPrice } = useHelpers();

// ─── Galerie ──────────────────────────────────────────────────────────────────
const activeImage = ref(0);
const colorImageOverride = ref(null);

// ─── Variantes ────────────────────────────────────────────────────────────────
const selectedColorId     = ref(null);
const selectedSecondaryId = ref(null);

if (props.product.has_variants && props.product.colors?.length) {
    selectedColorId.value = props.product.colors[0].id;
}

const availableSecondaryValues = computed(() => {
    if (!selectedColorId.value || !props.product.secondary_attribute) return [];
    const ids = props.product.variants
        .filter(v => v.color_id === selectedColorId.value)
        .map(v => v.secondary_id);
    return (props.product.secondary_attribute.values ?? []).filter(v => ids.includes(v.id));
});

if (props.product.secondary_attribute && availableSecondaryValues.value.length) {
    selectedSecondaryId.value = availableSecondaryValues.value[0].id;
}

const selectedVariant = computed(() => {
    if (!props.product.has_variants) return null;
    return props.product.variants.find(v =>
        v.color_id === selectedColorId.value &&
        (props.product.secondary_attribute ? v.secondary_id === selectedSecondaryId.value : true)
    );
});

const selectColor = (colorId) => {
    selectedColorId.value = colorId;
    const available = props.product.variants.filter(v => v.color_id === colorId).map(v => v.secondary_id);
    if (available.length) selectedSecondaryId.value = available[0];

    // Priority: color attribute image > variant image > gallery
    const color = props.product.colors.find(c => c.id === colorId);
    const variantImg = props.product.variants.find(v => v.color_id === colorId)?.image;
    if (color?.image) {
        colorImageOverride.value = color.image;
    } else if (variantImg) {
        colorImageOverride.value = variantImg;
    } else {
        colorImageOverride.value = null;
    }
};

const currentPrice = computed(() => selectedVariant.value?.price ?? props.product.price);
const currentStock = computed(() => selectedVariant.value?.stock ?? props.product.stock);
const discountPct  = computed(() => {
    if (!props.product.compare_price) return null;
    return Math.round((1 - currentPrice.value / props.product.compare_price) * 100);
});

// ─── Panier ───────────────────────────────────────────────────────────────────
const quantity = ref(1);
const form = useForm({ product_id: props.product.id, variant_id: null, quantity: 1 });

const addToCart = () => {
    if (props.product.has_variants && !selectedVariant.value) return;
    form.variant_id = selectedVariant.value?.id ?? null;
    form.quantity   = quantity.value;
    form.post('/panier/ajouter', { preserveScroll: true });
};

// ─── WhatsApp ─────────────────────────────────────────────────────────────────
const waMessage = computed(() => {
    const varLabel = selectedVariant.value ? ` (${props.product.colors?.find(c => c.id === selectedColorId.value)?.name ?? ''})` : '';
    const url = typeof window !== 'undefined' ? window.location.href : '';
    return encodeURIComponent(`Bonjour, je souhaite commander : ${props.product.name}${varLabel} — ${formatPrice(currentPrice.value)}\n${url}`);
});

// ─── Tabs ──────────────────────────────────────────────────────────────────────
const activeTab = ref('description');

// ─── Avis ──────────────────────────────────────────────────────────────────────
const stars = (n) => Array.from({ length: 5 }, (_, i) => i < Math.round(n));
</script>

<template>
    <FrontLayout :title="product.name">
        <Head>
            <title>{{ product.name }}</title>
            <meta name="description" :content="product.short_description" />
        </Head>

        <!-- Barre top "annonce" style Beauty Shop -->
        <div class="bg-slate-800 text-slate-300 text-xs text-center py-2 px-4">
            Livraison rapide en Côte d'Ivoire &nbsp;·&nbsp; Paiement sécurisé &nbsp;·&nbsp; Support 7j/7
        </div>

        <!-- Breadcrumb -->
        <div class="bg-slate-50 border-b border-slate-200 py-3">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-1.5 text-xs text-slate-400">
                    <Link href="/" class="hover:text-slate-600 transition">Accueil</Link>
                    <span>/</span>
                    <Link href="/boutique" class="hover:text-slate-600 transition">Boutique</Link>
                    <template v-if="product.category">
                        <span>/</span>
                        <Link :href="`/categorie/${product.category.slug}`" class="hover:text-slate-600 transition">{{ product.category.name }}</Link>
                    </template>
                    <span>/</span>
                    <span class="text-slate-700">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8 max-w-6xl">

            <!-- ─── Bloc principal : galerie + infos ───────────────── -->
            <div class="grid lg:grid-cols-2 gap-10 mb-14">

                <!-- Galerie -->
                <div>
                    <!-- Image principale -->
                    <div class="relative bg-slate-100 rounded-2xl overflow-hidden aspect-square mb-3 border border-slate-200">
                        <img
                            v-if="colorImageOverride"
                            :src="colorImageOverride"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <img
                            v-else-if="product.images[activeImage]"
                            :src="`/storage/${product.images[activeImage]}`"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                            <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <!-- Badge promo -->
                        <div v-if="discountPct" class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                            -{{ discountPct }}%
                        </div>
                        <!-- Prev / Next -->
                        <template v-if="product.images.length > 1">
                            <button @click="activeImage = (activeImage - 1 + product.images.length) % product.images.length"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/80 hover:bg-white rounded-full shadow flex items-center justify-center transition">
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="activeImage = (activeImage + 1) % product.images.length"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/80 hover:bg-white rounded-full shadow flex items-center justify-center transition">
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </template>
                    </div>
                    <!-- Thumbnails -->
                    <div v-if="product.images.length > 1" class="grid grid-cols-5 gap-2">
                        <button
                            v-for="(img, i) in product.images.slice(0, 5)"
                            :key="i"
                            @click="activeImage = i"
                            class="aspect-square bg-slate-100 rounded-xl overflow-hidden border-2 transition"
                            :class="activeImage === i ? 'border-primary-600' : 'border-transparent hover:border-slate-300'"
                        >
                            <img :src="`/storage/${img}`" :alt="`${product.name} ${i+1}`" class="w-full h-full object-cover" loading="lazy" />
                        </button>
                    </div>
                </div>

                <!-- Infos produit -->
                <div class="flex flex-col">
                    <!-- Catégorie + nom -->
                    <p v-if="product.category" class="text-xs font-semibold text-primary-600 uppercase tracking-wider mb-1">{{ product.category.name }}</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-3 leading-tight">{{ product.name }}</h1>

                    <!-- Avis inline -->
                    <div v-if="product.review_count > 0" class="flex items-center gap-2 mb-4">
                        <div class="flex gap-0.5">
                            <svg v-for="(full, i) in stars(product.review_avg)" :key="i" class="w-4 h-4" :class="full ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <button @click="activeTab = 'reviews'" class="text-sm text-slate-500 hover:text-slate-700 transition underline underline-offset-2">
                            {{ product.review_avg }} ({{ product.review_count }} avis)
                        </button>
                    </div>

                    <!-- Prix -->
                    <div class="flex items-baseline gap-3 mb-4">
                        <span class="text-3xl font-bold text-slate-900">{{ formatPrice(currentPrice) }}</span>
                        <span v-if="product.compare_price" class="text-xl text-slate-400 line-through">{{ formatPrice(product.compare_price) }}</span>
                    </div>

                    <!-- Description courte -->
                    <p v-if="product.short_description" class="text-sm text-slate-600 leading-relaxed mb-5 border-t border-slate-100 pt-4">
                        {{ product.short_description }}
                    </p>

                    <!-- Sélecteur couleur -->
                    <div v-if="product.has_variants && product.colors.length" class="mb-5">
                        <p class="text-xs font-semibold text-slate-700 uppercase tracking-wide mb-2">
                            Couleur — <span class="font-normal normal-case text-slate-500">{{ product.colors.find(c => c.id === selectedColorId)?.name }}</span>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in product.colors"
                                :key="color.id"
                                @click="selectColor(color.id)"
                                class="w-9 h-9 rounded-full border-2 transition-all relative shadow-sm overflow-hidden"
                                :style="!color.image ? { backgroundColor: color.hex || '#94a3b8' } : {}"
                                :class="selectedColorId === color.id ? 'border-primary-600 ring-2 ring-offset-1 ring-primary-600' : 'border-white hover:scale-110'"
                                :title="color.name"
                            >
                                <img v-if="color.image" :src="color.image" :alt="color.name" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Sélecteur taille / attribut secondaire -->
                    <div v-if="product.secondary_attribute && availableSecondaryValues.length" class="mb-5">
                        <p class="text-xs font-semibold text-slate-700 uppercase tracking-wide mb-2">
                            {{ product.secondary_attribute.name }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="val in availableSecondaryValues"
                                :key="val.id"
                                @click="selectedSecondaryId = val.id"
                                class="px-4 py-1.5 border rounded-lg text-sm font-medium transition"
                                :class="selectedSecondaryId === val.id
                                    ? 'bg-primary-600 border-primary-600 text-white'
                                    : 'border-slate-300 text-slate-700 hover:border-primary-600'"
                            >
                                {{ val.value }}
                            </button>
                        </div>
                    </div>

                    <!-- Stock badge -->
                    <div class="mb-5">
                        <span v-if="currentStock > 0" class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-3 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            En stock · {{ currentStock }} disponibles
                        </span>
                        <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-3 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                            Rupture de stock
                        </span>
                    </div>

                    <!-- Quantité -->
                    <div v-if="currentStock > 0" class="flex items-center gap-3 mb-5">
                        <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                            <button @click="quantity = Math.max(1, quantity - 1)" class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 transition text-lg font-medium text-slate-700">−</button>
                            <span class="w-12 text-center text-sm font-semibold text-slate-900">{{ quantity }}</span>
                            <button @click="quantity = Math.min(currentStock, quantity + 1)" class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 transition text-lg font-medium text-slate-700">+</button>
                        </div>
                        <p class="text-xs text-slate-400">Max {{ currentStock }} pièces</p>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row gap-3 mb-6">
                        <button
                            @click="addToCart"
                            :disabled="currentStock === 0 || form.processing || (product.has_variants && !selectedVariant)"
                            class="flex-1 flex items-center justify-center gap-2 py-3 px-6 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ form.processing ? 'Ajout...' : 'Ajouter au panier' }}
                        </button>

                        <a
                            v-if="whatsapp_number && currentStock > 0 && $page.props.settings?.whatsapp_order_enabled !== '0'"
                            :href="`https://wa.me/${whatsapp_number}?text=${waMessage}`"
                            target="_blank"
                            rel="noopener"
                            class="flex-1 flex items-center justify-center gap-2 py-3 px-6 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Commander sur WhatsApp
                        </a>
                    </div>

                    <!-- Méta infos -->
                    <div class="border-t border-slate-100 pt-4 space-y-1.5 text-xs text-slate-500">
                        <p>SKU : <span class="text-slate-700 font-medium">{{ selectedVariant?.sku || product.sku }}</span></p>
                        <p v-if="product.category">Catégorie : <Link :href="`/categorie/${product.category.slug}`" class="text-primary-600 hover:underline">{{ product.category.name }}</Link></p>
                        <p v-if="product.weight">Poids : <span class="text-slate-700">{{ product.weight }} g</span></p>
                    </div>
                </div>
            </div>

            <!-- ─── Tabs Description / Infos / Avis ────────────────── -->
            <div class="mb-14">
                <!-- Tab headers -->
                <div class="flex border-b border-slate-200 mb-6">
                    <button
                        v-for="tab in [{key:'description', label:'Description'}, {key:'info', label:'Informations'}, {key:'reviews', label:`Avis (${product.review_count})`}]"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="px-5 py-3 text-sm font-medium border-b-2 -mb-px transition"
                        :class="activeTab === tab.key
                            ? 'border-primary-600 text-primary-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Description -->
                <div v-if="activeTab === 'description'" class="prose prose-sm max-w-none text-slate-700" v-html="product.description || '<p>Aucune description disponible.</p>'"></div>

                <!-- Infos -->
                <div v-else-if="activeTab === 'info'">
                    <table class="text-sm w-full max-w-lg">
                        <tbody class="divide-y divide-slate-100">
                            <tr><td class="py-2.5 pr-6 text-slate-500 w-36">SKU</td><td class="py-2.5 font-medium text-slate-900">{{ product.sku }}</td></tr>
                            <tr v-if="product.category"><td class="py-2.5 text-slate-500">Catégorie</td><td class="py-2.5 font-medium text-slate-900">{{ product.category.name }}</td></tr>
                            <tr v-if="product.weight"><td class="py-2.5 text-slate-500">Poids</td><td class="py-2.5 font-medium text-slate-900">{{ product.weight }} g</td></tr>
                            <tr><td class="py-2.5 text-slate-500">Disponibilité</td><td class="py-2.5 font-medium" :class="currentStock > 0 ? 'text-green-600' : 'text-red-600'">{{ currentStock > 0 ? 'En stock' : 'Rupture' }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Avis -->
                <div v-else-if="activeTab === 'reviews'">
                    <!-- Note globale -->
                    <div v-if="product.review_count > 0" class="flex items-center gap-6 mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200 max-w-sm">
                        <div class="text-center">
                            <p class="text-5xl font-bold text-slate-900">{{ product.review_avg }}</p>
                            <div class="flex gap-0.5 justify-center mt-1">
                                <svg v-for="(full, i) in stars(product.review_avg)" :key="i" class="w-4 h-4" :class="full ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">{{ product.review_count }} avis</p>
                        </div>
                    </div>

                    <!-- Liste avis -->
                    <div v-if="product.reviews.length" class="space-y-5">
                        <div v-for="review in product.reviews" :key="review.id" class="border-b border-slate-100 pb-5">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-xs font-semibold text-slate-600">
                                        {{ review.author[0] }}
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">{{ review.author }}</p>
                                </div>
                                <div class="flex gap-0.5">
                                    <svg v-for="(full, i) in stars(review.rating)" :key="i" class="w-3.5 h-3.5" :class="full ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ review.body }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ review.created_at }}</p>
                        </div>
                    </div>
                    <div v-else class="text-sm text-slate-500 py-4">Aucun avis pour ce produit.</div>
                </div>
            </div>

            <!-- ─── Produits liés ───────────────────────────────────── -->
            <div v-if="related_products?.length">
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <p class="text-xs font-semibold text-primary-600 uppercase tracking-widest mb-1">Suggestions</p>
                        <h2 class="text-xl font-bold text-slate-900">Vous aimerez aussi</h2>
                    </div>
                    <Link v-if="product.category" :href="`/categorie/${product.category.slug}`" class="text-sm text-slate-500 hover:text-slate-900 transition">Voir tout →</Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <ProductCard v-for="p in related_products" :key="p.id" :product="p" />
                </div>
            </div>

            <!-- ─── Réassurance bas de page ─────────────────────────── -->
            <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div v-for="item in [
                    {icon:'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', title:'Livraison rapide', desc:'Partout en Côte d\'Ivoire'},
                    {icon:'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', title:'Paiement sécurisé', desc:'Plusieurs méthodes acceptées'},
                    {icon:'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', title:'Support 7j/7', desc:'Réponse rapide via WhatsApp'}
                ]" :key="item.title"
                    class="flex items-center gap-4 bg-slate-50 rounded-xl border border-slate-200 px-5 py-4"
                >
                    <div class="w-10 h-10 bg-white rounded-lg border border-slate-200 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ item.title }}</p>
                        <p class="text-xs text-slate-500">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
