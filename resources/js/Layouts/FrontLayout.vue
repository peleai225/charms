<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { Search, Menu, X } from 'lucide-vue-next';
import { useCartStore } from '@/Stores/cart';
import { useUserStore } from '@/Stores/user';
import { useNotificationStore } from '@/Stores/notifications';
import ToastContainer    from '@/Components/UI/ToastContainer.vue';
import ConfirmModal      from '@/Components/UI/ConfirmModal.vue';
import AdminBar          from '@/Components/Layout/AdminBar.vue';
import AnnouncementBar   from '@/Components/Layout/AnnouncementBar.vue';
import SearchOverlay     from '@/Components/Layout/SearchOverlay.vue';
import LayoutFooter      from '@/Components/Layout/LayoutFooter.vue';
import MobileBottomNav   from '@/Components/Layout/MobileBottomNav.vue';
import WhatsAppButton    from '@/Components/Layout/WhatsAppButton.vue';
import CartDrawer        from '@/Components/Layout/CartDrawer.vue';
import CategoryNavBar    from '@/Components/Layout/CategoryNavBar.vue';
import HeaderShortcuts   from '@/Components/Layout/HeaderShortcuts.vue';
import HeaderNavBar      from '@/Components/Layout/HeaderNavBar.vue';
import MobileMenu        from '@/Components/Layout/MobileMenu.vue';
import ImageSearchButton from '@/Components/Shop/ImageSearchButton.vue';

const props = defineProps({ title: String });

const page              = usePage();
const cartStore         = useCartStore();
const userStore         = useUserStore();
const notificationStore = useNotificationStore();

const settings = computed(() => page.props.settings || {});
const siteName = computed(() => settings.value.site_name || 'Chamse');
const logoPath = computed(() => settings.value.logo || null);

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

// ─── Ombre de l'en-tête au défilement ─────────────────────────────────────────
const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 60; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// ─── Menu téléphone ───────────────────────────────────────────────────────────
const mobileMenuOpen = ref(false);
watch(() => page.url, () => { mobileMenuOpen.value = false; });

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

const banners = computed(() => page.props.banners || { announcement: [], popup: null });

// ─── Panier (tiroir) ──────────────────────────────────────────────────────────
const cartDrawerOpen = ref(false);

const iconButton = 'w-11 h-11 flex items-center justify-center rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition';
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col">
        <Head :title="title ? `${title} — ${siteName}` : siteName" />

        <AdminBar />

        <AnnouncementBar
            :announcements="banners.announcement || []"
            :popup="banners.popup || null"
        />

        <!-- ─── EN-TÊTE ────────────────────────────────────────────────────── -->
        <header class="sticky top-0 z-50 bg-white border-b border-slate-200 transition-shadow duration-200"
                :class="scrolled ? 'shadow-md' : ''">

            <!-- Ligne 1 : logo · catégories · recherche · raccourcis -->
            <div class="container mx-auto px-4">
                <div class="flex items-center gap-2 lg:gap-3 h-16 lg:h-[72px]">

                    <Link href="/" class="flex items-center shrink-0 min-w-0 mr-1 lg:mr-2">
                        <img v-if="logoPath" :src="`/storage/${logoPath}`" :alt="siteName" class="h-9 lg:h-10 w-auto max-w-[9rem] sm:max-w-[11rem] object-contain" />
                        <span v-else class="text-lg lg:text-xl font-black text-slate-900 tracking-tight truncate">{{ siteName }}</span>
                    </Link>

                    <!-- Catégories (ordinateur) -->
                    <div class="hidden lg:block shrink-0">
                        <CategoryNavBar />
                    </div>

                    <!-- Recherche (tablette et ordinateur) -->
                    <form role="search" class="hidden md:flex flex-1 min-w-0 items-stretch h-11" @submit.prevent="submitSearch">
                        <input v-model="searchQuery" type="search" aria-label="Rechercher"
                               placeholder="Rechercher des produits, marques..."
                               class="flex-1 min-w-0 px-4 text-sm border border-r-0 border-slate-300 rounded-l-lg focus:outline-none focus:border-primary-600 focus:ring-1 focus:ring-primary-600"/>
                        <ImageSearchButton button-class="h-full px-3 flex items-center border-y border-slate-300 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition" />
                        <button type="submit" aria-label="Lancer la recherche"
                                class="px-5 rounded-r-lg flex items-center justify-center shrink-0 bg-primary-600 text-primary-contrast hover:bg-primary-700 transition">
                            <Search class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </form>

                    <div class="flex items-center gap-0.5 ml-auto md:ml-0 shrink-0">
                        <!-- Recherche (téléphone) -->
                        <ImageSearchButton :button-class="`md:hidden ${iconButton}`" />
                        <button type="button" :class="['md:hidden', iconButton]" aria-label="Rechercher" @click="searchOpen = true">
                            <Search class="w-5 h-5" aria-hidden="true" />
                        </button>

                        <HeaderShortcuts @open-cart="cartDrawerOpen = true" />

                        <!-- Menu (téléphone et tablette) -->
                        <button type="button" :class="['lg:hidden', iconButton]" :aria-expanded="String(mobileMenuOpen)"
                                :aria-label="mobileMenuOpen ? 'Fermer le menu' : 'Ouvrir le menu'" @click="mobileMenuOpen = !mobileMenuOpen">
                            <X v-if="mobileMenuOpen" class="w-5 h-5" aria-hidden="true" />
                            <Menu v-else class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ligne 2 : rubriques (tablette et ordinateur) -->
            <HeaderNavBar />

            <MobileMenu :open="mobileMenuOpen" />
        </header>

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

        <CartDrawer v-model:open="cartDrawerOpen" :primary-color="settings.primary_color" />
    </div>
</template>
