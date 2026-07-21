<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    subtitle: String,
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4">
        <Head :title="title" />

        <div class="max-w-sm mx-auto w-full mt-12 mb-20">
            <!-- Logo + Titre -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center justify-center gap-2 text-slate-900 mb-4">
                    <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl">{{ $page.props.settings?.site_name || 'Chamse' }}</span>
                </Link>
                <h1 class="text-2xl font-bold text-slate-900">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1 text-sm text-slate-500">{{ subtitle }}</p>
            </div>

            <!-- Content -->
            <slot />

            <!-- Legal -->
            <p class="mt-6 text-center text-xs text-slate-400">
                En continuant vous acceptez nos
                <Link href="/legal/conditions-generales" class="underline underline-offset-2 hover:text-primary-600">CGV</Link>
                et notre
                <Link href="/legal/politique-de-confidentialite" class="underline underline-offset-2 hover:text-primary-600">politique de confidentialité</Link>
            </p>
        </div>
    </div>
</template>
