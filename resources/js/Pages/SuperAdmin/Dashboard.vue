<template>
    <div class="min-h-screen bg-slate-950">
        <!-- Header -->
        <header class="bg-slate-900 border-b border-slate-700/60 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-primary-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">Super Administration</h1>
                            <p class="text-sm text-slate-400">Tableau de bord</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link :href="route('admin.dashboard')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Admin</span>
                        </Link>
                        <Link :href="route('superadmin.logout')" method="post" as="button" class="px-4 py-2 bg-danger-900/40 hover:bg-danger-900/60 text-danger-300 hover:text-white rounded-lg transition-colors border border-danger-700/50 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Déconnexion</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- SuperAdmins -->
                <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-primary-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span class="px-3 py-1 bg-slate-800 text-slate-300 text-xs font-bold rounded-full border border-slate-600">CRITICAL</span>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">{{ stats.superadmins }}</h3>
                    <p class="text-slate-400 text-sm">Super Administrateurs</p>
                </div>

                <!-- Admins -->
                <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-primary-700">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">{{ stats.admins }}</h3>
                    <p class="text-slate-400 text-sm">Administrateurs</p>
                </div>

                <!-- Total Users -->
                <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-primary-800">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">{{ stats.users }}</h3>
                    <p class="text-slate-400 text-sm">Tous les utilisateurs</p>
                </div>

                <!-- Customers -->
                <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-success-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">{{ stats.customers }}</h3>
                    <p class="text-slate-400 text-sm">Clients</p>
                </div>

                <!-- Orders -->
                <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-success-700">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">{{ stats.orders }}</h3>
                    <p class="text-slate-400 text-sm">Commandes</p>
                </div>

                <!-- Products -->
                <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-slate-700">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-2">{{ stats.products }}</h3>
                    <p class="text-slate-400 text-sm">Produits</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-slate-900 rounded-2xl border border-slate-700/60 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-white">Activité Récente</h2>
                </div>

                <div class="space-y-3">
                    <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start gap-4 p-4 bg-slate-950 rounded-xl border border-slate-700/40 hover:border-slate-600 transition-colors">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center border border-slate-700">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium">{{ activity.description }}</p>
                            <div class="flex items-center gap-3 mt-1 text-sm">
                                <span v-if="activity.user" class="text-slate-300">{{ activity.user.name }} ({{ activity.user.role }})</span>
                                <span class="text-slate-500">{{ activity.created_at }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="recentActivity.length === 0" class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p>Aucune activité récente</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recentActivity: Array,
});
</script>
