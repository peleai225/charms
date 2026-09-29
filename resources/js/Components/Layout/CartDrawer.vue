<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cart';

const props = defineProps({
    open: { type: Boolean, default: false },
    primaryColor: { type: String, default: '#2563EB' },
});

const emit = defineEmits(['update:open']);

const cartStore = useCartStore();
const items     = ref([]);
const subtotal  = ref(0);
const loading   = ref(false);

async function fetchDrawer() {
    loading.value = true;
    try {
        const res = await fetch('/panier/drawer', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
            const data = await res.json();
            items.value    = data.items    || [];
            subtotal.value = data.subtotal ?? 0;
            cartStore.setCount(data.count  ?? 0);
        }
    } catch (e) {
        console.error('CartDrawer fetch error:', e);
    } finally {
        loading.value = false;
    }
}

watch(() => props.open, (val) => { if (val) fetchDrawer(); });

function close() { emit('update:open', false); }

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR').format(p) + ' F CFA';
}
</script>

<template>
    <Transition enter-from-class="opacity-0" enter-active-class="transition duration-250"
                leave-to-class="opacity-0" leave-active-class="transition duration-200">
    <div v-if="open" class="fixed inset-0 z-[200] flex justify-end" @keydown.escape="close" tabindex="-1">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/50" @click="close"/>

        <!-- Panel -->
        <div class="relative w-full max-w-sm bg-white h-full flex flex-col shadow-2xl translate-x-0
                    transition-transform duration-300">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">
                    Mon panier
                    <span v-if="cartStore.count > 0" class="ml-1.5 text-sm text-slate-400 font-normal">({{ cartStore.count }})</span>
                </h2>
                <button @click="close"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Items -->
            <div class="flex-1 overflow-y-auto px-5 py-4">
                <!-- Skeleton loading -->
                <div v-if="loading" class="space-y-4">
                    <div v-for="i in 3" :key="i" class="flex gap-3 animate-pulse">
                        <div class="w-16 h-16 bg-slate-100 rounded-lg shrink-0"/>
                        <div class="flex-1 space-y-2 pt-1">
                            <div class="h-3 bg-slate-100 rounded w-3/4"/>
                            <div class="h-3 bg-slate-100 rounded w-1/2"/>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else-if="items.length === 0"
                     class="flex flex-col items-center justify-center h-full py-16 text-center">
                    <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="text-sm font-medium text-slate-500 mb-4">Votre panier est vide</p>
                    <Link href="/boutique" @click="close"
                          class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition hover:opacity-90"
                          :style="{ backgroundColor: primaryColor }">
                        Découvrir nos produits
                    </Link>
                </div>

                <!-- Items list -->
                <div v-else class="space-y-4">
                    <div v-for="item in items" :key="item.id ?? item.product_id" class="flex gap-3">
                        <div class="w-16 h-16 bg-slate-50 rounded-lg overflow-hidden shrink-0 border border-slate-100">
                            <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ item.name }}</p>
                            <p v-if="item.variant_label" class="text-xs text-slate-400 mt-0.5">{{ item.variant_label }}</p>
                            <div class="flex items-center justify-between mt-1.5">
                                <span class="text-xs text-slate-500">Qté : {{ item.quantity }}</span>
                                <span class="text-sm font-semibold text-slate-900">{{ formatPrice(item.total ?? item.price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer avec total + CTA -->
            <div v-if="items.length > 0" class="border-t border-slate-100 px-5 py-4 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Sous-total</span>
                    <span class="font-semibold text-slate-900">{{ formatPrice(subtotal) }}</span>
                </div>
                <Link href="/panier" @click="close"
                      class="block w-full text-center py-3 rounded-xl text-white font-semibold text-sm transition hover:opacity-90"
                      :style="{ backgroundColor: primaryColor }">
                    Voir le panier &amp; commander
                </Link>
            </div>
        </div>
    </div>
    </Transition>
</template>
