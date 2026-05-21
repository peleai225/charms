{{-- Bottom Navigation Mobile Type Application Native --}}
<nav
    x-data="mobileBottomNav()"
    x-cloak
    class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-t border-slate-200/60 shadow-2xl shadow-slate-900/10"
    style="padding-bottom: env(safe-area-inset-bottom);"
>
    <div class="grid grid-cols-5 h-16 px-2">
        {{-- Dashboard --}}
        <a
            href="{{ route('admin.dashboard') }}"
            @click="setActive('dashboard')"
            class="flex flex-col items-center justify-center gap-1 relative group transition-all duration-300 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'active-tab' : '' }}"
        >
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-xl scale-0 group-hover:scale-100 {{ request()->routeIs('admin.dashboard') ? 'scale-100' : '' }} transition-transform duration-300"></div>
                <svg class="w-6 h-6 relative transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 scale-110' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('admin.dashboard') ? '2.5' : '2' }}" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold tracking-tight transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 scale-105' : 'text-slate-500 group-hover:text-slate-700' }}">
                Dashboard
            </span>
            @if(request()->routeIs('admin.dashboard'))
                <div class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full"></div>
            @endif
        </a>

        {{-- Commandes --}}
        <a
            href="{{ route('admin.orders.index') }}"
            @click="setActive('orders')"
            class="flex flex-col items-center justify-center gap-1 relative group transition-all duration-300 rounded-xl {{ request()->routeIs('admin.orders.*') ? 'active-tab' : '' }}"
        >
            <div class="relative">
                @php $pendingOrders = \App\Models\Order::whereIn('status', ['pending', 'confirmed'])->count(); @endphp
                @if($pendingOrders > 0)
                    <span class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 text-[9px] font-extrabold text-white bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center ring-2 ring-white shadow-lg shadow-red-500/50 animate-pulse">
                        {{ $pendingOrders }}
                    </span>
                @endif
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/20 to-amber-500/20 rounded-xl scale-0 group-hover:scale-100 {{ request()->routeIs('admin.orders.*') ? 'scale-100' : '' }} transition-transform duration-300"></div>
                <svg class="w-6 h-6 relative transition-all duration-300 {{ request()->routeIs('admin.orders.*') ? 'text-orange-600 scale-110' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('admin.orders.*') ? '2.5' : '2' }}" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold tracking-tight transition-all duration-300 {{ request()->routeIs('admin.orders.*') ? 'text-orange-600 scale-105' : 'text-slate-500 group-hover:text-slate-700' }}">
                Commandes
            </span>
            @if(request()->routeIs('admin.orders.*'))
                <div class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-orange-600 to-amber-600 rounded-full"></div>
            @endif
        </a>

        {{-- Produits --}}
        <a
            href="{{ route('admin.products.index') }}"
            @click="setActive('products')"
            class="flex flex-col items-center justify-center gap-1 relative group transition-all duration-300 rounded-xl {{ request()->routeIs('admin.products.*') ? 'active-tab' : '' }}"
        >
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-500/20 rounded-xl scale-0 group-hover:scale-100 {{ request()->routeIs('admin.products.*') ? 'scale-100' : '' }} transition-transform duration-300"></div>
                <svg class="w-6 h-6 relative transition-all duration-300 {{ request()->routeIs('admin.products.*') ? 'text-emerald-600 scale-110' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('admin.products.*') ? '2.5' : '2' }}" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold tracking-tight transition-all duration-300 {{ request()->routeIs('admin.products.*') ? 'text-emerald-600 scale-105' : 'text-slate-500 group-hover:text-slate-700' }}">
                Produits
            </span>
            @if(request()->routeIs('admin.products.*'))
                <div class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-emerald-600 to-green-600 rounded-full"></div>
            @endif
        </a>

        {{-- Clients --}}
        <a
            href="{{ route('admin.customers.index') }}"
            @click="setActive('customers')"
            class="flex flex-col items-center justify-center gap-1 relative group transition-all duration-300 rounded-xl {{ request()->routeIs('admin.customers.*') ? 'active-tab' : '' }}"
        >
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-pink-500/20 to-rose-500/20 rounded-xl scale-0 group-hover:scale-100 {{ request()->routeIs('admin.customers.*') ? 'scale-100' : '' }} transition-transform duration-300"></div>
                <svg class="w-6 h-6 relative transition-all duration-300 {{ request()->routeIs('admin.customers.*') ? 'text-pink-600 scale-110' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('admin.customers.*') ? '2.5' : '2' }}" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold tracking-tight transition-all duration-300 {{ request()->routeIs('admin.customers.*') ? 'text-pink-600 scale-105' : 'text-slate-500 group-hover:text-slate-700' }}">
                Clients
            </span>
            @if(request()->routeIs('admin.customers.*'))
                <div class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-pink-600 to-rose-600 rounded-full"></div>
            @endif
        </a>

        {{-- Plus (Menu) --}}
        <button
            @click="toggleMenu()"
            class="flex flex-col items-center justify-center gap-1 relative group transition-all duration-300 rounded-xl"
            :class="menuOpen ? 'active-tab' : ''"
        >
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-indigo-500/20 rounded-xl scale-0 group-hover:scale-100 transition-transform duration-300"
                     :class="menuOpen ? 'scale-100' : ''"></div>
                <svg class="w-6 h-6 relative transition-all duration-300"
                     :class="menuOpen ? 'text-purple-600 scale-110 rotate-45' : 'text-slate-400 group-hover:text-slate-600'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold tracking-tight transition-all duration-300"
                  :class="menuOpen ? 'text-purple-600 scale-105' : 'text-slate-500 group-hover:text-slate-700'">
                Plus
            </span>
            <div x-show="menuOpen" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-full"></div>
        </button>
    </div>

    {{-- Haptic feedback effect --}}
    <div x-show="rippleEffect"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-100 scale-50"
         x-transition:enter-end="opacity-0 scale-150"
         class="absolute bg-blue-500/20 rounded-full pointer-events-none"
         :style="`left: ${rippleX}px; top: ${rippleY}px; width: 60px; height: 60px; transform: translate(-50%, -50%);`">
    </div>
</nav>

<script>
function mobileBottomNav() {
    return {
        menuOpen: false,
        rippleEffect: false,
        rippleX: 0,
        rippleY: 0,

        toggleMenu() {
            this.menuOpen = !this.menuOpen;
            this.$dispatch('mobile-menu-toggle', { open: this.menuOpen });

            // Haptic feedback (vibration légère)
            if ('vibrate' in navigator) {
                navigator.vibrate(10);
            }
        },

        setActive(tab) {
            this.menuOpen = false;

            // Haptic feedback
            if ('vibrate' in navigator) {
                navigator.vibrate(5);
            }
        },

        showRipple(e) {
            this.rippleX = e.clientX;
            this.rippleY = e.clientY;
            this.rippleEffect = true;
            setTimeout(() => this.rippleEffect = false, 600);
        }
    };
}
</script>

<style>
@keyframes tab-bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.active-tab {
    animation: tab-bounce 0.3s ease-out;
}

/* Safe area for iPhone notch */
@supports (padding: max(0px)) {
    nav {
        padding-bottom: max(env(safe-area-inset-bottom), 0px);
    }
}
</style>
