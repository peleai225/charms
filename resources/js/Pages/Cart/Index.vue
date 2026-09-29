<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { useCartStore } from '@/Stores/cart';
import { ref, reactive, computed } from 'vue';
import { Package, ShieldCheck, RotateCcw, ChevronRight, ChevronLeft, ShoppingCart, Tag, Eye, Trash2, X, Lock, Image } from 'lucide-vue-next'

const props = defineProps({
    cart: Object,
    nudges: {
        type: Array,
        default: () => [],
    },
});

const { formatPrice } = useHelpers();
const cartStore = useCartStore();

// ─── État local réactif ────────────────────────────────────────────────────────
// On copie les items dans un state local pour mise à jour instantanée
const items = reactive(props.cart?.items?.map(i => ({ ...i })) ?? []);
const subtotal = ref(props.cart?.subtotal ?? 0);
const discount = ref(props.cart?.discount ?? 0);
const shippingCost = ref(props.cart?.shipping_cost ?? 0);
const total = ref(props.cart?.total ?? 0);
const couponCode = ref(props.cart?.coupon_code ?? '');

const isEmpty = computed(() => items.length === 0);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

const jsonHeaders = () => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': csrf(),
});

// ─── Mise à jour quantité — 0 rechargement ────────────────────────────────────
const updatingItem = ref(null);

const updateQuantity = async (item, newQty) => {
    if (newQty < 1) return removeItem(item);
    updatingItem.value = item.id;
    const prev = item.quantity;
    item.quantity = newQty;
    item.total = item.unit_price * newQty;

    try {
        const res = await fetch(`/panier/${item.id}`, {
            method: 'PATCH',
            headers: jsonHeaders(),
            body: JSON.stringify({ quantity: newQty }),
        });
        if (res.ok) {
            const data = await res.json();
            subtotal.value = data.subtotal;
            total.value    = data.total;
            cartStore.setCount(data.cart_count);
        } else {
            // Rollback
            item.quantity = prev;
            item.total = item.unit_price * prev;
        }
    } catch {
        item.quantity = prev;
        item.total = item.unit_price * prev;
    } finally {
        updatingItem.value = null;
    }
};

// ─── Suppression — 0 rechargement ─────────────────────────────────────────────
const removingItem = ref(null);

const removeItem = async (item) => {
    removingItem.value = item.id;
    try {
        const res = await fetch(`/panier/${item.id}`, {
            method: 'DELETE',
            headers: jsonHeaders(),
        });
        if (res.ok) {
            const data = await res.json();
            const idx = items.findIndex(i => i.id === item.id);
            if (idx !== -1) items.splice(idx, 1);
            subtotal.value = data.subtotal;
            total.value    = data.total;
            cartStore.setCount(data.cart_count);
        }
    } finally {
        removingItem.value = null;
    }
};

// ─── Coupon ────────────────────────────────────────────────────────────────────
const couponInput  = ref('');
const couponError  = ref('');
const couponLoading = ref(false);

const applyCoupon = async () => {
    if (!couponInput.value.trim()) return;
    couponLoading.value = true;
    couponError.value   = '';
    try {
        const res = await fetch('/panier/coupon', {
            method: 'POST',
            headers: jsonHeaders(),
            body: JSON.stringify({ coupon_code: couponInput.value }),
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            couponError.value = data.error || data.message || 'Code invalide.';
        } else {
            couponCode.value = data.coupon_code;
            discount.value   = data.discount_amount;
            subtotal.value   = data.subtotal;
            total.value      = data.total;
            couponInput.value = '';
        }
    } catch {
        couponError.value = 'Erreur réseau.';
    } finally {
        couponLoading.value = false;
    }
};

const removeCoupon = () => {
    router.delete('/panier/coupon', {
        preserveScroll: true,
        onSuccess: () => { couponCode.value = ''; discount.value = 0; },
    });
};

// ─── Réassurance ─────────────────────────────────────────────────────────────
const reassuranceItems = [
    { icon: Package, text: 'Livraison rapide' },
    { icon: ShieldCheck, text: 'Paiement sécurisé' },
    { icon: RotateCcw, text: 'Retours faciles' },
]

// ─── WhatsApp ──────────────────────────────────────────────────────────────────
const waCartMessage = computed(() => {
    if (!items.length) return '#';
    const lines = items.map(i => `• ${i.product.name} x${i.quantity} — ${formatPrice(i.total)}`).join('\n');
    const msg = `Bonjour, je souhaite commander :\n${lines}\n\nTotal : ${formatPrice(total.value)}`;
    return `https://wa.me/?text=${encodeURIComponent(msg)}`;
});
</script>

<template>
    <FrontLayout title="Mon panier">
        <Head title="Mon panier" />

        <!-- Réassurance band -->
        <div class="bg-slate-900 text-white">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-3 divide-x divide-slate-700 py-3">
                    <div v-for="item in reassuranceItems" :key="item.text"
                        class="flex items-center justify-center gap-2 py-1 text-xs text-slate-300"
                    >
                        <component :is="item.icon" class="w-4 h-4 text-slate-400 shrink-0" />
                        <span class="hidden sm:block">{{ item.text }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 min-h-screen py-8">
            <div class="container mx-auto px-4 max-w-5xl">

                <!-- Titre + étapes -->
                <div class="flex items-center justify-between mb-7">
                    <h1 class="text-2xl font-bold text-slate-900">Mon panier</h1>
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
                        <span class="font-semibold text-slate-900">1. Panier</span>
                        <ChevronRight class="w-3 h-3" />
                        <span>2. Livraison</span>
                        <ChevronRight class="w-3 h-3" />
                        <span>3. Confirmation</span>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="isEmpty" class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-5">
                        <ShoppingCart class="w-10 h-10 text-slate-300" />
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-2">Votre panier est vide</h2>
                    <p class="text-slate-500 mb-7">Découvrez notre catalogue et ajoutez vos coups de cœur.</p>
                    <Link href="/boutique" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                        Continuer mes achats
                    </Link>
                </div>

                <!-- Panier non vide -->
                <template v-else>

                <!-- Nudges bulk pricing -->
                <div v-if="nudges.length" class="space-y-2 mb-4">
                    <div
                        v-for="nudge in nudges"
                        :key="nudge.product_id"
                        class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 sm:px-5 sm:py-4"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div class="shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mt-0.5">
                                <Tag class="w-4 h-4 text-blue-600" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Text -->
                                <p class="text-sm text-blue-900">
                                    Ajoutez
                                    <span class="font-bold">{{ nudge.items_needed }} {{ nudge.product_name }}</span>
                                    de plus pour économiser
                                    <span class="font-bold">{{ formatPrice(nudge.total_saving) }}</span> !
                                </p>

                                <!-- Progress bar -->
                                <div class="mt-2 flex items-center gap-2.5">
                                    <div class="flex-1 h-2 bg-blue-100 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-blue-500 rounded-full transition-all duration-300"
                                            :style="{ width: Math.round((nudge.current_qty / nudge.next_tier_qty) * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-xs font-semibold text-blue-700 tabular-nums shrink-0">
                                        {{ nudge.current_qty }}/{{ nudge.next_tier_qty }}
                                    </span>
                                </div>

                                <!-- CTA -->
                                <div class="mt-2.5">
                                    <Link
                                        :href="nudge.shop_url"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition min-h-[44px] sm:min-h-0 sm:py-1.5"
                                    >
                                        <Eye class="w-3.5 h-3.5" />
                                        Voir {{ nudge.category_name || 'les produits' }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">

                    <!-- ─── Articles ─────────────────────────────── -->
                    <div class="lg:col-span-2 space-y-3">
                        <TransitionGroup name="cart-item">
                            <div
                                v-for="item in items"
                                :key="item.id"
                                class="bg-white rounded-xl border border-slate-200 p-4 flex gap-4 transition-opacity duration-200"
                                :class="updatingItem === item.id || removingItem === item.id ? 'opacity-50' : 'opacity-100'"
                            >
                                <!-- Image -->
                                <Link :href="`/produit/${item.product.slug}`" class="w-20 h-20 sm:w-24 sm:h-24 bg-slate-100 rounded-xl overflow-hidden shrink-0 block">
                                    <img
                                        v-if="item.product.primary_image"
                                        :src="`/storage/${item.product.primary_image}`"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-200"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                        <Image class="w-8 h-8" />
                                    </div>
                                </Link>

                                <!-- Infos -->
                                <div class="flex-1 min-w-0">
                                    <Link :href="`/produit/${item.product.slug}`" class="font-semibold text-slate-900 hover:text-primary-600 transition text-sm leading-tight line-clamp-2">
                                        {{ item.product.name }}
                                    </Link>
                                    <div v-if="item.variant?.attributes?.length" class="flex flex-wrap gap-1 mt-1">
                                        <span v-for="(attr, i) in item.variant.attributes" :key="i" class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                            {{ attr.name }} : {{ attr.value }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">{{ formatPrice(item.unit_price) }} / unité</p>

                                    <!-- Qté + supprimer -->
                                    <div class="flex items-center gap-3 mt-3">
                                        <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                                            <button
                                                @click="updateQuantity(item, item.quantity - 1)"
                                                :disabled="updatingItem === item.id"
                                                class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 active:bg-slate-200 transition text-lg font-bold text-slate-700 disabled:opacity-40"
                                            >−</button>
                                            <span class="w-10 text-center text-sm font-semibold text-slate-900 tabular-nums">{{ item.quantity }}</span>
                                            <button
                                                @click="updateQuantity(item, item.quantity + 1)"
                                                :disabled="updatingItem === item.id"
                                                class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 active:bg-slate-200 transition text-lg font-bold text-slate-700 disabled:opacity-40"
                                            >+</button>
                                        </div>
                                        <button
                                            @click="removeItem(item)"
                                            :disabled="removingItem === item.id"
                                            class="text-xs text-red-500 hover:text-red-700 transition flex items-center gap-1 py-2 px-1 disabled:opacity-40"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                            Retirer
                                        </button>
                                    </div>
                                </div>

                                <!-- Prix total ligne -->
                                <div class="shrink-0 text-right">
                                    <p class="text-base font-bold text-slate-900 tabular-nums transition-all duration-200">{{ formatPrice(item.total) }}</p>
                                </div>
                            </div>
                        </TransitionGroup>

                        <!-- Continuer achats -->
                        <Link href="/boutique" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 transition mt-2">
                            <ChevronLeft class="w-4 h-4" />
                            Continuer mes achats
                        </Link>
                    </div>

                    <!-- ─── Récapitulatif ─────────────────────────── -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl border border-slate-200 p-5 sticky top-24">
                            <h2 class="text-base font-bold text-slate-900 mb-5">Récapitulatif</h2>

                            <!-- Lignes -->
                            <div class="space-y-3 pb-4 border-b border-slate-100">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Sous-total</span>
                                    <span class="font-medium text-slate-900 tabular-nums transition-all duration-200">{{ formatPrice(subtotal) }}</span>
                                </div>
                                <div v-if="discount > 0" class="flex justify-between text-sm">
                                    <span class="text-green-700">Réduction</span>
                                    <span class="font-semibold text-green-700 tabular-nums">−{{ formatPrice(discount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Livraison</span>
                                    <span class="font-medium text-slate-500 italic">{{ shippingCost > 0 ? formatPrice(shippingCost) : 'Calculée au paiement' }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between py-4">
                                <span class="font-bold text-slate-900">Total</span>
                                <span class="text-xl font-bold text-slate-900 tabular-nums transition-all duration-200">{{ formatPrice(total) }}</span>
                            </div>

                            <!-- Coupon -->
                            <div class="mb-5">
                                <div v-if="couponCode" class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-sm">
                                    <span class="text-green-800 font-medium">Code : {{ couponCode }}</span>
                                    <button @click="removeCoupon" class="text-green-600 hover:text-red-600 transition">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <div v-else>
                                    <p class="text-xs font-medium text-slate-500 mb-2">Code promo</p>
                                    <div class="flex gap-2">
                                        <input
                                            v-model="couponInput"
                                            type="text"
                                            placeholder="CHAMSE10"
                                            class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 uppercase tracking-wider"
                                            @keyup.enter="applyCoupon"
                                        />
                                        <button
                                            @click="applyCoupon"
                                            :disabled="couponLoading"
                                            class="px-3 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-900 transition disabled:opacity-50"
                                        >
                                            {{ couponLoading ? '...' : 'OK' }}
                                        </button>
                                    </div>
                                    <p v-if="couponError" class="text-xs text-red-600 mt-1">{{ couponError }}</p>
                                </div>
                            </div>

                            <!-- CTA principal -->
                            <Link
                                href="/commander"
                                class="block w-full text-center py-3.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition mb-3"
                            >
                                Passer commande →
                            </Link>

                            <!-- CTA WhatsApp -->
                            <a
                                v-if="$page.props.settings?.social_whatsapp && $page.props.settings?.whatsapp_order_enabled !== '0'"
                                :href="waCartMessage"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center justify-center gap-2 w-full text-center py-3 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Commander sur WhatsApp
                            </a>

                            <!-- Sécurité -->
                            <p class="text-xs text-center text-slate-400 mt-4 flex items-center justify-center gap-1">
                                <Lock class="w-3.5 h-3.5" />
                                Paiement 100% sécurisé
                            </p>
                        </div>
                    </div>
                </div>

                </template>
            </div>
        </div>
    </FrontLayout>
</template>

<style scoped>
.cart-item-enter-active,
.cart-item-leave-active {
    transition: all 0.25s ease;
}
.cart-item-enter-from {
    opacity: 0;
    transform: translateX(-12px);
}
.cart-item-leave-to {
    opacity: 0;
    transform: translateX(12px);
    max-height: 0;
}
</style>
