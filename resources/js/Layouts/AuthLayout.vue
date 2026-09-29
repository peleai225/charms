<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    title: String,
    subtitle: String,
});

const page     = usePage();
const siteName = computed(() => page.props.settings?.site_name || 'Chamse');
const logoPath = computed(() => page.props.settings?.logo || null);
</script>

<template>
    <div class="min-h-screen flex">
        <Head :title="title" />

        <!-- Gauche : formulaire -->
        <div class="w-full lg:w-[45%] flex flex-col justify-center px-8 sm:px-14 lg:px-16 py-12 bg-white">

            <!-- Logo -->
            <div class="mb-10">
                <Link href="/" class="inline-flex items-center gap-2.5 text-slate-900 hover:opacity-80 transition-opacity">
                    <img
                        v-if="logoPath"
                        :src="`/storage/${logoPath}`"
                        :alt="siteName"
                        class="h-9 w-auto object-contain"
                    />
                    <template v-else>
                        <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-lg tracking-tight">{{ siteName }}</span>
                    </template>
                </Link>
            </div>

            <!-- Titre -->
            <div class="mb-8">
                <h1 class="text-[1.75rem] font-bold text-slate-900 leading-tight">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1.5 text-slate-500 text-sm">{{ subtitle }}</p>
            </div>

            <!-- Contenu formulaire -->
            <div class="max-w-sm w-full">
                <slot />
            </div>

            <!-- Légal bas de page -->
            <p class="mt-10 text-xs text-slate-400">
                © {{ new Date().getFullYear() }} {{ siteName }} ·
                <Link href="/legal/conditions-generales" class="hover:text-slate-600 transition-colors">CGV</Link>
                ·
                <Link href="/legal/politique-de-confidentialite" class="hover:text-slate-600 transition-colors">Confidentialité</Link>
            </p>
        </div>

        <!-- Droite : panneau visuel (caché sur mobile) -->
        <div class="hidden lg:block flex-1 relative bg-stone-100 p-5">
            <!-- Carte arrondie qui remplit le panneau -->
            <div class="relative w-full h-full rounded-3xl overflow-hidden">

                <!-- Fond sombre -->
                <div class="absolute inset-0 bg-slate-900"></div>

                <!-- Overlay dégradé bas -->
                <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>

                <!-- Contenu bas -->
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <p class="text-amber-400 text-xs font-semibold uppercase tracking-widest mb-3">Chamse · Boutique en ligne</p>
                    <h2 class="text-white text-2xl font-bold leading-snug mb-2">
                        Découvrez les meilleurs<br>produits de qualité
                    </h2>
                    <p class="text-white/60 text-sm mb-5 leading-relaxed">
                        Des articles soigneusement sélectionnés,<br>livrés directement chez vous en Côte d'Ivoire.
                    </p>

                    <!-- Badges -->
                    <div class="flex flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-1.5 text-xs text-white/90 border border-white/25 rounded-full px-3.5 py-1.5 bg-white/5">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Qualité garantie
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs text-white/90 border border-white/25 rounded-full px-3.5 py-1.5 bg-white/5">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Livraison Abidjan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
