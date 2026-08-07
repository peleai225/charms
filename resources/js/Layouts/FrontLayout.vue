<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { useCartStore } from '@/Stores/cart';
import { useUserStore } from '@/Stores/user';
import { useNotificationStore } from '@/Stores/notifications';
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'

const props = defineProps({
    title: String,
});

const page = usePage();
const cartStore = useCartStore();
const userStore = useUserStore();
const notificationStore = useNotificationStore();

const settings = computed(() => page.props.settings || {});
const siteName = computed(() => settings.value.site_name || 'Chamse');
const logoPath  = computed(() => settings.value.logo || null);

// Sync depuis shared props à chaque navigation
watch(() => page.props.cart_count, (val) => {
    if (val !== undefined) {
        cartStore.setCount(val);
        // Si des articles dans le panier, sync les IDs pour les indicateurs de carte
        if (val > 0 && cartStore.productIds.size === 0) {
            cartStore.sync();
        }
    }
}, { immediate: true });

watch(() => page.props.auth?.user, (val) => {
    if (val) userStore.setUser(val);
    else userStore.clearUser();
}, { immediate: true });

// ─── Scroll pour header compact ────────────────────────────────────────────────
const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 60; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// ─── Menu mobile ───────────────────────────────────────────────────────────────
const mobileMenuOpen = ref(false);
const closeMobile = () => { mobileMenuOpen.value = false; };
watch(() => page.url, closeMobile);

// ─── Search ────────────────────────────────────────────────────────────────────
const searchOpen  = ref(false);
const searchQuery = ref('');
const openSearch  = () => { searchOpen.value = true; };
const closeSearch = () => { searchOpen.value = false; searchQuery.value = ''; };
const submitSearch = () => {
    if (!searchQuery.value.trim()) return;
    router.get('/boutique', { search: searchQuery.value });
    closeSearch();
};

// ─── Bannière admin ────────────────────────────────────────────────────────────
const authUser           = computed(() => page.props.auth?.user || null)
const userRole           = computed(() => authUser.value?.role || null)
const showAdminBar       = computed(() => ['admin', 'manager', 'staff'].includes(userRole.value))
const showSuperAdminBar  = computed(() => userRole.value === 'superadmin')
const adminBarVisible    = ref(true)
const superAdminBarVisible = ref(true)

// ─── Bannières promo ───────────────────────────────────────────────────────────
const banners            = computed(() => page.props.banners || { announcement: [], popup: null })
const announcementList   = computed(() => banners.value.announcement || [])
const popupBanner        = computed(() => banners.value.popup || null)

const announcementIndex  = ref(0)
const announcementHidden = ref(false)
const popupVisible       = ref(false)
let announcementTimer    = null

function dismissAnnouncement() {
    announcementHidden.value = true
    if (announcementList.value[0]) {
        try { localStorage.setItem(`ann_dismissed_${announcementList.value[0].id}`, '1') } catch {}
    }
}

function dismissPopup() {
    popupVisible.value = false
    if (popupBanner.value) {
        try { localStorage.setItem(`popup_dismissed_${popupBanner.value.id}`, '1') } catch {}
    }
}

onMounted(() => {
    // Vérifier si la barre d'annonce a été fermée
    if (announcementList.value[0]) {
        try {
            if (localStorage.getItem(`ann_dismissed_${announcementList.value[0].id}`) === '1') {
                announcementHidden.value = true
            }
        } catch {}
    }
    // Auto-rotation si plusieurs annonces
    if (announcementList.value.length > 1) {
        announcementTimer = setInterval(() => {
            announcementIndex.value = (announcementIndex.value + 1) % announcementList.value.length
        }, 4000)
    }
    // Popup après délai
    if (popupBanner.value) {
        try {
            if (localStorage.getItem(`popup_dismissed_${popupBanner.value.id}`) !== '1') {
                setTimeout(() => { popupVisible.value = true }, 1500)
            }
        } catch {
            setTimeout(() => { popupVisible.value = true }, 1500)
        }
    }
})

onUnmounted(() => {
    if (announcementTimer) clearInterval(announcementTimer)
})

// ─── Flash messages ────────────────────────────────────────────────────────────
const flash = computed(() => page.props.flash || {});
watch(flash, (f) => {
    if (f.success) notificationStore.success(f.success);
    if (f.error)   notificationStore.error(f.error);
}, { deep: true, immediate: true });

const navLinks = [
    { href: '/',         label: 'Accueil'  },
    { href: '/boutique', label: 'Boutique', hasDropdown: true },
    { href: '/a-propos', label: 'À propos' },
    { href: '/contact',  label: 'Contact'  },
];

const navCategories = computed(() => page.props.nav_categories || []);

// Dropdown boutique
const shopDropdownOpen = ref(false);
let shopDropdownTimer = null;
const openShopDropdown  = () => { clearTimeout(shopDropdownTimer); shopDropdownOpen.value = true; };
const closeShopDropdown = () => { shopDropdownTimer = setTimeout(() => { shopDropdownOpen.value = false; }, 120); };

// Dropdown compte
const accountDropdownOpen = ref(false);
let accountDropdownTimer = null;
const openAccountDropdown  = () => { clearTimeout(accountDropdownTimer); accountDropdownOpen.value = true; };
const closeAccountDropdown = () => { accountDropdownTimer = setTimeout(() => { accountDropdownOpen.value = false; }, 120); };

// Fermer dropdowns à la navigation
watch(() => page.url, () => {
    shopDropdownOpen.value    = false;
    accountDropdownOpen.value = false;
    closeMobile();
});

onUnmounted(() => {
    clearTimeout(shopDropdownTimer);
    clearTimeout(accountDropdownTimer);
});
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col">
        <Head :title="title ? `${title} — ${siteName}` : siteName" />

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- BARRE SUPER ADMIN                                               -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <Transition enter-from-class="opacity-0 -translate-y-full" enter-active-class="transition duration-200"
                    leave-to-class="opacity-0 -translate-y-full" leave-active-class="transition duration-150">
        <div v-if="showSuperAdminBar && superAdminBarVisible"
             class="relative z-[400] h-9 bg-gradient-to-r from-violet-700 to-violet-600 text-white text-xs flex items-center shrink-0 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 w-full flex items-center gap-3">
                <!-- Gauche -->
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-white/15 shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <span class="text-violet-200 hidden sm:inline shrink-0">Super Admin —</span>
                    <span class="font-semibold truncate">{{ authUser?.name }}</span>
                </div>
                <!-- Bouton backoffice -->
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

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- BARRE ADMIN / MANAGER / STAFF                                  -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <Transition enter-from-class="opacity-0 -translate-y-full" enter-active-class="transition duration-200"
                    leave-to-class="opacity-0 -translate-y-full" leave-active-class="transition duration-150">
        <div v-if="showAdminBar && adminBarVisible"
             class="relative z-[300] h-9 bg-slate-900 text-white text-xs flex items-center shrink-0">
            <div class="max-w-7xl mx-auto px-4 w-full flex items-center gap-2">
                <!-- Identité -->
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
                <!-- Raccourcis -->
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
                <!-- Fermer -->
                <button @click="adminBarVisible = false" class="p-1 text-slate-500 hover:text-slate-300 transition-colors shrink-0 ml-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- BARRE D'ANNONCE (depuis DB)                                    -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <Transition enter-from-class="opacity-0 -translate-y-2" enter-active-class="transition duration-300">
        <div v-if="announcementList.length && !announcementHidden"
             class="relative text-sm py-2.5 text-center px-10 shrink-0 overflow-hidden"
             :style="{
                 backgroundColor: announcementList[announcementIndex]?.background_color || '#2563EB',
                 color: announcementList[announcementIndex]?.text_color || '#ffffff',
             }">
            <!-- Texte + lien -->
            <div class="flex items-center justify-center gap-2 flex-wrap">
                <span class="font-medium">{{ announcementList[announcementIndex]?.title }}</span>
                <span v-if="announcementList[announcementIndex]?.subtitle" class="opacity-80 hidden sm:inline">
                    {{ announcementList[announcementIndex]?.subtitle }}
                </span>
                <a v-if="announcementList[announcementIndex]?.link"
                   :href="announcementList[announcementIndex].link"
                   class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 hover:bg-white/30 transition-colors">
                    {{ announcementList[announcementIndex]?.button_text || 'Découvrir' }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <!-- Pagination points -->
            <div v-if="announcementList.length > 1" class="flex items-center justify-center gap-1 mt-1">
                <button v-for="(_, i) in announcementList" :key="i"
                        @click="announcementIndex = i"
                        class="w-1.5 h-1.5 rounded-full transition-colors"
                        :class="i === announcementIndex ? 'bg-white' : 'bg-white/40'"/>
            </div>
            <!-- Fermer -->
            <button @click="dismissAnnouncement"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 opacity-60 hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        </Transition>

        <!-- Fallback topbar si aucune annonce en DB -->
        <div v-if="!announcementList.length && !showAdminBar && !showSuperAdminBar"
             class="bg-slate-900 text-slate-400 text-xs py-2 text-center px-4 hidden sm:block shrink-0">
            <span>Livraison rapide partout en Côte d'Ivoire</span>
            <span class="mx-3 text-slate-700">·</span>
            <span>Paiement sécurisé</span>
            <span class="mx-3 text-slate-700">·</span>
            <span>Support 7j/7 sur WhatsApp</span>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- POPUP BANNIÈRE                                                  -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-300"
                    leave-to-class="opacity-0" leave-active-class="transition duration-200">
        <div v-if="popupBanner && popupVisible"
             class="fixed inset-0 z-[500] flex items-center justify-center p-4"
             @keydown.escape="dismissPopup" tabindex="-1">
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="dismissPopup"/>
            <div class="relative z-10 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl"
                 :style="{ backgroundColor: popupBanner.background_color || '#1e293b' }">
                <img v-if="popupBanner.image_url" :src="popupBanner.image_url" class="w-full object-cover max-h-52"/>
                <div class="p-6" :style="{ color: popupBanner.text_color || '#ffffff' }">
                    <h3 class="text-xl font-bold leading-tight">{{ popupBanner.title }}</h3>
                    <p v-if="popupBanner.subtitle" class="mt-1 opacity-80 text-sm">{{ popupBanner.subtitle }}</p>
                    <p v-if="popupBanner.description" class="mt-3 text-sm opacity-70 leading-relaxed">{{ popupBanner.description }}</p>
                    <div class="mt-5 flex items-center gap-3">
                        <a v-if="popupBanner.link" :href="popupBanner.link"
                           class="flex-1 text-center px-4 py-2.5 rounded-xl bg-white font-semibold text-sm transition-opacity hover:opacity-90"
                           :style="{ color: popupBanner.background_color || '#1e293b' }"
                           @click="dismissPopup">
                            {{ popupBanner.button_text || 'Découvrir' }}
                        </a>
                        <button @click="dismissPopup"
                                class="px-4 py-2.5 rounded-xl text-sm opacity-60 hover:opacity-100 transition-opacity border border-white/20">
                            Fermer
                        </button>
                    </div>
                </div>
                <button @click="dismissPopup" class="absolute top-3 right-3 p-1.5 rounded-full bg-black/20 hover:bg-black/40 transition-colors text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        </Transition>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- HEADER                                                             -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <header
            class="bg-white border-b sticky top-0 z-50 transition-shadow duration-200"
            :class="scrolled ? 'shadow-md border-transparent' : 'border-slate-200'"
        >
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">

                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2.5 shrink-0">
                        <img
                            v-if="logoPath"
                            :src="`/storage/${logoPath}`"
                            :alt="siteName"
                            class="h-9 w-auto object-contain"
                        />
                        <span v-else class="text-xl font-black text-slate-900 tracking-tight">{{ siteName }}</span>
                    </Link>

                    <!-- Nav desktop -->
                    <nav class="hidden lg:flex items-center gap-0.5">
                        <template v-for="link in navLinks" :key="link.href">
                            <!-- Boutique avec dropdown catégories -->
                            <div
                                v-if="link.hasDropdown"
                                class="relative"
                                @mouseenter="openShopDropdown"
                                @mouseleave="closeShopDropdown"
                            >
                                <Link
                                    :href="link.href"
                                    class="flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="$page.url.startsWith(link.href)
                                        ? 'text-slate-900 bg-slate-50'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                                >
                                    {{ link.label }}
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="shopDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </Link>

                                <!-- Dropdown catégories -->
                                <Transition name="dropdown">
                                    <div
                                        v-if="shopDropdownOpen"
                                        class="absolute left-0 top-full mt-1 w-72 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50"
                                        @mouseenter="openShopDropdown"
                                        @mouseleave="closeShopDropdown"
                                    >
                                        <!-- Header -->
                                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Catégories</p>
                                            <Link href="/boutique" class="text-xs text-slate-500 hover:text-slate-900 transition">Voir tout →</Link>
                                        </div>
                                        <!-- Catégories -->
                                        <div v-if="navCategories.length" class="py-2">
                                            <Link
                                                v-for="cat in navCategories"
                                                :key="cat.id"
                                                :href="`/categorie/${cat.slug}`"
                                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition group"
                                            >
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                                                    <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name" class="w-full h-full object-cover" />
                                                    <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                                </div>
                                                <span class="text-sm text-slate-700 group-hover:text-slate-900 font-medium transition">{{ cat.name }}</span>
                                            </Link>
                                        </div>
                                        <div v-else class="py-4 px-4 text-sm text-slate-400">Aucune catégorie</div>
                                        <!-- Footer -->
                                        <div class="border-t border-slate-100 p-3">
                                            <Link href="/boutique" class="flex items-center justify-center gap-1.5 w-full py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-lg transition">
                                                Voir toute la boutique
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </Link>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Lien normal -->
                            <Link
                                v-else
                                :href="link.href"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                                :class="$page.url === link.href || ($page.url.startsWith(link.href) && link.href !== '/')
                                    ? 'text-slate-900 bg-slate-50'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                            >
                                {{ link.label }}
                            </Link>
                        </template>
                    </nav>

                    <!-- Actions droite -->
                    <div class="flex items-center gap-1 sm:gap-2">

                        <!-- Search trigger -->
                        <button
                            @click="openSearch"
                            class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                            aria-label="Rechercher"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                            </svg>
                        </button>

                        <!-- Favoris (si connecté) -->
                        <Link
                            v-if="userStore.isAuthenticated"
                            href="/mon-compte/favoris"
                            class="w-9 h-9 hidden sm:flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                            aria-label="Mes favoris"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </Link>

                        <!-- Panier -->
                        <Link
                            href="/panier"
                            class="relative w-9 h-9 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                            aria-label="Panier"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span
                                v-if="cartStore.count > 0"
                                class="absolute -top-1 -right-1 bg-slate-900 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-none"
                            >
                                {{ cartStore.count > 99 ? '99+' : cartStore.count }}
                            </span>
                        </Link>

                        <!-- Compte (connecté = dropdown, sinon lien connexion) -->
                        <Link
                            v-if="!userStore.isAuthenticated"
                            href="/connexion"
                            class="hidden sm:flex items-center gap-1.5 pl-2 pr-3 py-1.5 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="hidden md:block">Connexion</span>
                        </Link>

                        <!-- Dropdown compte (connecté) -->
                        <div
                            v-else
                            class="relative hidden sm:block"
                            @mouseenter="openAccountDropdown"
                            @mouseleave="closeAccountDropdown"
                        >
                            <button class="flex items-center gap-2 pl-1 pr-2 py-1.5 hover:bg-slate-100 rounded-lg transition">
                                <div class="w-7 h-7 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ (userStore.user?.name || 'U')[0].toUpperCase() }}
                                </div>
                                <span class="hidden md:block text-sm font-medium text-slate-700 max-w-[90px] truncate">{{ userStore.user?.name }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <Transition name="dropdown">
                                <div
                                    v-if="accountDropdownOpen"
                                    class="absolute right-0 top-full mt-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50"
                                    @mouseenter="openAccountDropdown"
                                    @mouseleave="closeAccountDropdown"
                                >
                                    <!-- Avatar header -->
                                    <div class="px-4 py-3 border-b border-slate-100">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ userStore.user?.name }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ userStore.user?.email }}</p>
                                    </div>
                                    <!-- Liens -->
                                    <div class="py-2">
                                        <Link href="/mon-compte" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition group">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            <span class="text-sm text-slate-700 group-hover:text-slate-900">Tableau de bord</span>
                                        </Link>
                                        <Link href="/mon-compte/commandes" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition group">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span class="text-sm text-slate-700 group-hover:text-slate-900">Mes commandes</span>
                                        </Link>
                                        <Link href="/mon-compte/favoris" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition group">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            <span class="text-sm text-slate-700 group-hover:text-slate-900">Mes favoris</span>
                                        </Link>
                                        <Link href="/mon-compte/fidelite" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition group">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                            <span class="text-sm text-slate-700 group-hover:text-slate-900">Programme fidélité</span>
                                        </Link>
                                    </div>
                                    <!-- Déconnexion -->
                                    <div class="border-t border-slate-100 p-2">
                                        <Link
                                            href="/deconnexion"
                                            method="post"
                                            as="button"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-50 transition group"
                                        >
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-red-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            <span class="text-sm text-slate-600 group-hover:text-red-600 transition">Se déconnecter</span>
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Burger mobile -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden w-9 h-9 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                            aria-label="Menu"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menu mobile -->
            <div
                v-if="mobileMenuOpen"
                class="lg:hidden border-t border-slate-100 bg-white"
            >
                <div class="container mx-auto px-4 py-3 space-y-0.5">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition"
                        :class="$page.url === link.href ? 'bg-slate-50 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-50'"
                    >
                        {{ link.label }}
                    </Link>
                    <div class="border-t border-slate-100 pt-2 mt-2 space-y-0.5">
                        <Link
                            :href="userStore.isAuthenticated ? '/mon-compte' : '/connexion'"
                            class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ userStore.isAuthenticated ? 'Mon compte' : 'Connexion' }}
                        </Link>
                        <Link
                            v-if="userStore.isAuthenticated"
                            href="/mon-compte/favoris"
                            class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Mes favoris
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- SEARCH OVERLAY                                                     -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <Transition name="search-overlay">
            <div v-if="searchOpen" class="fixed inset-0 z-[60] bg-black/50 flex items-start justify-center pt-20 px-4" @click.self="closeSearch">
                <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input
                            v-model="searchQuery"
                            @keyup.enter="submitSearch"
                            @keyup.esc="closeSearch"
                            type="search"
                            placeholder="Rechercher un produit..."
                            class="flex-1 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none"
                            autofocus
                        />
                        <button @click="closeSearch" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="px-4 py-3 text-xs text-slate-400 flex items-center justify-between">
                        <span>Appuyez sur <kbd class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600 font-mono">Entrée</kbd> pour rechercher</span>
                        <button @click="submitSearch" :disabled="!searchQuery.trim()" class="text-xs font-semibold text-slate-700 hover:text-slate-900 disabled:opacity-40 transition">
                            Rechercher →
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- MAIN CONTENT                                                       -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <main class="flex-1 pb-16 lg:pb-0">
            <slot />
        </main>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- FOOTER                                                             -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <footer class="bg-slate-900 text-slate-400">
            <!-- Contenu principal footer -->
            <div class="container mx-auto px-4 py-14">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                    <!-- Col 1 — Marque -->
                    <div>
                        <div class="mb-4">
                            <img v-if="logoPath" :src="`/storage/${logoPath}`" :alt="siteName" class="h-8 w-auto object-contain brightness-200 mb-3" />
                            <p v-else class="text-xl font-black text-white tracking-tight mb-3">{{ siteName }}</p>
                        </div>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Votre boutique en ligne de confiance en Côte d'Ivoire. Produits de qualité, livrés rapidement.
                        </p>
                        <!-- Réseaux sociaux -->
                        <div class="flex items-center gap-2 mt-5">
                            <a
                                v-if="$page.props.settings?.social_facebook"
                                :href="$page.props.settings.social_facebook"
                                target="_blank" rel="noopener"
                                class="w-8 h-8 bg-slate-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition"
                                aria-label="Facebook"
                            >
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a
                                v-if="$page.props.settings?.social_instagram"
                                :href="$page.props.settings.social_instagram"
                                target="_blank" rel="noopener"
                                class="w-8 h-8 bg-slate-800 hover:bg-pink-600 rounded-lg flex items-center justify-center transition"
                                aria-label="Instagram"
                            >
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a
                                v-if="$page.props.settings?.social_whatsapp"
                                :href="`https://wa.me/${$page.props.settings.social_whatsapp?.replace(/\D/g, '')}`"
                                target="_blank" rel="noopener"
                                class="w-8 h-8 bg-slate-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition"
                                aria-label="WhatsApp"
                            >
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Col 2 — Navigation -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Navigation</p>
                        <ul class="space-y-2.5">
                            <li><Link href="/"         class="text-sm hover:text-white transition">Accueil</Link></li>
                            <li><Link href="/boutique" class="text-sm hover:text-white transition">Boutique</Link></li>
                            <li><Link href="/a-propos" class="text-sm hover:text-white transition">À propos</Link></li>
                            <li><Link href="/contact"  class="text-sm hover:text-white transition">Contact</Link></li>
                        </ul>
                    </div>

                    <!-- Col 3 — Mon compte -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Mon compte</p>
                        <ul class="space-y-2.5">
                            <li><Link href="/mon-compte"            class="text-sm hover:text-white transition">Tableau de bord</Link></li>
                            <li><Link href="/mon-compte/commandes"  class="text-sm hover:text-white transition">Mes commandes</Link></li>
                            <li><Link href="/mon-compte/favoris"    class="text-sm hover:text-white transition">Mes favoris</Link></li>
                            <li><Link href="/panier"                class="text-sm hover:text-white transition">Mon panier</Link></li>
                            <li><Link href="/suivi-commande"        class="text-sm hover:text-white transition">Suivi commande</Link></li>
                        </ul>
                    </div>

                    <!-- Col 4 — Informations -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Informations</p>
                        <ul class="space-y-2.5">
                            <li><Link href="/legal/conditions-generales"        class="text-sm hover:text-white transition">CGV</Link></li>
                            <li><Link href="/legal/politique-de-confidentialite" class="text-sm hover:text-white transition">Confidentialité</Link></li>
                            <li><Link href="/legal/livraison"                   class="text-sm hover:text-white transition">Livraison & retours</Link></li>
                        </ul>

                        <!-- Contact rapide -->
                        <div class="mt-6 p-4 bg-slate-800 rounded-xl">
                            <p class="text-xs font-semibold text-white mb-2">Besoin d'aide ?</p>
                            <a
                                v-if="$page.props.settings?.social_whatsapp"
                                :href="`https://wa.me/${$page.props.settings.social_whatsapp?.replace(/\D/g, '')}?text=${encodeURIComponent('Bonjour, j\'ai besoin d\'aide.')}`"
                                target="_blank" rel="noopener"
                                class="flex items-center gap-2 text-xs text-green-400 hover:text-green-300 transition font-medium"
                            >
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Nous écrire sur WhatsApp
                            </a>
                            <a v-else href="/contact" class="text-xs text-slate-400 hover:text-white transition">Nous contacter →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="border-t border-slate-800">
                <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-600">
                    <p>© {{ new Date().getFullYear() }} {{ siteName }}. Tous droits réservés.</p>
                    <div class="flex items-center gap-4">
                        <Link href="/legal/conditions-generales" class="hover:text-slate-400 transition">CGV</Link>
                        <Link href="/legal/politique-de-confidentialite" class="hover:text-slate-400 transition">Confidentialité</Link>
                        <Link href="/legal/livraison" class="hover:text-slate-400 transition">Livraison</Link>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ───────────────────────────────────────────────────────────────── -->
        <!-- NOTIFICATIONS TOAST                                               -->
        <!-- ───────────────────────────────────────────────────────────────── -->
        <div class="fixed bottom-4 right-4 z-[70] space-y-2 max-w-sm w-full pointer-events-none px-4 sm:px-0">
            <TransitionGroup name="toast">
                <div
                    v-for="notification in notificationStore.notifications"
                    :key="notification.id"
                    class="pointer-events-auto bg-white border rounded-xl shadow-lg p-4 flex items-start gap-3"
                    :class="{
                        'border-green-200':  notification.type === 'success',
                        'border-red-200':    notification.type === 'error',
                        'border-amber-200':  notification.type === 'warning',
                        'border-slate-200':  notification.type === 'info',
                    }"
                >
                    <!-- Icône -->
                    <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center"
                        :class="{
                            'bg-green-50':  notification.type === 'success',
                            'bg-red-50':    notification.type === 'error',
                            'bg-amber-50':  notification.type === 'warning',
                            'bg-slate-100': notification.type === 'info',
                        }">
                        <svg v-if="notification.type === 'success'" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg v-else-if="notification.type === 'error'" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <svg v-else-if="notification.type === 'warning'" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <svg v-else class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="flex-1 text-sm text-slate-800 leading-relaxed">{{ notification.message }}</p>
                    <button @click="notificationStore.remove(notification.id)" class="text-slate-300 hover:text-slate-500 transition shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>

        <!-- Bottom Nav Mobile (front) -->
        <nav class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white border-t border-slate-200 safe-area-pb">
            <div class="flex items-stretch h-16">
                <Link href="/" class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                    :class="$page.url === '/' ? 'text-primary-600' : 'text-slate-500'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Accueil</span>
                </Link>
                <Link href="/boutique" class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                    :class="$page.url.startsWith('/boutique') ? 'text-primary-600' : 'text-slate-500'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Boutique</span>
                </Link>
                <Link href="/panier" class="flex flex-col items-center justify-center flex-1 gap-0.5 text-[10px] font-medium transition-colors"
                    :class="$page.url.startsWith('/panier') ? 'text-primary-600' : 'text-slate-500'">
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
                    :class="$page.url.startsWith('/mon-compte') || $page.url.startsWith('/connexion') ? 'text-primary-600' : 'text-slate-500'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Compte</span>
                </Link>
            </div>
        </nav>

        <ToastContainer />
        <ConfirmModal />
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(24px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(24px);
}

.search-overlay-enter-active,
.search-overlay-leave-active {
    transition: opacity 0.2s ease;
}
.search-overlay-enter-from,
.search-overlay-leave-to {
    opacity: 0;
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
