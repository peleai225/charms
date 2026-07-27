<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentUrl = computed(() => page.url);

const nav = [
    { href: '/mon-compte',           label: 'Tableau de bord', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { href: '/mon-compte/commandes',  label: 'Mes commandes',  icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    { href: '/mon-compte/adresses',   label: 'Mes adresses',   icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
    { href: '/mon-compte/fidelite',   label: 'Fidélité',       icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
    { href: '/mon-compte/favoris',    label: 'Mes favoris',    icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' },
];

const isActive = (href) => {
    if (href === '/mon-compte') return currentUrl.value === '/mon-compte' || currentUrl.value === '/mon-compte/';
    return currentUrl.value.startsWith(href);
};

const initials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
});
</script>

<template>
    <!-- Mobile: onglets horizontaux scrollables -->
    <div class="md:hidden bg-white border-b border-slate-200 sticky top-0 z-20">
        <div class="flex overflow-x-auto scrollbar-hide">
            <Link
                v-for="item in nav"
                :key="item.href"
                :href="item.href"
                class="flex-none px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                :class="isActive(item.href)
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-slate-500 hover:text-slate-700'"
            >
                {{ item.label }}
            </Link>
        </div>
    </div>

    <!-- Desktop: sidebar sticky -->
    <aside class="hidden md:block w-64 shrink-0">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden sticky top-4">
            <!-- User header -->
            <div class="bg-slate-900 px-5 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold text-white shrink-0">
                        {{ initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ user?.name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ user?.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Nav links -->
            <nav class="py-2">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors"
                    :class="isActive(item.href)
                        ? 'bg-blue-50 text-blue-700 font-medium'
                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- Logout -->
            <div class="border-t border-slate-100 px-4 py-3">
                <Link
                    href="/deconnexion"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-3 px-1 py-1 text-sm text-slate-500 hover:text-red-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Se déconnecter
                </Link>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
