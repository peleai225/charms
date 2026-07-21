<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';

defineProps({
    stats: Object,
});
</script>

<template>
    <FrontLayout title="Mon compte">
        <Head title="Mon compte" />

        <div class="bg-slate-50 min-h-screen py-8">
            <div class="container mx-auto px-4">
                <h1 class="text-2xl font-bold text-slate-900 mb-6">Tableau de bord</h1>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <Card padding="default">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats?.orders_count || 0 }}</p>
                                <p class="text-sm text-slate-500">Commandes</p>
                            </div>
                        </div>
                    </Card>

                    <Card padding="default">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats?.orders_delivered || 0 }}</p>
                                <p class="text-sm text-slate-500">Livrées</p>
                            </div>
                        </div>
                    </Card>

                    <Card padding="default">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ stats?.orders_pending || 0 }}</p>
                                <p class="text-sm text-slate-500">En cours</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Quick links -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link href="/mon-compte/commandes">
                        <Card padding="default" class="hover:shadow-lg transition cursor-pointer">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-slate-900 mb-1">Mes commandes</h3>
                                    <p class="text-sm text-slate-500">Suivez vos commandes</p>
                                </div>
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </Card>
                    </Link>

                    <Link href="/mon-compte/adresses">
                        <Card padding="default" class="hover:shadow-lg transition cursor-pointer">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-slate-900 mb-1">Mes adresses</h3>
                                    <p class="text-sm text-slate-500">Gérez vos adresses de livraison</p>
                                </div>
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </Card>
                    </Link>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
