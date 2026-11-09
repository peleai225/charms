<script setup>
import { Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { useCartStore } from '@/Stores/cart';
import { useNotificationStore } from '@/Stores/notifications';
import { ref, computed, onMounted } from 'vue';
import { Heart, ShoppingCart, Star, Check, Image } from 'lucide-vue-next'

const props = defineProps({
    product: Object,
});

const { formatPrice } = useHelpers();
const cartStore = useCartStore();
const notifStore = useNotificationStore();

const addingToCart = ref(false);
const wishlistLoading = ref(false);
const inWishlist = ref(props.product.in_wishlist ?? false);

const inCart      = computed(() => cartStore.hasProduct(props.product.id));
const isInStock   = computed(() => props.product.stock > 0 || props.product.allow_backorder);

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

// Rupture > offre par lot > réduction > nouveauté : une étiquette, jamais une pile.
// Couleurs du thème (back-office) : accent pour les promotions, principale pour la nouveauté.
const badge = computed(() => {
    if (!isInStock.value) return { text: 'Rupture', class: 'bg-slate-700 text-white' };
    if (props.product.promotion_label) return { text: props.product.promotion_label, class: 'bg-accent-50 text-accent-800 border border-accent-200' };
    if (discount.value) return { text: `-${discount.value} %`, class: 'bg-accent-600 text-accent-contrast' };
    if (props.product.is_new) return { text: 'Nouveau', class: 'bg-primary-50 text-primary-800 border border-primary-200' };
    return null;
});

// Deuxième photo montrée au survol, s'il y en a une
const secondaryImage = computed(() => props.product.secondary_image || props.product.images?.[1] || null);

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
            window.trackPixel?.addToCart({ id: props.product.id, name: props.product.name, price: props.product.price }, 1);
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
    <div class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition-[border-color,box-shadow] duration-200 hover:border-slate-300 hover:shadow-md">

        <!-- Photo, étiquette, favoris et ajout au panier (boutons hors du lien : pas d'éléments interactifs imbriqués) -->
        <!-- Cadre carré fixe : une photo en portrait ne doit pas allonger la carte -->
        <div class="relative aspect-square overflow-hidden bg-slate-50">
            <Link :href="`/produit/${product.slug}`" tabindex="-1" class="absolute inset-0 block">
                <img
                    v-if="product.primary_image"
                    :src="`/storage/${product.primary_image}`"
                    :alt="product.name"
                    class="h-full w-full object-cover"
                    :class="{ 'transition-opacity duration-200 group-hover:opacity-0': secondaryImage }"
                    loading="lazy"
                />
                <img
                    v-if="product.primary_image && secondaryImage"
                    :src="`/storage/${secondaryImage}`"
                    :alt="`${product.name} survol`"
                    class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                    loading="lazy"
                />
                <span v-if="!product.primary_image" class="flex h-full w-full items-center justify-center">
                    <Image class="h-14 w-14 text-slate-200" aria-hidden="true" />
                </span>
            </Link>

            <!-- Une seule étiquette, la plus utile (à gauche : le cœur est à droite) -->
            <span v-if="badge"
                  class="pointer-events-none absolute left-2 top-2 max-w-[calc(100%-3.75rem)] truncate rounded-md px-2 py-0.5 text-xs font-semibold"
                  :class="badge.class">
                {{ badge.text }}
            </span>

            <!-- Favoris : zone tactile de 44 px, pastille de 36 px -->
            <button
                type="button"
                @click.prevent="toggleWishlist"
                :disabled="wishlistLoading"
                :aria-pressed="inWishlist"
                :aria-label="inWishlist ? `Retirer ${product.name} des favoris` : `Ajouter ${product.name} aux favoris`"
                :title="inWishlist ? 'Retirer des favoris' : 'Ajouter aux favoris'"
                class="absolute right-1 top-1 flex h-11 w-11 items-center justify-center rounded-full disabled:opacity-60
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
            >
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                    <Heart class="h-4 w-4"
                        :class="inWishlist ? 'text-primary-600' : 'text-slate-500'"
                        :fill="inWishlist ? 'currentColor' : 'none'" />
                </span>
            </button>

            <!-- Ajout au panier (produit simple en stock) -->
            <button
                v-if="isInStock && !product.has_variants"
                type="button"
                @click.prevent="addToCart"
                :disabled="addingToCart"
                :aria-label="inCart ? `${product.name} est déjà dans le panier : en ajouter un` : `Ajouter ${product.name} au panier`"
                :title="inCart ? 'Déjà dans le panier' : 'Ajouter au panier'"
                class="absolute bottom-2 right-2 flex h-11 w-11 items-center justify-center rounded-full bg-primary-600 text-primary-contrast shadow-sm
                       transition-colors hover:bg-primary-700 disabled:opacity-60
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
            >
                <svg v-if="addingToCart" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <Check v-else-if="inCart" class="h-5 w-5" aria-hidden="true" />
                <ShoppingCart v-else class="h-5 w-5" aria-hidden="true" />
            </button>

            <!-- Produit à déclinaisons : choisir taille, couleur… sur sa page -->
            <Link
                v-else-if="isInStock && product.has_variants"
                :href="`/produit/${product.slug}`"
                :aria-label="`Choisir les options de ${product.name}`"
                class="absolute bottom-2 right-2 inline-flex h-11 items-center rounded-full border border-slate-200 bg-white px-4 text-sm font-semibold text-primary-700 shadow-sm
                       transition-colors hover:border-primary-300 hover:bg-primary-50
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
            >
                Choisir
            </Link>
        </div>

        <!-- Infos produit -->
        <div class="flex flex-1 flex-col px-3 pb-3 pt-2.5">

            <!-- Catégorie + étoiles -->
            <div v-if="product.category_name || product.rating" class="mb-1 flex items-center justify-between gap-2">
                <span v-if="product.category_name" class="truncate text-[11px] font-medium uppercase tracking-wide text-slate-400">
                    {{ product.category_name }}
                </span>
                <span v-else class="flex-1"></span>

                <!-- Étoiles -->
                <div v-if="product.rating" class="flex shrink-0 items-center gap-0.5">
                    <!-- Étoiles pleines -->
                    <template v-for="i in stars.full" :key="'f'+i">
                        <Star class="h-3 w-3 text-accent-500" fill="currentColor" />
                    </template>
                    <!-- Demi-étoile -->
                    <svg v-if="stars.half" class="h-3 w-3 text-accent-500" viewBox="0 0 20 20">
                        <defs><linearGradient :id="halfId"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#d1d5db"/></linearGradient></defs>
                        <path :fill="`url(#${halfId})`" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <!-- Étoiles vides -->
                    <template v-for="i in stars.empty" :key="'e'+i">
                        <Star class="h-3 w-3 text-slate-200" fill="currentColor" />
                    </template>
                    <span class="ml-1 text-[11px] font-medium text-slate-400">{{ product.rating }}</span>
                </div>
            </div>

            <!-- Nom produit (2 lignes, hauteur réservée pour aligner les prix d'une rangée) -->
            <Link :href="`/produit/${product.slug}`"
                  class="mb-1.5 line-clamp-2 min-h-10 text-sm font-medium leading-5 text-slate-800 hover:text-primary-700">
                {{ product.name }}
            </Link>

            <!-- Prix : prix barré à côté, ou dessous si la carte est étroite -->
            <div class="mt-auto flex flex-wrap items-baseline gap-x-2">
                <span class="whitespace-nowrap text-base font-bold" :class="discount ? 'text-primary-700' : 'text-slate-900'">
                    {{ formatPrice(product.price) }}
                </span>
                <span v-if="discount" class="whitespace-nowrap text-xs text-slate-400 line-through">
                    {{ formatPrice(product.compare_price) }}
                </span>
            </div>
        </div>
    </div>
</template>
