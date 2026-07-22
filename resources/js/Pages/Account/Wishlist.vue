<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { useCartStore } from '@/Stores/cart';
import { useNotificationStore } from '@/Stores/notifications';
import { ref, reactive, computed } from 'vue';

const props = defineProps({
    wishlist: Object,
});

const { formatPrice } = useHelpers();
const cartStore = useCartStore();
const notifStore = useNotificationStore();

// État local réactif pour suppression instantanée
const items = reactive(props.wishlist?.data?.map(p => ({ ...p })) ?? []);
const totalCount = ref(props.wishlist?.total ?? 0);

const removing = ref(null);
const addingToCart = ref(null);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

// Retirer des favoris — instantané, sans rechargement
const removeFromWishlist = async (productId) => {
    removing.value = productId;
    try {
        const res = await fetch(`/favoris/${productId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'Accept': 'application/json',
            },
        });
        if (res.ok) {
            const idx = items.findIndex(p => p.id === productId);
            if (idx !== -1) {
                items.splice(idx, 1);
                totalCount.value = Math.max(0, totalCount.value - 1);
            }
            notifStore.add({ type: 'success', message: 'Retiré de vos favoris' });
        }
    } finally {
        removing.value = null;
    }
};

// Ajouter au panier — instantané
const addToCart = async (product) => {
    if (addingToCart.value === product.id) return;
    addingToCart.value = product.id;
    try {
        const res = await fetch('/panier/ajouter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: product.id, quantity: 1 }),
        });
        if (res.status === 401) { window.location.href = '/connexion'; return; }
        if (res.ok) {
            cartStore.addProductId(product.id, null);
            notifStore.add({ type: 'success', message: `${product.name} ajouté au panier` });
        }
    } finally {
        addingToCart.value = null;
    }
};

const inCart = (productId) => cartStore.hasProduct(productId);

const pageNumbers = computed(() => {
    const current = props.wishlist?.current_page || 1;
    const last    = props.wishlist?.last_page    || 1;
    const pages   = [];
    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
});

const goToPage = (p) => {
    if (p === '...') return;
    router.get('/mon-compte/favoris', { page: p }, { preserveScroll: true });
};
</script>

<template>
    <AccountLayout title="Mes favoris">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Mes favoris</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ totalCount }} produit{{ totalCount > 1 ? 's' : '' }} sauvegardé{{ totalCount > 1 ? 's' : '' }}
                </p>
            </div>
            <Link href="/boutique" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 transition border border-slate-200 rounded-lg px-3 py-1.5 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Boutique
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="!items.length" class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-2">Aucun favori pour l'instant</h2>
            <p class="text-sm text-slate-500 mb-6 max-w-xs mx-auto">Parcourez la boutique et cliquez sur le ❤ pour sauvegarder vos coups de cœur.</p>
            <Link href="/boutique" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Découvrir les produits
            </Link>
        </div>

        <!-- Grille favoris -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <TransitionGroup name="wishlist-item">
                <div
                    v-for="product in items"
                    :key="product.id"
                    class="bg-white rounded-xl border border-slate-200 overflow-hidden group transition-all duration-200"
                    :class="removing === product.id ? 'opacity-40 scale-95' : 'hover:border-slate-300 hover:shadow-md'"
                >
                    <!-- Image -->
                    <Link :href="`/produit/${product.slug}`" class="block relative overflow-hidden bg-slate-100" style="aspect-ratio:1/1">
                        <img
                            v-if="product.primary_image"
                            :src="`/storage/${product.primary_image}`"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-200">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>

                        <!-- Badge promo -->
                        <span v-if="product.compare_price" class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            -{{ Math.round((1 - product.price / product.compare_price) * 100) }}%
                        </span>

                        <!-- Bouton retirer (toujours visible sur mobile, hover desktop) -->
                        <button
                            @click.prevent="removeFromWishlist(product.id)"
                            :disabled="removing === product.id"
                            class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full flex items-center justify-center shadow transition
                                   sm:opacity-0 sm:group-hover:opacity-100 opacity-100
                                   hover:bg-red-50 disabled:opacity-40"
                            title="Retirer des favoris"
                        >
                            <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>

                        <!-- Overlay rupture -->
                        <div v-if="product.stock === 0" class="absolute inset-0 bg-white/60 flex items-center justify-center">
                            <span class="text-xs font-semibold text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200">Rupture</span>
                        </div>
                    </Link>

                    <!-- Infos -->
                    <div class="p-3">
                        <p v-if="product.category_name" class="text-[10px] text-slate-400 mb-0.5 uppercase tracking-wide">{{ product.category_name }}</p>
                        <Link :href="`/produit/${product.slug}`" class="text-xs font-semibold text-slate-900 hover:text-blue-600 transition line-clamp-2 leading-snug block">
                            {{ product.name }}
                        </Link>

                        <div class="flex items-baseline gap-2 mt-1.5 mb-3">
                            <span class="text-sm font-bold text-slate-900">{{ formatPrice(product.price) }}</span>
                            <span v-if="product.compare_price" class="text-xs text-slate-400 line-through">{{ formatPrice(product.compare_price) }}</span>
                        </div>

                        <!-- CTA -->
                        <button
                            v-if="product.stock > 0 && !product.has_variants && !inCart(product.id)"
                            @click="addToCart(product)"
                            :disabled="addingToCart === product.id"
                            class="w-full py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-800 transition flex items-center justify-center gap-1.5 disabled:opacity-60"
                        >
                            <svg v-if="addingToCart !== product.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <svg v-else class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ addingToCart === product.id ? '...' : 'Ajouter au panier' }}
                        </button>

                        <!-- Déjà dans le panier -->
                        <div
                            v-else-if="product.stock > 0 && !product.has_variants && inCart(product.id)"
                            class="w-full py-2 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Dans le panier
                        </div>

                        <Link
                            v-else-if="product.stock > 0 && product.has_variants"
                            :href="`/produit/${product.slug}`"
                            class="block w-full py-2 text-center bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition"
                        >
                            Choisir les options →
                        </Link>

                        <span v-else class="block w-full py-2 text-center bg-slate-100 text-slate-400 text-xs rounded-lg">
                            Rupture de stock
                        </span>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Pagination -->
        <div v-if="wishlist?.last_page > 1" class="mt-6 flex items-center justify-center gap-1.5">
            <button :disabled="wishlist.current_page === 1" @click="goToPage(wishlist.current_page - 1)"
                class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <template v-for="(p, i) in pageNumbers" :key="i">
                <span v-if="p === '...'" class="text-slate-400 text-sm px-1">…</span>
                <button v-else @click="goToPage(p)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition"
                    :class="p === wishlist.current_page ? 'bg-slate-900 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'">
                    {{ p }}
                </button>
            </template>
            <button :disabled="wishlist.current_page === wishlist.last_page" @click="goToPage(wishlist.current_page + 1)"
                class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </AccountLayout>
</template>

<style scoped>
.wishlist-item-enter-active,
.wishlist-item-leave-active {
    transition: all 0.25s ease;
}
.wishlist-item-enter-from {
    opacity: 0;
    transform: scale(0.95);
}
.wishlist-item-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
