<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { useCartStore } from '@/Stores/cart';
import { useUserStore } from '@/Stores/user';
import { useNotificationStore } from '@/Stores/notifications';
import ToastContainer   from '@/Components/UI/ToastContainer.vue';
import ConfirmModal     from '@/Components/UI/ConfirmModal.vue';
import AdminBar         from '@/Components/Layout/AdminBar.vue';
import AnnouncementBar  from '@/Components/Layout/AnnouncementBar.vue';
import SearchOverlay    from '@/Components/Layout/SearchOverlay.vue';
import LayoutFooter     from '@/Components/Layout/LayoutFooter.vue';
import MobileBottomNav  from '@/Components/Layout/MobileBottomNav.vue';
import WhatsAppButton   from '@/Components/Layout/WhatsAppButton.vue';
import CartDrawer       from '@/Components/Layout/CartDrawer.vue';
import CategoryNavBar   from '@/Components/Layout/CategoryNavBar.vue';

const props = defineProps({ title: String });

const page              = usePage();
const cartStore         = useCartStore();
const userStore         = useUserStore();
const notificationStore = useNotificationStore();

const settings     = computed(() => page.props.settings || {});
const siteName     = computed(() => settings.value.site_name || 'Chamse');
const logoPath     = computed(() => settings.value.logo || null);
const primaryColor = computed(() => settings.value.primary_color || '#2563EB');

// ─── Sync panier ──────────────────────────────────────────────────────────────
watch(() => page.props.cart_count, (val) => {
    if (val !== undefined) {
        cartStore.setCount(val);
        if (val > 0 && cartStore.productIds.size === 0) cartStore.sync();
    }
}, { immediate: true });

const unsubNavigate = router.on('navigate', () => { cartStore.sync(); });
onUnmounted(() => unsubNavigate());

watch(() => page.props.auth?.user, (val) => {
    if (val) userStore.setUser(val);
    else     userStore.clearUser();
}, { immediate: true });

// ─── Scroll header compact ────────────────────────────────────────────────────
const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 60; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// ─── Menu mobile ─────────────────────────────────────────────────────────────
const mobileMenuOpen = ref(false);
const closeMobile    = () => { mobileMenuOpen.value = false; };
watch(() => page.url, closeMobile);

// ─── Recherche ───────────────────────────────────────────────────────────────
const searchOpen  = ref(false);
const searchQuery = ref('');
const submitSearch = () => {
    if (!searchQuery.value.trim()) return;
    router.get('/boutique', { search: searchQuery.value });
    searchOpen.value  = false;
    searchQuery.value = '';
};

// ─── Flash messages ───────────────────────────────────────────────────────────
const flash = computed(() => page.props.flash || {});
watch(flash, (f) => {
    if (f.success) notificationStore.success(f.success);
    if (f.error)   notificationStore.error(f.error);
}, { deep: true, immediate: true });

const navCategories = computed(() => page.props.nav_categories || []);
const banners       = computed(() => page.props.banners || { announcement: [], popup: null });

// ─── Mega menu ────────────────────────────────────────────────────────────────
const megaMenuOpen       = ref(false);
const activeMegaCategory = ref(null);
let   megaMenuTimer      = null;
const openMegaMenu = (cat) => { clearTimeout(megaMenuTimer); activeMegaCategory.value = cat; megaMenuOpen.value = true; };
const closeMegaMenu = () => {
    megaMenuTimer = setTimeout(() => { megaMenuOpen.value = false; activeMegaCategory.value = null; }, 150);
};
const keepMegaMenuOpen = () => { clearTimeout(megaMenuTimer); };

// ─── Dropdown compte ──────────────────────────────────────────────────────────
const accountDropdownOpen = ref(false);
let   accountDropdownTimer = null;
const openAccountDropdown  = () => { clearTimeout(accountDropdownTimer); accountDropdownOpen.value = true; };
const closeAccountDropdown = () => { accountDropdownTimer = setTimeout(() => { accountDropdownOpen.value = false; }, 120); };

watch(() => page.url, () => {
    megaMenuOpen.value = false; activeMegaCategory.value = null; accountDropdownOpen.value = false; closeMobile();
});

onUnmounted(() => { clearTimeout(megaMenuTimer); clearTimeout(accountDropdownTimer); });

// ─── Panier drawer ────────────────────────────────────────────────────────────
const cartDrawerOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col"
         :style="{
             '--color-primary-600': primaryColor,
             '--color-primary-700': `color-mix(in srgb, ${primaryColor} 85%, black)`,
         }">
        <Head :title="title ? `${title} — ${siteName}` : siteName" />

        <AdminBar />

        <AnnouncementBar
            :announcements="banners.announcement || []"
            :popup="banners.popup || null"
        />

        <!-- ─── HEADER ─────────────────────────────────────────────────────── -->
        <header class="sticky top-0 z-50 transition-shadow duration-200"
                :class="scrolled ? 'shadow-md' : ''">

            <!-- Ligne 1 : Logo · Recherche · Actions -->
            <div class="bg-white border-b border-slate-200">
                <div class="container mx-auto px-4">
                    <div class="flex items-center gap-3 h-16">

                        <!-- Logo -->
                        <Link href="/" class="flex items-center gap-2.5 shrink-0 mr-2">
                            <img v-if="logoPath" :src="`/storage/${logoPath}`" :alt="siteName" class="h-9 w-auto object-contain" />
                            <span v-else class="text-xl font-black text-slate-900 tracking-tight">{{ siteName }}</span>
                        </Link>

                        <!-- Barre de recherche (desktop) -->
                        <form @submit.prevent="submitSearch" class="hidden md:flex flex-1 max-w-2xl items-stretch h-10">
                            <input v-model="searchQuery" type="search"
                                   placeholder="Rechercher des produits, marques..."
                                   class="flex-1 px-4 text-sm border border-r-0 border-slate-300 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-slate-400"/>
                            <button type="submit"
                                    class="px-5 text-white rounded-r-lg flex items-center justify-center transition shrink-0 nav-primary-btn"
                                    :style="{ '--nav-primary': primaryColor }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                                </svg>
                            </button>
                        </form>

                        <!-- Actions droite -->
                        <div class="flex items-center gap-1 sm:gap-2 ml-auto md:ml-0">

                            <!-- Search trigger (mobile) -->
                            <button @click="searchOpen = true"
                                    class="md:hidden w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                    aria-label="Rechercher">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                                </svg>
                            </button>

                            <!-- Favoris -->
                            <Link v-if="userStore.isAuthenticated" href="/mon-compte/favoris"
                                  class="w-11 h-11 hidden sm:flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                  aria-label="Mes favoris">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </Link>

                            <!-- Panier (ouvre le drawer) -->
                            <button @click="cartDrawerOpen = true"
                                    class="relative w-11 h-11 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                    aria-label="Panier">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span v-if="cartStore.count > 0"
                                      class="absolute -top-1 -right-1 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-none"
                                      :style="{ backgroundColor: primaryColor }">
                                    {{ cartStore.count > 99 ? '99+' : cartStore.count }}
                                </span>
                            </button>

                            <!-- Connexion -->
                            <Link v-if="!userStore.isAuthenticated" href="/connexion"
                                  class="hidden sm:flex items-center gap-1.5 pl-2 pr-3 py-1.5 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="hidden md:block">Connexion</span>
                            </Link>

                            <!-- Dropdown compte (connecté) -->
                            <div v-else class="relative hidden sm:block"
                                 @mouseenter="openAccountDropdown" @mouseleave="closeAccountDropdown">
                                <button class="flex items-center gap-2 pl-1 pr-2 py-1.5 hover:bg-slate-100 rounded-lg transition">
                                    <div class="w-7 h-7 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ (userStore.user?.name || 'U')[0].toUpperCase() }}
                                    </div>
                                    <span class="hidden md:block text-sm font-medium text-slate-700 max-w-[90px] truncate">{{ userStore.user?.name }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <Transition name="dropdown">
                                    <div v-if="accountDropdownOpen"
                                         class="absolute right-0 top-full mt-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50"
                                         @mouseenter="openAccountDropdown" @mouseleave="closeAccountDropdown">
                                        <div class="px-4 py-3 border-b border-slate-100">
                                            <p class="text-sm font-semibold text-slate-900 truncate">{{ userStore.user?.name }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ userStore.user?.email }}</p>
                                        </div>
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
                                        <div class="border-t border-slate-100 p-2">
                                            <Link href="/deconnexion" method="post" as="button"
                                                  class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-50 transition group">
                                                <svg class="w-4 h-4 text-slate-400 group-hover:text-red-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                <span class="text-sm text-slate-600 group-hover:text-red-600 transition">Se déconnecter</span>
                                            </Link>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Burger mobile -->
                            <button @click="mobileMenuOpen = !mobileMenuOpen"
                                    class="lg:hidden w-11 h-11 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                    aria-label="Menu">
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
            </div>

            <!-- Ligne 2 : Barre catégories (desktop) + Mega menu -->
            <div class="hidden lg:block relative nav-catbar"
                 :style="{ '--nav-primary': primaryColor }"
                 @mouseleave="closeMegaMenu">
                <div class="container mx-auto px-4">
                    <div class="flex items-stretch h-10">
                        <button @mouseenter="navCategories.length && openMegaMenu(navCategories[0])"
                                class="flex items-center gap-2 px-4 h-full text-white text-sm font-semibold shrink-0 transition nav-allcat-btn"
                                :class="megaMenuOpen ? 'is-active' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            Toutes les catégories
                        </button>
                        <div class="flex items-stretch overflow-x-auto scrollbar-none flex-1">
                            <button v-for="cat in navCategories" :key="cat.id"
                                    @mouseenter="openMegaMenu(cat)"
                                    class="px-4 h-full text-white/90 text-sm font-medium hover:text-white transition shrink-0 whitespace-nowrap nav-cat-btn"
                                    :class="activeMegaCategory?.id === cat.id && megaMenuOpen ? 'is-active' : ''">
                                {{ cat.name }}
                            </button>
                        </div>
                        <div class="flex items-stretch nav-catbar-sep shrink-0">
                            <Link href="/"         class="px-4 h-full flex items-center text-white/80 text-sm hover:text-white transition whitespace-nowrap nav-cat-link">Accueil</Link>
                            <Link href="/a-propos" class="px-4 h-full flex items-center text-white/80 text-sm hover:text-white transition whitespace-nowrap nav-cat-link">À propos</Link>
                            <Link href="/contact"  class="px-4 h-full flex items-center text-white/80 text-sm hover:text-white transition whitespace-nowrap nav-cat-link">Contact</Link>
                        </div>
                    </div>
                </div>

                <!-- Panneau mega menu -->
                <Transition name="megamenu">
                    <div v-if="megaMenuOpen && activeMegaCategory"
                         class="absolute left-0 right-0 top-full bg-white shadow-2xl border-t-2 z-50"
                         :style="{ borderTopColor: primaryColor }"
                         @mouseenter="keepMegaMenuOpen" @mouseleave="closeMegaMenu">
                        <div class="container mx-auto px-4 py-6">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                                <h3 class="font-bold text-slate-900 text-base">{{ activeMegaCategory.name }}</h3>
                                <Link :href="`/categorie/${activeMegaCategory.slug}`"
                                      class="text-sm font-medium transition nav-primary-link"
                                      :style="{ color: primaryColor }">
                                    Voir tout →
                                </Link>
                            </div>
                            <div v-if="activeMegaCategory.children?.length"
                                 class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-x-8 gap-y-1">
                                <div v-for="child in activeMegaCategory.children" :key="child.id" class="mb-4">
                                    <Link :href="`/categorie/${child.slug}`"
                                          class="block font-semibold text-sm text-slate-800 mb-1.5 transition nav-mega-link">
                                        {{ child.name }}
                                    </Link>
                                    <div v-if="child.children?.length" class="space-y-0.5">
                                        <Link v-for="gc in child.children.slice(0, 5)" :key="gc.id"
                                              :href="`/categorie/${gc.slug}`"
                                              class="block text-xs text-slate-500 py-0.5 transition nav-mega-link">
                                            {{ gc.name }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-3">
                                <Link :href="`/categorie/${activeMegaCategory.slug}`"
                                      class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl transition nav-primary-btn"
                                      :style="{ '--nav-primary': primaryColor }">
                                    Voir les produits
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Menu mobile (slide-down) -->
            <div v-if="mobileMenuOpen" class="lg:hidden border-t border-slate-100 bg-white max-h-[80vh] overflow-y-auto">
                <div class="container mx-auto px-4 py-3">
                    <div class="space-y-0.5 mb-3">
                        <Link href="/"         class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition"
                              :class="$page.url === '/' ? 'bg-slate-50 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-50'">Accueil</Link>
                        <Link href="/boutique" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition"
                              :class="$page.url.startsWith('/boutique') ? 'bg-slate-50 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-50'">Boutique</Link>
                        <Link href="/a-propos" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition"
                              :class="$page.url === '/a-propos' ? 'bg-slate-50 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-50'">À propos</Link>
                        <Link href="/contact"  class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition"
                              :class="$page.url === '/contact' ? 'bg-slate-50 text-slate-900 font-semibold' : 'text-slate-600 hover:bg-slate-50'">Contact</Link>
                    </div>
                    <div v-if="navCategories.length" class="border-t border-slate-100 pt-3 mb-3">
                        <p class="text-xs font-semibold text-slate-400 px-3 mb-2">Catégories</p>
                        <div class="grid grid-cols-2 gap-1">
                            <Link v-for="cat in navCategories" :key="cat.id"
                                  :href="`/categorie/${cat.slug}`"
                                  class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition">
                                <div class="w-6 h-6 rounded-md bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                                    <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name" class="w-full h-full object-cover"/>
                                    <svg v-else class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                </div>
                                <span class="font-medium truncate">{{ cat.name }}</span>
                            </Link>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-2 space-y-0.5">
                        <Link :href="userStore.isAuthenticated ? '/mon-compte' : '/connexion'"
                              class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ userStore.isAuthenticated ? 'Mon compte' : 'Connexion' }}
                        </Link>
                        <Link v-if="userStore.isAuthenticated" href="/mon-compte/favoris"
                              class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Mes favoris
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <CategoryNavBar />

        <SearchOverlay
            v-model:open="searchOpen"
            v-model:query="searchQuery"
            @submit="submitSearch"
        />

        <!-- MAIN -->
        <main class="flex-1 pb-16 lg:pb-0">
            <slot />
        </main>

        <LayoutFooter />

        <MobileBottomNav />

        <ToastContainer />
        <ConfirmModal />

        <WhatsAppButton :phone="settings.social_whatsapp || ''" />

        <CartDrawer v-model:open="cartDrawerOpen" :primary-color="primaryColor" />
    </div>
</template>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from, .dropdown-leave-to {
    opacity: 0; transform: translateY(-6px);
}

.megamenu-enter-active, .megamenu-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.megamenu-enter-from, .megamenu-leave-to {
    opacity: 0; transform: translateY(-8px);
}

.scrollbar-none { scrollbar-width: none; -ms-overflow-style: none; }
.scrollbar-none::-webkit-scrollbar { display: none; }

/* Navbar couleur primaire dynamique */
.nav-catbar        { background-color: var(--nav-primary, #2563EB); }
.nav-catbar-sep    { border-left: 1px solid color-mix(in srgb, var(--nav-primary, #2563EB) 60%, white); }
.nav-allcat-btn    { background-color: color-mix(in srgb, var(--nav-primary, #2563EB) 80%, black); }
.nav-allcat-btn:hover, .nav-allcat-btn.is-active { background-color: color-mix(in srgb, var(--nav-primary, #2563EB) 65%, black); }
.nav-cat-btn:hover, .nav-cat-btn.is-active       { background-color: color-mix(in srgb, var(--nav-primary, #2563EB) 80%, black); }
.nav-cat-link:hover  { background-color: color-mix(in srgb, var(--nav-primary, #2563EB) 80%, black); }
.nav-primary-btn     { background-color: var(--nav-primary, #2563EB); }
.nav-primary-btn:hover { background-color: color-mix(in srgb, var(--nav-primary, #2563EB) 85%, black); }
.nav-mega-link:hover   { color: var(--nav-primary, #2563EB); }
.nav-primary-link:hover { opacity: 0.8; }
</style>
