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

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-notifications.js', 'resources/js/admin-charts.js'])

    <style>
        [x-cloak] { display: none !important; }

        /* Active nav — orange left bar, like Uxerflow */
        .nav-active {
            color: #111827;
            font-weight: 600;
            position: relative;
        }
        .nav-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            height: 70%;
            width: 3px;
            background: #f97316;
            border-radius: 0 2px 2px 0;
        }

        /* Thin scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 99px; }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100 min-h-screen antialiased text-[13px]"
      x-data="{
          sidebarCollapsed: (() => { try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e) { return false } })(),
          mobileOpen: false,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              try { localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed) } catch(e) {}
          }
      }">

    {{-- Toast --}}
    <div x-data="notification" class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none">
        <template x-for="n in notifications" :key="n.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 :class="{
                     'border-l-4 border-green-500': n.type==='success',
                     'border-l-4 border-red-500':   n.type==='error',
                     'border-l-4 border-amber-400': n.type==='warning',
                     'border-l-4 border-blue-500':  n.type==='info',
                 }"
                 class="pointer-events-auto flex items-center gap-3 px-4 py-3 bg-white rounded-lg shadow-lg min-w-[280px] border border-gray-100">
                <span x-text="n.message" class="flex-1 text-gray-700"></span>
                <button @click="remove(n.id)" class="text-gray-300 hover:text-gray-500">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    <div class="flex h-screen overflow-hidden">

        {{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
        <aside class="flex flex-col bg-white border-r border-gray-200 flex-shrink-0 transition-all duration-200 z-50"
               :class="sidebarCollapsed ? 'w-[56px]' : 'w-[220px]'">

            {{-- Logo --}}
            <div class="flex items-center h-[56px] border-b border-gray-100 flex-shrink-0 overflow-hidden"
                 :class="sidebarCollapsed ? 'justify-center px-0' : 'px-5 gap-3'">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}"
                         class="h-7 w-7 rounded-md object-contain flex-shrink-0">
                @else
                    <div class="w-7 h-7 rounded-md bg-orange-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ substr($siteName, 0, 1) }}
                    </div>
                @endif
                <div x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="min-w-0 overflow-hidden">
                    <p class="text-[13px] font-bold text-gray-900 truncate leading-none">{{ $siteName }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5 leading-none">Administration</p>
                </div>
                <button @click="toggleSidebar()" x-show="!sidebarCollapsed" x-cloak
                        class="ml-auto text-gray-300 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Expand button when collapsed --}}
            <div x-show="sidebarCollapsed" x-cloak class="flex justify-center py-2 border-b border-gray-100">
                <button @click="toggleSidebar()" class="text-gray-300 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3">

                {{-- MAIN MENU --}}
                <div x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms
                     class="px-4 mb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Main Menu</div>

                @include('layouts.admin-nav-item', ['href' => route('admin.dashboard'),   'label' => 'Dashboard',  'match' => 'admin.dashboard',   'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'badge' => null])
                @include('layouts.admin-nav-item', ['href' => route('admin.orders.index'),    'label' => 'Commandes', 'match' => 'admin.orders.*',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'badge' => $pendingOrders ?: null])
                @include('layouts.admin-nav-item', ['href' => route('admin.customers.index'), 'label' => 'Clients',   'match' => 'admin.customers.*', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'badge' => null])

                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    {{-- CATALOGUE --}}
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms
                         class="px-4 mt-4 mb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Catalogue</div>
                    <div x-show="sidebarCollapsed" class="border-t border-gray-100 my-2 mx-3"></div>

                    @include('layouts.admin-nav-item', ['href' => route('admin.products.index'),   'label' => 'Produits',    'match' => 'admin.products.*',    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.categories.index'), 'label' => 'Catégories',  'match' => 'admin.categories.*',  'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.attributes.index'), 'label' => 'Attributs',   'match' => 'admin.attributes.*',  'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.coupons.index'),    'label' => 'Codes promo', 'match' => 'admin.coupons.*',     'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'badge' => null])
                @endif

                {{-- TOOLS --}}
                <div x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms
                     class="px-4 mt-4 mb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Tools</div>
                <div x-show="sidebarCollapsed" class="border-t border-gray-100 my-2 mx-3"></div>

                @include('layouts.admin-nav-item', ['href' => route('admin.stock.index'),   'label' => 'Stock',       'match' => 'admin.stock.*',     'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4', 'badge' => $stockAlerts ?: null])
                @include('layouts.admin-nav-item', ['href' => route('admin.reports.index'), 'label' => 'Rapports',    'match' => 'admin.reports.*',   'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'badge' => null])
                @include('layouts.admin-nav-item', ['href' => route('admin.scanner.index'), 'label' => 'Scanner/POS', 'match' => 'admin.scanner.*',   'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z', 'badge' => null])
                @include('layouts.admin-nav-item', ['href' => route('admin.reviews.index'), 'label' => 'Avis',        'match' => 'admin.reviews.*',   'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'badge' => null])

                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    @include('layouts.admin-nav-item', ['href' => route('admin.suppliers.index'),    'label' => 'Fournisseurs',   'match' => 'admin.suppliers.*',    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.banners.index'),      'label' => 'Bannières',      'match' => 'admin.banners.*',      'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.refunds.index'),      'label' => 'Remboursements', 'match' => 'admin.refunds.*',      'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.dropshipping.index'), 'label' => 'Dropshipping',   'match' => 'admin.dropshipping.*', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', 'badge' => null])
                @endif

                {{-- WORKSPACE (admin) --}}
                @if(auth()->user()->role === 'admin')
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms
                         class="px-4 mt-4 mb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Workspace</div>
                    <div x-show="sidebarCollapsed" class="border-t border-gray-100 my-2 mx-3"></div>

                    @include('layouts.admin-nav-item', ['href' => route('admin.accounting.index'),   'label' => 'Comptabilité',   'match' => 'admin.accounting.*',   'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.users.index'),        'label' => 'Utilisateurs',   'match' => 'admin.users.*',        'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.import-export.index'),'label' => 'Import/Export',  'match' => 'admin.import-export.*','icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.whatsapp.index'),     'label' => 'WhatsApp',       'match' => 'admin.whatsapp.*',     'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'badge' => null])
                @endif

                <div class="h-4"></div>
            </nav>

            {{-- Bottom: settings --}}
            <div class="border-t border-gray-100 py-2">
                @if(auth()->user()->role === 'admin')
                    @include('layouts.admin-nav-item', ['href' => route('admin.settings.index'), 'label' => 'Paramètres', 'match' => 'admin.settings.*', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'badge' => null])
                    @include('layouts.admin-nav-item', ['href' => route('admin.system.index'),   'label' => 'Système',    'match' => 'admin.system.*',   'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'badge' => null])
                @endif

                {{-- Profile --}}
                <div class="mx-2 mt-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div x-show="!sidebarCollapsed" x-transition.opacity.duration.100ms class="min-w-0">
                            <p class="text-[12px] font-semibold text-gray-800 truncate leading-none">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5 capitalize leading-none">{{ Auth::user()->role ?? 'admin' }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </aside>

        {{-- ═══════════════════════ MAIN ═══════════════════════ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Header --}}
            <header class="h-[56px] bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Mobile menu --}}
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-500 hover:text-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-[15px] font-semibold text-gray-900 truncate">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Search --}}
                    <div class="hidden md:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 w-48">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="text-[12px] text-gray-400">Search here</span>
                    </div>

                    {{-- Voir le site --}}
                    <a href="{{ route('home') }}" target="_blank"
                       class="hidden md:flex items-center gap-1.5 px-3 py-1.5 text-[12px] text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-lg border border-gray-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Site
                    </a>

                    {{-- Bell --}}
                    <div class="relative" x-data="{
                            open: false, orders: [], loading: false,
                            async load() {
                                this.loading = true;
                                try { const r = await fetch('/api/admin/poll-stats', {credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}); const d = await r.json(); this.orders = d.pending_order_list || []; } catch(e) {}
                                this.loading = false;
                            }
                         }" @open-bell.window="open=true;load()">
                        <button @click="open=!open; if(open) load()"
                                class="relative p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors"
                                id="notification-bell-btn">
                            <svg class="w-4 h-4" id="notification-bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span id="notification-count-badge" data-notification-dot
                                  class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center ring-2 ring-white {{ $pendingOrders > 0 ? '' : 'hidden' }}">
                                {{ $pendingOrders > 0 ? $pendingOrders : '' }}
                            </span>
                        </button>
                        <div x-show="open" @click.away="open=false" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-1 w-72 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <p class="text-[12px] font-semibold text-gray-800">Commandes en attente</p>
                                <span class="text-[11px] text-gray-400" x-text="orders.length + ' commande(s)'"></span>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <div x-show="loading" class="py-5 text-center text-[12px] text-gray-400">Chargement…</div>
                                <div x-show="!loading && orders.length === 0" class="py-5 text-center text-[12px] text-gray-400">Aucune commande en attente</div>
                                <template x-for="o in orders" :key="o.id">
                                    <a :href="'/admin/orders/'+o.id" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 border-b border-gray-50 transition-colors">
                                        <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-600 text-[11px] font-bold flex items-center justify-center flex-shrink-0"
                                             x-text="(o.customer_name||'??').substring(0,2).toUpperCase()"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[12px] font-medium text-gray-800 truncate" x-text="o.order_number"></p>
                                            <p class="text-[11px] text-gray-400 truncate" x-text="o.customer_name||'Client'"></p>
                                        </div>
                                        <span class="text-[12px] font-semibold text-gray-700 flex-shrink-0" x-text="o.total_fmt"></span>
                                    </a>
                                </template>
                            </div>
                            <div class="px-4 py-2.5 border-t border-gray-100">
                                <a href="{{ route('admin.orders.index') }}" class="block text-center text-[12px] text-orange-500 hover:text-orange-600 font-semibold">Voir toutes →</a>
                            </div>
                        </div>
                    </div>

                    {{-- User --}}
                    <div class="relative" x-data="{open:false}">
                        <button @click="open=!open" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 rounded-full bg-orange-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-[12px] font-semibold text-gray-800 leading-none">{{ Auth::user()->name ?? 'Admin' }}</p>
                                <p class="text-[10px] text-gray-400 capitalize mt-0.5 leading-none">{{ Auth::user()->role ?? 'admin' }}</p>
                            </div>
                            <svg class="w-3 h-3 text-gray-400 transition-transform" :class="open?'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open=false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50">
                            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-[12px] text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Mon profil
                            </a>
                            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-[12px] text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Voir le site
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-[12px] text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
