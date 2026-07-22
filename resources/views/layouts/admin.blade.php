<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — {{ \App\Models\Setting::get('site_name', config('app.name')) }}</title>

    @php
        $siteLogo    = \App\Models\Setting::get('logo');
        $siteFavicon = \App\Models\Setting::get('favicon');
        $siteName    = \App\Models\Setting::get('site_name', config('app.name'));
        $pendingOrders = \Illuminate\Support\Facades\Cache::remember('admin_pending_orders_count', 60,
            fn() => \App\Models\Order::whereIn('status', ['pending', 'confirmed'])->count()
        );
        $stockAlerts = \Illuminate\Support\Facades\Cache::remember('admin_stock_alerts_count', 120,
            fn() => \App\Models\Product::active()->where('track_stock', true)->where(function($q) {
                $q->where('stock_quantity', 0)->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
            })->count()
        );
    @endphp

    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%232563EB' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-notifications.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        * { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .sidebar-scroll::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; }

        [x-cloak] { display: none !important; }

        /* Active nav item — left accent bar */
        .nav-active {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            position: relative;
        }
        .nav-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 3px;
            background: #2563eb;
            border-radius: 0 3px 3px 0;
        }

        .fade-in { animation: fadeIn .2s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 min-h-screen antialiased"
      x-data="{
          sidebarCollapsed: (() => { try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e) { return false } })(),
          mobileMenuOpen: false,
          searchOpen: false,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              try { localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed) } catch(e) {}
          }
      }">

    {{-- Toast notifications --}}
    <div x-data="notification" class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none">
        <template x-for="n in notifications" :key="n.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-6"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0 translate-x-6"
                 :class="{
                     'bg-white border-l-4 border-green-500': n.type === 'success',
                     'bg-white border-l-4 border-red-500':   n.type === 'error',
                     'bg-white border-l-4 border-amber-400': n.type === 'warning',
                     'bg-white border-l-4 border-blue-500':  n.type === 'info',
                 }"
                 class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg border border-gray-200 min-w-[300px]">
                <span x-text="n.message" class="flex-1 text-sm text-gray-800"></span>
                <button @click="remove(n.id)" class="text-gray-400 hover:text-gray-600 p-0.5 pointer-events-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <div class="min-h-screen flex">

        {{-- ===================== SIDEBAR ===================== --}}
        <aside class="fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-100 flex flex-col transition-all duration-300 shadow-sm lg:translate-x-0"
               :class="{
                   '-translate-x-full lg:translate-x-0': !mobileMenuOpen,
                   'translate-x-0': mobileMenuOpen,
                   'w-16': sidebarCollapsed && !mobileMenuOpen,
                   'w-60': !sidebarCollapsed || mobileMenuOpen,
               }">

            {{-- Logo --}}
            <div class="h-[60px] flex items-center border-b border-gray-100 flex-shrink-0"
                 :class="sidebarCollapsed ? 'justify-center px-3' : 'px-5'">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 min-w-0">
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}"
                             class="h-8 w-auto rounded-lg flex-shrink-0 object-contain">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                            {{ substr($siteName, 0, 1) }}
                        </div>
                    @endif
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate leading-tight">{{ $siteName }}</p>
                        <p class="text-[11px] text-gray-400 leading-tight">Administration</p>
                    </div>
                </a>
                <button @click="mobileMenuOpen = false" class="lg:hidden ml-auto text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto sidebar-scroll py-3 space-y-0.5" :class="sidebarCollapsed ? 'px-2' : 'px-3'">

                @php
                    $navItem = function(string $route, string $label, string $icon, string $routeMatch, ?int $badge = null) use ($sidebarCollapsed): string { return ''; };
                @endphp

                {{-- Helper macro --}}
                @php
                    function adminNavItem(string $routeName, string $label, string $svg, string $routeMatch, ?int $badge = null): array {
                        return compact('routeName','label','svg','routeMatch','badge');
                    }
                    $isActive = fn(string $pattern) => request()->routeIs($pattern);
                @endphp

                {{-- ── MAIN MENU ── --}}
                <p class="px-2 pt-1 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Menu principal</p>

                @php $navLinks = [
                    ['route' => 'admin.dashboard',   'label' => 'Dashboard',     'match' => 'admin.dashboard',   'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'badge' => null],
                ]; @endphp

                {{-- Dashboard --}}
                @include('layouts.admin-nav-item', [
                    'href'  => route('admin.dashboard'),
                    'label' => 'Dashboard',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                    'active' => request()->routeIs('admin.dashboard'),
                    'badge'  => null,
                    'tip'    => 'Dashboard',
                ])

                {{-- ── CATALOGUE ── --}}
                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Catalogue</p>

                @include('layouts.admin-nav-item', ['href' => route('admin.products.index'),   'label' => 'Produits',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',                                                                                                                                                                                                                           'active' => request()->routeIs('admin.products.*'),    'badge' => null, 'tip' => 'Produits'])
                @include('layouts.admin-nav-item', ['href' => route('admin.categories.index'), 'label' => 'Catégories',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',                                                                                                                                                              'active' => request()->routeIs('admin.categories.*'), 'badge' => null, 'tip' => 'Catégories'])
                @include('layouts.admin-nav-item', ['href' => route('admin.attributes.index'), 'label' => 'Attributs',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',                                                                                                                                                                                                                                                    'active' => request()->routeIs('admin.attributes.*'), 'badge' => null, 'tip' => 'Attributs'])
                @endif
                @include('layouts.admin-nav-item', ['href' => route('admin.barcodes.index'),  'label' => 'Codes-barres',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>', 'active' => request()->routeIs('admin.barcodes.*'),   'badge' => null, 'tip' => 'Codes-barres'])
                @include('layouts.admin-nav-item', ['href' => route('admin.scanner.index'),   'label' => 'Scanner / POS', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>',                                                                                                                                                   'active' => request()->routeIs('admin.scanner.*'),    'badge' => null, 'tip' => 'Scanner / POS'])

                {{-- ── VENTES ── --}}
                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Ventes</p>

                @include('layouts.admin-nav-item', ['href' => route('admin.orders.index'),    'label' => 'Commandes',         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>', 'active' => request()->routeIs('admin.orders.*'),    'badge' => $pendingOrders ?: null, 'tip' => 'Commandes'])
                @include('layouts.admin-nav-item', ['href' => route('admin.customers.index'), 'label' => 'Clients',           'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>', 'active' => request()->routeIs('admin.customers.*'), 'badge' => null, 'tip' => 'Clients'])
                @include('layouts.admin-nav-item', ['href' => route('admin.reviews.index'),   'label' => 'Avis clients',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>', 'active' => request()->routeIs('admin.reviews.*'), 'badge' => null, 'tip' => 'Avis clients'])
                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                @include('layouts.admin-nav-item', ['href' => route('admin.refunds.index'),   'label' => 'Remboursements',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>', 'active' => request()->routeIs('admin.refunds.*'),   'badge' => null, 'tip' => 'Remboursements'])
                @include('layouts.admin-nav-item', ['href' => route('admin.coupons.index'),   'label' => 'Codes promo',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>', 'active' => request()->routeIs('admin.coupons.*'),   'badge' => null, 'tip' => 'Codes promo'])
                @endif

                {{-- ── STOCK ── --}}
                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Stock</p>

                @include('layouts.admin-nav-item', ['href' => route('admin.stock.index'),     'label' => 'Gestion stock',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>', 'active' => request()->routeIs('admin.stock.*'),     'badge' => $stockAlerts ?: null, 'tip' => 'Stock'])
                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                @include('layouts.admin-nav-item', ['href' => route('admin.suppliers.index'), 'label' => 'Fournisseurs',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>', 'active' => request()->routeIs('admin.suppliers.*'), 'badge' => null, 'tip' => 'Fournisseurs'])
                @endif

                {{-- ── FINANCES ── --}}
                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Finances</p>

                @include('layouts.admin-nav-item', ['href' => route('admin.reports.index'),    'label' => 'Rapports',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>', 'active' => request()->routeIs('admin.reports.*'), 'badge' => null, 'tip' => 'Rapports'])
                @if(auth()->user()->role === 'admin')
                @include('layouts.admin-nav-item', ['href' => route('admin.accounting.index'), 'label' => 'Comptabilité', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>', 'active' => request()->routeIs('admin.accounting.*'), 'badge' => null, 'tip' => 'Comptabilité'])
                @endif

                {{-- ── CONTENU ── --}}
                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Contenu</p>

                @include('layouts.admin-nav-item', ['href' => route('admin.whatsapp.index'), 'label' => 'WhatsApp', 'icon' => '<path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>', 'active' => request()->routeIs('admin.whatsapp.*'), 'badge' => null, 'tip' => 'WhatsApp', 'fill' => true])
                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                @include('layouts.admin-nav-item', ['href' => route('admin.banners.index'),       'label' => 'Bannières',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'active' => request()->routeIs('admin.banners.*'),   'badge' => null, 'tip' => 'Bannières'])
                @include('layouts.admin-nav-item', ['href' => route('admin.dropshipping.index'),  'label' => 'Dropshipping', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>', 'active' => request()->routeIs('admin.dropshipping.*'), 'badge' => null, 'tip' => 'Dropshipping'])
                @endif

                {{-- ── CONFIGURATION (admin) ── --}}
                @if(auth()->user()->role === 'admin')
                <p class="px-2 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest"
                   x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Configuration</p>

                @include('layouts.admin-nav-item', ['href' => route('admin.users.index'),        'label' => 'Utilisateurs',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>', 'active' => request()->routeIs('admin.users.*'),    'badge' => null, 'tip' => 'Utilisateurs'])
                @include('layouts.admin-nav-item', ['href' => route('admin.import-export.index'),'label' => 'Import / Export', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>',                                                                                                                                                                                                                    'active' => request()->routeIs('admin.import-export.*'), 'badge' => null, 'tip' => 'Import / Export'])
                @include('layouts.admin-nav-item', ['href' => route('admin.settings.index'),     'label' => 'Paramètres',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',  'active' => request()->routeIs('admin.settings.*'),  'badge' => null, 'tip' => 'Paramètres'])
                @include('layouts.admin-nav-item', ['href' => route('admin.system.index'),       'label' => 'Système',        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/>',                                                                                                                                                                                                                                                        'active' => request()->routeIs('admin.system.*'),    'badge' => null, 'tip' => 'Système'])
                @endif

                <div class="h-4"></div>
            </nav>

            {{-- Sidebar toggle (desktop) --}}
            <div class="hidden lg:flex border-t border-gray-100 h-12 items-center flex-shrink-0"
                 :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                <button @click="toggleSidebar()"
                        class="flex items-center gap-2 text-gray-400 hover:text-gray-700 text-xs font-medium transition-colors w-full"
                        :class="sidebarCollapsed ? 'justify-center' : ''">
                    <svg class="w-4 h-4 transition-transform duration-300 flex-shrink-0"
                         :class="sidebarCollapsed ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms>Réduire</span>
                </button>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" x-cloak
             class="fixed inset-0 bg-black/40 z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- ===================== MAIN CONTENT ===================== --}}
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-60'">

            {{-- Header --}}
            <header class="bg-white border-b border-gray-100 sticky top-0 z-30 flex-shrink-0">
                <div class="h-[60px] flex items-center gap-3 px-4 lg:px-6">

                    {{-- Mobile menu --}}
                    <button @click="mobileMenuOpen = true"
                            class="lg:hidden p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Page title --}}
                    <div class="min-w-0">
                        <h1 class="text-base font-semibold text-gray-900 leading-tight truncate">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        @hasSection('breadcrumbs')
                            <div class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                @yield('breadcrumbs')
                            </div>
                        @endif
                    </div>

                    {{-- Search --}}
                    <div class="hidden md:flex flex-1 max-w-xs mx-4">
                        <button @click="searchOpen = true"
                                class="w-full flex items-center gap-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-400 transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Rechercher…</span>
                            <kbd class="ml-auto text-[10px] text-gray-300 bg-white border border-gray-200 px-1.5 py-0.5 rounded font-medium">⌘K</kbd>
                        </button>
                    </div>

                    <div class="flex items-center gap-1 ml-auto">
                        {{-- Voir le site --}}
                        <a href="{{ route('home') }}" target="_blank"
                           class="hidden md:flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 px-3 py-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span>Site</span>
                        </a>

                        {{-- Son --}}
                        <button x-data="{
                                    on: true,
                                    init() { try { this.on = localStorage.getItem('admin_sound_enabled') !== 'false'; } catch(e) {} },
                                    toggle() {
                                        this.on = !this.on;
                                        try { localStorage.setItem('admin_sound_enabled', this.on); } catch(e) {}
                                        if (window.adminToggleSound) window.adminToggleSound();
                                    }
                                }"
                                @click="toggle()"
                                class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                :title="on ? 'Son activé' : 'Son désactivé'">
                            <svg x-show="on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                            </svg>
                            <svg x-show="!on" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                            </svg>
                        </button>

                        {{-- Notification bell --}}
                        <div class="relative" x-data="{
                                open: false,
                                orders: [],
                                loading: false,
                                async load() {
                                    this.loading = true;
                                    try {
                                        const r = await fetch('/api/admin/poll-stats', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                                        const d = await r.json();
                                        this.orders = d.pending_order_list || [];
                                    } catch(e) {}
                                    this.loading = false;
                                }
                             }" @open-bell.window="open = true; load()">
                            <button @click="open = !open; if(open) load()"
                                    class="relative p-2 rounded-lg transition-colors"
                                    :class="{{ $pendingOrders > 0 ? 'true' : 'false' }} || orders.length > 0
                                        ? 'text-orange-500 bg-orange-50 hover:bg-orange-100'
                                        : 'text-gray-400 hover:text-gray-700 hover:bg-gray-100'"
                                    id="notification-bell-btn">
                                <svg class="w-4 h-4" id="notification-bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span id="notification-count-badge"
                                      data-notification-dot
                                      class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center ring-2 ring-white {{ $pendingOrders > 0 ? '' : 'hidden' }}">
                                    {{ $pendingOrders > 0 ? $pendingOrders : '' }}
                                </span>
                            </button>

                            {{-- Dropdown notifications --}}
                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-800">Commandes en attente</p>
                                    <span class="text-xs text-gray-400" x-text="orders.length + ' commande(s)'"></span>
                                </div>
                                <div class="max-h-72 overflow-y-auto">
                                    <template x-if="loading">
                                        <div class="py-6 text-center text-sm text-gray-400">Chargement…</div>
                                    </template>
                                    <template x-if="!loading && orders.length === 0">
                                        <div class="py-6 text-center text-sm text-gray-400">Aucune commande en attente</div>
                                    </template>
                                    <template x-for="o in orders" :key="o.id">
                                        <a :href="'/admin/orders/' + o.id"
                                           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                                            <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-xs font-bold text-orange-600 flex-shrink-0"
                                                 x-text="(o.customer_name || 'IN').substring(0,2).toUpperCase()"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-800 truncate" x-text="o.order_number"></p>
                                                <p class="text-xs text-gray-400 truncate" x-text="o.customer_name || 'Client inconnu'"></p>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-700 flex-shrink-0" x-text="o.total_fmt"></span>
                                        </a>
                                    </template>
                                </div>
                                <div class="px-4 py-3 border-t border-gray-100">
                                    <a href="{{ route('admin.orders.index') }}" class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                                        Voir toutes les commandes →
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- User menu --}}
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-xs font-semibold text-gray-800 leading-tight">{{ Auth::user()->name ?? 'Admin' }}</p>
                                    <p class="text-[10px] text-gray-400 leading-tight capitalize">{{ Auth::user()->role ?? 'admin' }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                                <a href="{{ route('admin.profile.edit') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon profil
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Search palette --}}
    <div x-show="searchOpen" @keydown.escape.window="searchOpen = false" x-cloak
         class="fixed inset-0 z-[100] flex items-start justify-center pt-[10vh]"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="searchOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100"
             x-data="adminSearch()" @click.away="searchOpen = false">
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       x-ref="searchInput"
                       x-model="query"
                       @input.debounce.250ms="search()"
                       x-init="$nextTick(() => $refs.searchInput.focus())"
                       placeholder="Rechercher une commande, un produit, un client…"
                       class="flex-1 text-sm text-gray-800 placeholder-gray-400 outline-none bg-transparent">
                <kbd class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded font-medium">Esc</kbd>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <template x-if="loading">
                    <div class="py-8 text-center text-sm text-gray-400">Recherche…</div>
                </template>
                <template x-if="!loading && results.length === 0 && query.length > 1">
                    <div class="py-8 text-center text-sm text-gray-400">Aucun résultat pour "{{ '${query}' }}"</div>
                </template>
                <template x-if="!loading && results.length === 0 && query.length <= 1">
                    <div class="py-6 px-4 text-sm text-gray-400 space-y-1">
                        <p class="font-medium text-gray-500 mb-3">Accès rapide</p>
                        <a href="{{ route('admin.orders.index') }}" @click="searchOpen=false" class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Commandes
                        </a>
                        <a href="{{ route('admin.products.index') }}" @click="searchOpen=false" class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Produits
                        </a>
                        <a href="{{ route('admin.customers.index') }}" @click="searchOpen=false" class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Clients
                        </a>
                    </div>
                </template>
                <template x-for="r in results" :key="r.id + r.type">
                    <a :href="r.url" @click="searchOpen = false"
                       class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 text-xs font-bold"
                             x-text="r.type === 'order' ? '#' : r.type === 'product' ? 'P' : 'C'"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="r.title"></p>
                            <p class="text-xs text-gray-400 truncate" x-text="r.subtitle"></p>
                        </div>
                        <span class="text-xs text-gray-300 capitalize flex-shrink-0" x-text="r.type"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
    // Ctrl+K shortcut
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const main = document.querySelector('[x-data]')?.__x?.$data;
            if (main) main.searchOpen = true;
        }
    });

    function adminSearch() {
        return {
            query: '',
            results: [],
            loading: false,
            async search() {
                if (this.query.length < 2) { this.results = []; return; }
                this.loading = true;
                try {
                    const r = await fetch('/api/admin/search?q=' + encodeURIComponent(this.query), {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (r.ok) this.results = await r.json();
                } catch(e) {}
                this.loading = false;
            }
        };
    }
    </script>
</body>
</html>
