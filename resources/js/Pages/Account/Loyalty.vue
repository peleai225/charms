<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    transactions: Object,
});
</script>

<template>
    <FrontLayout title="Mon programme fidélité">
        <Head title="Fidélité" />

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="container mx-auto px-4 max-w-3xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Programme fidélité</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Vos points et historique</p>
                    </div>
                    <Link href="/mon-compte" class="text-sm text-slate-500 hover:text-slate-700 transition">← Mon compte</Link>
                </div>

                <!-- Points balance -->
                <div class="bg-slate-900 rounded-xl px-8 py-6 mb-6 text-white">
                    <p class="text-sm text-slate-400 mb-1">Solde de points</p>
                    <p class="text-5xl font-bold tabular-nums">{{ customer.points_balance.toLocaleString('fr-FR') }}</p>
                    <p class="text-sm text-slate-400 mt-2">points disponibles</p>
                </div>

                <!-- Historique -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-900">Historique des transactions</p>
                    </div>

                    <!-- Empty -->
                    <div v-if="transactions.data.length === 0" class="py-12 text-center">
                        <p class="text-sm text-slate-500">Aucune transaction pour l'instant.</p>
                    </div>

                    <!-- List -->
                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="tx in transactions.data"
                            :key="tx.id"
                            class="px-5 py-4 flex items-center justify-between"
                        >
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ tx.description || (tx.type === 'earn' ? 'Points gagnés' : 'Points utilisés') }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ tx.created_at }}</p>
                            </div>
                            <span
                                class="text-sm font-semibold tabular-nums"
                                :class="tx.type === 'earn' ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ tx.type === 'earn' ? '+' : '-' }}{{ tx.points }} pts
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.last_page > 1" class="mt-4 flex justify-center gap-2">
                    <span class="text-sm text-slate-500">Page {{ transactions.current_page }} / {{ transactions.last_page }}</span>
                </div>

            </div>
        </div>
    </FrontLayout>
</template>
