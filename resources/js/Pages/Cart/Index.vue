<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useCartStore } from '@/Stores/cart';

const props = defineProps({
    cart: Object,
});

const { formatPrice } = useHelpers();
const cartStore = useCartStore();

const updateQuantity = (itemId, quantity) => {
    router.post(`/panier/mettre-a-jour/${itemId}`, {
        quantity: Math.max(1, quantity),
    }, {
        preserveScroll: true,
        onSuccess: () => cartStore.sync(),
    });
};

const removeItem = (itemId) => {
    router.delete(`/panier/retirer/${itemId}`, {
        preserveScroll: true,
        onSuccess: () => cartStore.sync(),
    });
};
</script>

<template>
    <FrontLayout title="Mon panier">
        <Head title="Mon panier" />

        <div class="bg-slate-50 min-h-screen py-8">
            <div class="container mx-auto px-4">
                <h1 class="text-2xl font-bold text-slate-900 mb-6">Mon panier</h1>

                <!-- Empty state -->
                <div v-if="!cart || cart.items.length === 0" class="text-center py-16">
                    <svg class="w-20 h-20 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-slate-900 mb-2">Votre panier est vide</h2>
                    <p class="text-slate-500 mb-6">Découvrez notre catalogue et ajoutez des produits</p>
                    <Link href="/boutique">
                        <Button variant="primary" size="lg">
                            Continuer mes achats
                        </Button>
                    </Link>
                </div>

                <!-- Cart items -->
                <div v-else class="grid lg:grid-cols-3 gap-6">
                    <!-- Items list -->
                    <div class="lg:col-span-2 space-y-4">
                        <Card
                            v-for="item in cart.items"
                            :key="item.id"
                            padding="default"
                        >
                            <div class="flex gap-4">
                                <!-- Image -->
                                <div class="w-24 h-24 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                    <img
                                        v-if="item.product.primary_image"
                                        :src="`/storage/${item.product.primary_image}`"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <Link :href="`/produit/${item.product.slug}`" class="font-semibold text-slate-900 hover:text-primary-600 transition">
                                        {{ item.product.name }}
                                    </Link>

                                    <!-- Variant info -->
                                    <div v-if="item.variant" class="text-sm text-slate-500 mt-1">
                                        <span v-for="(attr, index) in item.variant.attributes" :key="index">
                                            {{ attr.name }}: {{ attr.value }}
                                            <span v-if="index < item.variant.attributes.length - 1"> • </span>
                                        </span>
                                    </div>

                                    <!-- Quantity controls -->
                                    <div class="flex items-center gap-3 mt-3">
                                        <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                                            <button
                                                @click="updateQuantity(item.id, item.quantity - 1)"
                                                class="px-3 py-1 bg-slate-50 hover:bg-slate-100 transition text-sm"
                                            >
                                                -
                                            </button>
                                            <span class="px-4 py-1 text-sm font-medium">{{ item.quantity }}</span>
                                            <button
                                                @click="updateQuantity(item.id, item.quantity + 1)"
                                                class="px-3 py-1 bg-slate-50 hover:bg-slate-100 transition text-sm"
                                            >
                                                +
                                            </button>
                                        </div>

                                        <button
                                            @click="removeItem(item.id)"
                                            class="text-sm text-red-600 hover:text-red-700 font-medium"
                                        >
                                            Supprimer
                                        </button>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="text-right shrink-0">
                                    <p class="text-lg font-bold text-slate-900">
                                        {{ formatPrice(item.total) }}
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        {{ formatPrice(item.unit_price) }} × {{ item.quantity }}
                                    </p>
                                </div>
                            </div>
                        </Card>
                    </div>

                    <!-- Summary -->
                    <div class="lg:col-span-1">
                        <Card padding="lg" shadow="lg" class="sticky top-4">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Récapitulatif</h2>

                            <div class="space-y-3 mb-4 pb-4 border-b border-slate-200">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600">Sous-total</span>
                                    <span class="font-medium text-slate-900">{{ formatPrice(cart.subtotal) }}</span>
                                </div>
                                <div v-if="cart.discount > 0" class="flex justify-between text-sm">
                                    <span class="text-slate-600">Réduction</span>
                                    <span class="font-medium text-success-600">-{{ formatPrice(cart.discount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600">Livraison</span>
                                    <span class="font-medium text-slate-900">
                                        {{ cart.shipping_cost > 0 ? formatPrice(cart.shipping_cost) : 'Calculé au paiement' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-between mb-6">
                                <span class="text-lg font-bold text-slate-900">Total</span>
                                <span class="text-2xl font-bold text-primary-600">{{ formatPrice(cart.total) }}</span>
                            </div>

                            <Link href="/commander">
                                <Button variant="primary" size="lg" class="w-full mb-3">
                                    Passer commande
                                </Button>
                            </Link>

                            <Link href="/boutique">
                                <Button variant="outline" size="md" class="w-full">
                                    Continuer mes achats
                                </Button>
                            </Link>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
