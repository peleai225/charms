<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cart';
import { useUserStore } from '@/Stores/user';

const page      = usePage();
const cartStore = useCartStore();
const userStore = useUserStore();
</script>

<template>
    <nav class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white border-t border-slate-200 safe-area-pb">
        <div class="flex items-stretch h-16">
            <Link href="/"
                  class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                  :class="page.url === '/' ? 'text-primary-600' : 'text-slate-500'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Accueil</span>
            </Link>
            <Link href="/boutique"
                  class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                  :class="page.url.startsWith('/boutique') ? 'text-primary-600' : 'text-slate-500'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Boutique</span>
            </Link>
            <Link href="/panier"
                  class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                  :class="page.url.startsWith('/panier') ? 'text-primary-600' : 'text-slate-500'">
                <span class="relative inline-flex">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span v-if="cartStore.count > 0"
                          class="absolute -top-1.5 -right-2 w-4 h-4 bg-primary-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                        {{ cartStore.count }}
                    </span>
                </span>
                <span>Panier</span>
            </Link>
            <Link :href="userStore.isAuthenticated ? '/mon-compte' : '/connexion'"
                  class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                  :class="page.url.startsWith('/mon-compte') || page.url.startsWith('/connexion') ? 'text-primary-600' : 'text-slate-500'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Compte</span>
            </Link>
        </div>
    </nav>
</template>
