<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { onMounted } from 'vue';

const props = defineProps({
    order: Object,
});

const { formatPrice } = useHelpers();

onMounted(() => {
    window.trackPixel?.purchase(props.order.order_number, props.order.total);
});
</script>

<template>
    <FrontLayout title="Commande confirmée">
        <Head :title="`Commande ${order.order_number} confirmée`" />

        <div class="min-h-screen bg-slate-50 py-12">
            <div class="container mx-auto px-4 max-w-2xl">

                <!-- Header succès -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 mb-1">Commande confirmée !</h1>
                    <p class="text-slate-500">
                        Commande <span class="font-semibold text-slate-700">#{{ order.order_number }}</span>
                    </p>
                </div>

                <!-- Carte commande -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">

                    <!-- Statut -->
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-1">Statut</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                En cours de traitement
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-1">Total</p>
                            <p class="text-xl font-bold text-slate-900">{{ formatPrice(order.total) }}</p>
                        </div>
                    </div>

                    <!-- Articles -->
                    <div class="px-6 py-4 border-b border-slate-100">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-3">Articles</p>
                        <div class="space-y-3">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center gap-3"
                            >
                                <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                    <img
                                        v-if="item.product?.primary_image"
                                        :src="`/storage/${item.product.primary_image}`"
                                        :alt="item.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ item.name }}</p>
                                    <p class="text-xs text-slate-500" v-if="item.variant_name">{{ item.variant_name }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold text-slate-900">{{ formatPrice(item.unit_price * item.quantity) }}</p>
                                    <p class="text-xs text-slate-400">× {{ item.quantity }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Livraison -->
                    <div class="px-6 py-4">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-2">Livraison à</p>
                        <p class="text-sm text-slate-700">
                            {{ order.shipping_first_name }} {{ order.shipping_last_name }}
                        </p>
                        <p class="text-sm text-slate-500">{{ order.shipping_address }}, {{ order.shipping_city }}</p>
                        <p class="text-sm text-slate-500">{{ order.shipping_phone }}</p>
                    </div>
                </div>

                <!-- Paiement COD -->
                <div v-if="order.payment_method === 'cod'" class="bg-amber-50 border border-amber-200 rounded-xl px-6 py-4 mb-6">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-800 mb-1">Paiement à la livraison</p>
                            <p class="text-sm text-amber-700">Préparez <strong>{{ formatPrice(order.total) }}</strong> en espèces lors de la réception.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        v-if="$page.props.auth?.user"
                        href="/mon-compte/commandes"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition"
                    >
                        Suivre ma commande
                    </Link>
                    <Link
                        href="/boutique"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white text-slate-700 text-sm font-medium rounded-lg border border-slate-200 hover:bg-slate-50 transition"
                    >
                        Continuer mes achats
                    </Link>
                </div>

            </div>
        </div>
    </FrontLayout>
</template>
