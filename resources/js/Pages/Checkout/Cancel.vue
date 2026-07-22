<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    order: Object,
});
</script>

<template>
    <FrontLayout title="Paiement annulé">
        <Head title="Paiement annulé" />

        <div class="min-h-screen bg-slate-50 py-12">
            <div class="container mx-auto px-4 max-w-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Paiement annulé</h1>
                    <p class="text-slate-500 mb-8">
                        Votre paiement n'a pas abouti. Votre panier a été conservé.
                    </p>

                    <div v-if="order" class="bg-white border border-slate-200 rounded-xl px-6 py-4 mb-8 text-left">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-1">Commande concernée</p>
                        <p class="text-sm font-semibold text-slate-900">#{{ order.order_number }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            href="/commander"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition"
                        >
                            Réessayer le paiement
                        </Link>
                        <Link
                            href="/panier"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white text-slate-700 text-sm font-medium rounded-lg border border-slate-200 hover:bg-slate-50 transition"
                        >
                            Retour au panier
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
