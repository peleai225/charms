<script setup>
import { Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { useCartStore } from '@/Stores/cart';
import { useNotificationStore } from '@/Stores/notifications';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    product: Object,
});

const { formatPrice } = useHelpers();
const cartStore = useCartStore();
const notifStore = useNotificationStore();

const addingToCart = ref(false);
const wishlistLoading = ref(false);
const inWishlist = ref(props.product.in_wishlist ?? false);

const inCart = computed(() => cartStore.hasProduct(props.product.id));

// Génération stable de l'ID pour le gradient de demi-étoile
const halfId = `half-${props.product.id}`;

onMounted(() => {
    if (cartStore.count > 0 && cartStore.productIds.size === 0) {
        cartStore.sync();
    }
});

const discount = computed(() => {
    const { price, compare_price } = props.product;
    if (!compare_price || compare_price <= price) return null;
    return Math.round((1 - price / compare_price) * 100);
});

const toggleWishlist = async (e) => {
    e.preventDefault();
    if (wishlistLoading.value) return;
    wishlistLoading.value = true;
    try {
        const res = await fetch(`/favoris/${props.product.id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        });
        if (res.status === 401) {
            window.location.href = '/connexion';
            return;
        }
        if (res.ok) {
            const data = await res.json();
            inWishlist.value = data.added;
            notifStore.add({
                type: data.added ? 'success' : 'info',
                message: data.added ? 'Ajouté aux favoris' : 'Retiré des favoris',
            });
        }
    } finally {
        wishlistLoading.value = false;
    }
};

const addToCart = async (e) => {
    e.preventDefault();
    if (addingToCart.value) return;
    if (props.product.has_variants) return; // redirige via le Link
    addingToCart.value = true;
    try {
        const res = await fetch('/panier/ajouter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: props.product.id, quantity: 1 }),
        });
        if (res.ok) {
            cartStore.addProductId(props.product.id, null);
            notifStore.add({ type: 'success', message: `${props.product.name} ajouté au panier` });
        }
    } finally {
        addingToCart.value = false;
    }
};

// Note étoiles (1-5, par demi-étoile)
const stars = computed(() => {
    const r = props.product.rating ?? 0;
    return { full: Math.floor(r), half: r % 1 >= 0.5, empty: 5 - Math.ceil(r) };
});
</script>

<template>
    <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 border border-transparent hover:border-slate-100">

        <!-- Zone image -->
        <Link :href="`/produit/${product.slug}`" class="block relative overflow-hidden bg-slate-50" style="aspect-ratio: 1/1">

            <!-- Image produit -->
            <img
                v-if="product.primary_image"
                :src="`/storage/${product.primary_image}`"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-14 h-14 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>

            <!-- Badge promo top-left -->
            <div class="absolute top-3 left-3 flex flex-col gap-1 z-10">
                <span v-if="discount" class="px-2.5 py-1 bg-emerald-800 text-white text-xs font-bold rounded-full shadow-sm">
                    {{ discount }}% off
                </span>
                <span v-if="product.is_new" class="px-2.5 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-sm">
                    Nouveau
                </span>
                <span v-if="product.stock === 0" class="px-2.5 py-1 bg-slate-500 text-white text-xs font-bold rounded-full shadow-sm">
                    Rupture
                </span>
            </div>

            <!-- 3 boutons d'action verticaux — toujours visibles sur mobile, slide sur desktop -->
            <div class="absolute top-3 right-3 flex flex-col gap-2 z-10
                        sm:translate-x-10 sm:opacity-0
                        sm:group-hover:translate-x-0 sm:group-hover:opacity-100
                        transition-all duration-300 ease-out">

                <!-- Favoris -->
                <button
                    @click.prevent="toggleWishlist"
                    :disabled="wishlistLoading"
                    class="w-9 h-9 bg-white rounded-full flex items-center justify-center shadow-md
                           hover:scale-110 transition-transform duration-150 disabled:opacity-60"
                    :title="inWishlist ? 'Retirer des favoris' : 'Ajouter aux favoris'"
                >
                    <svg class="w-4 h-4 transition-colors duration-150"
                        :class="inWishlist ? 'text-red-500 fill-current' : 'text-slate-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>

                <!-- Voir le produit (loupe) -->
                <Link
                    :href="`/produit/${product.slug}`"
                    class="w-9 h-9 bg-white rounded-full flex items-center justify-center shadow-md
                           hover:scale-110 transition-transform duration-150"
                    title="Voir le produit"
                    @click.stop
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </Link>

                <!-- Panier -->
                <button
                    v-if="product.stock > 0 && !product.has_variants"
                    @click.prevent="addToCart"
                    :disabled="addingToCart"
                    class="w-9 h-9 rounded-full flex items-center justify-center shadow-md
                           hover:scale-110 transition-all duration-150 disabled:opacity-60"
                    :class="inCart ? 'bg-emerald-600' : 'bg-white'"
                    :title="inCart ? 'Déjà dans le panier' : 'Ajouter au panier'"
                >
                    <svg v-if="!addingToCart" class="w-4 h-4" :class="inCart ? 'text-white' : 'text-slate-500'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>

                <Link
                    v-else-if="product.has_variants"
                    :href="`/produit/${product.slug}`"
                    class="w-9 h-9 bg-white rounded-full flex items-center justify-center shadow-md
                           hover:scale-110 transition-transform duration-150"
                    title="Choisir les options"
                    @click.stop
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </Link>
            </div>

        </Link>

        <!-- Infos produit -->
        <div class="px-3.5 py-3">

            <!-- Catégorie + étoiles -->
            <div class="flex items-center justify-between mb-1.5">
                <span v-if="product.category_name" class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">
                    {{ product.category_name }}
                </span>
                <span v-else class="flex-1"></span>

                <!-- Étoiles -->
                <div v-if="product.rating" class="flex items-center gap-0.5">
                    <!-- Étoiles pleines -->
                    <template v-for="i in stars.full" :key="'f'+i">
                        <svg class="w-3 h-3 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </template>
                    <!-- Demi-étoile -->
                    <svg v-if="stars.half" class="w-3 h-3 text-amber-400" viewBox="0 0 20 20">
                        <defs><linearGradient :id="halfId"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#d1d5db"/></linearGradient></defs>
                        <path :fill="`url(#${halfId})`" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <!-- Étoiles vides -->
                    <template v-for="i in stars.empty" :key="'e'+i">
                        <svg class="w-3 h-3 text-slate-200 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </template>
                    <span class="text-[11px] text-slate-400 ml-1 font-medium">{{ product.rating }}</span>
                </div>
            </div>

            <!-- Nom produit -->
            <Link :href="`/produit/${product.slug}`" class="block text-sm font-semibold text-slate-800 hover:text-emerald-700 transition-colors line-clamp-2 leading-snug mb-2">
                {{ product.name }}
            </Link>

            <!-- Prix -->
            <div class="flex items-center gap-2">
                <span class="text-base font-bold" :class="discount ? 'text-emerald-700' : 'text-slate-900'">
                    {{ formatPrice(product.price) }}
                </span>
                <span v-if="product.compare_price" class="text-sm text-slate-400 line-through font-normal">
                    {{ formatPrice(product.compare_price) }}
                </span>

                <!-- Petit indicateur "dans le panier" discret -->
                <span v-if="inCart" class="ml-auto flex items-center gap-0.5 text-[10px] text-emerald-600 font-semibold">
                    <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Panier
                </span>
            </div>
        </div>
    </div>
</template>
