<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page     = usePage();
const authUser = computed(() => page.props.auth?.user || null);
const userRole = computed(() => authUser.value?.role || null);

const showAdminBar      = computed(() => ['admin', 'manager', 'staff'].includes(userRole.value));
const showSuperAdminBar = computed(() => userRole.value === 'superadmin');

const adminBarVisible      = ref(true);
const superAdminBarVisible = ref(true);
</script>

<template>
    <!-- Barre Super Admin -->
    <Transition enter-from-class="opacity-0 -translate-y-full" enter-active-class="transition duration-200"
                leave-to-class="opacity-0 -translate-y-full" leave-active-class="transition duration-150">
    <div v-if="showSuperAdminBar && superAdminBarVisible"
         class="relative z-[400] h-9 bg-gradient-to-r from-violet-700 to-violet-600 text-white text-xs flex items-center shrink-0 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 w-full flex items-center gap-3">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-white/15 shrink-0">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                <span class="text-violet-200 hidden sm:inline shrink-0">Super Admin —</span>
                <span class="font-semibold truncate">{{ authUser?.name }}</span>
            </div>
            <a :href="route('superadmin.dashboard')"
               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white text-violet-700 text-[11px] font-semibold hover:bg-violet-50 transition-colors whitespace-nowrap shrink-0">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Espace Super Admin
            </a>
            <button @click="superAdminBarVisible = false" class="p-1 text-violet-300 hover:text-white transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    </Transition>

    <!-- Barre Admin / Manager / Staff -->
    <Transition enter-from-class="opacity-0 -translate-y-full" enter-active-class="transition duration-200"
                leave-to-class="opacity-0 -translate-y-full" leave-active-class="transition duration-150">
    <div v-if="showAdminBar && adminBarVisible"
         class="relative z-[300] h-9 bg-slate-900 text-white text-xs flex items-center shrink-0">
        <div class="max-w-7xl mx-auto px-4 w-full flex items-center gap-2">
            <div class="flex items-center gap-2 shrink-0 min-w-0 mr-2 border-r border-slate-700 pr-3">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-indigo-600 shrink-0">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                </span>
                <span class="font-medium text-slate-200 truncate hidden sm:block max-w-[120px]">{{ authUser?.name }}</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide shrink-0"
                      :class="{
                          'bg-red-500/25 text-red-300':     userRole === 'admin',
                          'bg-amber-500/25 text-amber-300': userRole === 'manager',
                          'bg-slate-600 text-slate-300':    userRole === 'staff',
                      }">{{ userRole }}</span>
            </div>
            <div class="flex items-center gap-0.5 flex-1 overflow-x-auto scrollbar-none">
                <a :href="route('admin.dashboard')"
                   class="flex items-center gap-1.5 px-2.5 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="hidden md:inline">Dashboard</span>
                </a>
                <a :href="route('admin.orders.index')"
                   class="flex items-center gap-1.5 px-2.5 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="hidden md:inline">Commandes</span>
                </a>
                <a v-if="['admin','manager'].includes(userRole)" :href="route('admin.products.index')"
                   class="flex items-center gap-1.5 px-2.5 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="hidden md:inline">Produits</span>
                </a>
                <a :href="route('admin.stock.index')"
                   class="flex items-center gap-1.5 px-2.5 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    <span class="hidden md:inline">Stock</span>
                </a>
                <a :href="route('admin.scanner.index')"
                   class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 hover:text-white transition-colors whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span class="hidden md:inline">Caisse</span>
                </a>
                <a :href="route('admin.customers.index')"
                   class="flex items-center gap-1.5 px-2.5 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="hidden md:inline">Clients</span>
                </a>
            </div>
            <button @click="adminBarVisible = false" class="p-1 text-slate-500 hover:text-slate-300 transition-colors shrink-0 ml-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    </Transition>
</template>

<style scoped>
.scrollbar-none { scrollbar-width: none; -ms-overflow-style: none; }
.scrollbar-none::-webkit-scrollbar { display: none; }
</style>
