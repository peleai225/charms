{{-- Bottom Navigation Mobile Type Application Native iOS/Shopify --}}
<nav
    x-data="mobileBottomNav()"
    x-cloak
    class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/85 backdrop-blur-2xl backdrop-saturate-150 border-t border-slate-900/[0.08] shadow-[0_-2px_16px_rgba(0,0,0,0.08)]"
    style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 0px);"
>
    <div class="grid grid-cols-5 px-1 safe-area-wrapper">
        {{-- Dashboard --}}
        <a
            href="{{ route('admin.dashboard') }}"
            @click="setActive('dashboard')"
            @touchstart="tapFeedback($event)"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        >
            <div class="nav-icon-wrapper">
                <svg class="nav-icon {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-400' }}"
                     fill="{{ request()->routeIs('admin.dashboard') ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24"
                     stroke-width="{{ request()->routeIs('admin.dashboard') ? '0' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <span class="nav-label {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-500' }}">
                Accueil
            </span>
        </a>

        {{-- Commandes --}}
        <a
            href="{{ route('admin.orders.index') }}"
            @click="setActive('orders')"
            @touchstart="tapFeedback($event)"
            class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
        >
            <div class="nav-icon-wrapper">
                @php $pendingOrders = \App\Models\Order::whereIn('status', ['pending', 'confirmed'])->count(); @endphp
                @if($pendingOrders > 0)
                    <span class="nav-badge">
                        {{ $pendingOrders > 9 ? '9+' : $pendingOrders }}
                    </span>
                @endif
                <svg class="nav-icon {{ request()->routeIs('admin.orders.*') ? 'text-orange-600' : 'text-slate-400' }}"
                     fill="{{ request()->routeIs('admin.orders.*') ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24"
                     stroke-width="{{ request()->routeIs('admin.orders.*') ? '0' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <span class="nav-label {{ request()->routeIs('admin.orders.*') ? 'text-orange-600' : 'text-slate-500' }}">
                Commandes
            </span>
        </a>

        {{-- Produits --}}
        <a
            href="{{ route('admin.products.index') }}"
            @click="setActive('products')"
            @touchstart="tapFeedback($event)"
            class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
        >
            <div class="nav-icon-wrapper">
                <svg class="nav-icon {{ request()->routeIs('admin.products.*') ? 'text-emerald-600' : 'text-slate-400' }}"
                     fill="{{ request()->routeIs('admin.products.*') ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24"
                     stroke-width="{{ request()->routeIs('admin.products.*') ? '0' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="nav-label {{ request()->routeIs('admin.products.*') ? 'text-emerald-600' : 'text-slate-500' }}">
                Produits
            </span>
        </a>

        {{-- Clients --}}
        <a
            href="{{ route('admin.customers.index') }}"
            @click="setActive('customers')"
            @touchstart="tapFeedback($event)"
            class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
        >
            <div class="nav-icon-wrapper">
                <svg class="nav-icon {{ request()->routeIs('admin.customers.*') ? 'text-pink-600' : 'text-slate-400' }}"
                     fill="{{ request()->routeIs('admin.customers.*') ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24"
                     stroke-width="{{ request()->routeIs('admin.customers.*') ? '0' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <span class="nav-label {{ request()->routeIs('admin.customers.*') ? 'text-pink-600' : 'text-slate-500' }}">
                Clients
            </span>
        </a>

        {{-- Plus (Menu) --}}
        <button
            @click="toggleMenu()"
            @touchstart="tapFeedback($event)"
            class="nav-item"
            :class="menuOpen ? 'active' : ''"
        >
            <div class="nav-icon-wrapper">
                <svg class="nav-icon transition-transform duration-200"
                     :class="[menuOpen ? 'text-purple-600 rotate-90' : 'text-slate-400']"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                    <circle cx="12" cy="5" r="1.5" fill="currentColor"/>
                    <circle cx="12" cy="19" r="1.5" fill="currentColor"/>
                </svg>
            </div>
            <span class="nav-label" :class="menuOpen ? 'text-purple-600' : 'text-slate-500'">
                Plus
            </span>
        </button>
    </div>

</nav>

<script>
function mobileBottomNav() {
    return {
        menuOpen: false,

        toggleMenu() {
            this.menuOpen = !this.menuOpen;
            this.$dispatch('mobile-menu-toggle', { open: this.menuOpen });
            this.hapticFeedback();
        },

        setActive(tab) {
            this.menuOpen = false;
            this.hapticFeedback(5);
        },

        tapFeedback(e) {
            this.hapticFeedback(3);
            const target = e.currentTarget;
            target.style.transform = 'scale(0.95)';
            setTimeout(() => {
                target.style.transform = '';
            }, 150);
        },

        hapticFeedback(duration = 10) {
            if ('vibrate' in navigator) {
                navigator.vibrate(duration);
            }
        }
    };
}
</script>

<style>
/* ===== BOTTOM NAV PREMIUM iOS/SHOPIFY STYLE ===== */

/* Container avec safe area optimisée */
.safe-area-wrapper {
    height: 56px;
    padding-top: 4px;
    padding-bottom: 4px;
}

/* Style base des items */
.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    position: relative;
    padding: 6px 4px;
    border-radius: 12px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
    user-select: none;
}

.nav-item:active {
    transform: scale(0.95);
}

/* Wrapper icône */
.nav-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
}

/* Icône */
.nav-icon {
    width: 24px;
    height: 24px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-item.active .nav-icon {
    transform: scale(1.08);
}

/* Label */
.nav-label {
    font-size: 10px;
    font-weight: 600;
    line-height: 1;
    letter-spacing: -0.01em;
    transition: color 0.2s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 60px;
}

/* Badge notifications */
.nav-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
    color: white;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-radius: 8px;
    border: 1.5px solid white;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    z-index: 10;
    animation: badge-pulse 2s ease-in-out infinite;
}

/* Badge pulse animation */
@keyframes badge-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Active state subtle background with smooth transition */
.nav-item.active {
    background: radial-gradient(circle at center, rgba(0, 0, 0, 0.03) 0%, transparent 70%);
}

/* Active indicator dot */
.nav-item.active::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.8;
}

/* Touch optimization */
@media (hover: none) and (pointer: coarse) {
    .nav-item {
        min-width: 60px;
        min-height: 48px;
    }
}

/* Safe area support complet */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    nav {
        padding-bottom: env(safe-area-inset-bottom);
    }

    .safe-area-wrapper {
        padding-bottom: max(4px, env(safe-area-inset-bottom));
    }
}

/* iOS Safari bottom bar overlap fix */
@supports (-webkit-touch-callout: none) {
    nav {
        padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);
    }
}

/* Prevent text selection and callouts on iOS */
nav * {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

/* Smooth hardware acceleration */
nav {
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    will-change: transform;
}

/* Enhanced backdrop blur for modern devices */
@supports (backdrop-filter: blur(20px)) {
    nav {
        backdrop-filter: blur(20px) saturate(150%);
        -webkit-backdrop-filter: blur(20px) saturate(150%);
    }
}

/* Prevent layout shift */
nav {
    contain: layout style paint;
}

/* Smooth transitions */
.nav-item, .nav-icon, .nav-label {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
