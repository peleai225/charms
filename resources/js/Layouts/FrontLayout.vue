<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { useCartStore } from '@/Stores/cart';
import { useUserStore } from '@/Stores/user';
import { useNotificationStore } from '@/Stores/notifications';

const props = defineProps({
    title: String,
});

const cartStore = useCartStore();
const userStore = useUserStore();
const notificationStore = useNotificationStore();

// Sync cart count from shared props
const page = computed(() => usePage());
onMounted(() => {
    if (page.value?.props?.cart_count) {
        cartStore.setCount(page.value.props.cart_count);
    }
    if (page.value?.props?.auth?.user) {
        userStore.setUser(page.value.props.auth.user);
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <Head :title="title" />

        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-3">
                        <span class="text-xl font-bold text-slate-900">
                            {{ $page.props.settings?.site_name || 'Chamse' }}
                        </span>
                    </Link>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center gap-6">
                        <Link href="/" class="text-sm font-medium text-slate-700 hover:text-primary-600">
                            Accueil
                        </Link>
                        <Link href="/boutique" class="text-sm font-medium text-slate-700 hover:text-primary-600">
                            Boutique
                        </Link>
                        <Link href="/contact" class="text-sm font-medium text-slate-700 hover:text-primary-600">
                            Contact
                        </Link>
                    </nav>

                    <!-- Right actions -->
                    <div class="flex items-center gap-4">
                        <!-- Cart -->
                        <Link href="/panier" class="relative">
                            <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span v-if="cartStore.count > 0" class="absolute -top-2 -right-2 bg-primary-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                {{ cartStore.count }}
                            </span>
                        </Link>

                        <!-- User -->
                        <Link v-if="userStore.isAuthenticated" href="/mon-compte" class="text-sm font-medium text-slate-700 hover:text-primary-600">
                            Mon compte
                        </Link>
                        <Link v-else href="/connexion" class="text-sm font-medium text-slate-700 hover:text-primary-600">
                            Connexion
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-300 mt-auto">
            <div class="container mx-auto px-4 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="font-bold text-white mb-4">{{ $page.props.settings?.site_name }}</h3>
                        <p class="text-sm">Votre boutique en ligne de confiance</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Liens rapides</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link href="/boutique" class="hover:text-white">Boutique</Link></li>
                            <li><Link href="/contact" class="hover:text-white">Contact</Link></li>
                            <li><Link href="/a-propos" class="hover:text-white">À propos</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Informations</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link href="/legal/conditions-generales" class="hover:text-white">CGV</Link></li>
                            <li><Link href="/legal/politique-de-confidentialite" class="hover:text-white">Confidentialité</Link></li>
                            <li><Link href="/legal/livraison" class="hover:text-white">Livraison</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-4">Suivez-nous</h4>
                        <p class="text-sm">Restez connecté sur nos réseaux sociaux</p>
                    </div>
                </div>
                <div class="border-t border-slate-800 mt-8 pt-8 text-center text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} {{ $page.props.settings?.site_name }}. Tous droits réservés.</p>
                </div>
            </div>
        </footer>

        <!-- Notifications Toast -->
        <div class="fixed bottom-4 right-4 z-50 space-y-2">
            <TransitionGroup name="notification">
                <div
                    v-for="notification in notificationStore.notifications"
                    :key="notification.id"
                    class="bg-white border-l-4 rounded-lg shadow-lg p-4 min-w-[300px]"
                    :class="{
                        'border-success-500': notification.type === 'success',
                        'border-danger-500': notification.type === 'error',
                        'border-warning-500': notification.type === 'warning',
                        'border-primary-500': notification.type === 'info',
                    }"
                >
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <p class="text-sm text-slate-900">{{ notification.message }}</p>
                        </div>
                        <button
                            @click="notificationStore.remove(notification.id)"
                            class="text-slate-400 hover:text-slate-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}
.notification-enter-from {
    opacity: 0;
    transform: translateX(100px);
}
.notification-leave-to {
    opacity: 0;
    transform: translateX(100px);
}
</style>
