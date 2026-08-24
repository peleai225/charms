@php
    $pendingOrders = \Illuminate\Support\Facades\Cache::remember('admin_pending_orders_count', 60,
        fn() => \App\Models\Order::whereIn('status', ['pending', 'confirmed'])->count()
    );
    $stockAlerts = \Illuminate\Support\Facades\Cache::remember('admin_stock_alerts_count', 120,
        fn() => \App\Models\Product::active()->where('track_stock', true)->where(function($q) {
            $q->where('stock_quantity', 0)->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
        })->count()
    );

    $moreItems = [
        ['href' => route('admin.stock.index'),             'label' => 'Stock',        'route' => 'admin.stock.*',        'badge' => $stockAlerts, 'color' => '#0ea5e9',  'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
        ['href' => route('admin.scanner.index'),           'label' => 'POS',          'route' => 'admin.scanner.*',      'badge' => null,          'color' => '#8b5cf6',  'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'],
        ['href' => route('admin.reports.index'),           'label' => 'Rapports',     'route' => 'admin.reports.*',      'badge' => null,          'color' => '#f59e0b',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['href' => route('admin.categories.index'),        'label' => 'Catégories',   'route' => 'admin.categories.*',   'badge' => null,          'color' => '#10b981',  'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['href' => route('admin.reviews.index'),           'label' => 'Avis',         'route' => 'admin.reviews.*',      'badge' => null,          'color' => '#f97316',  'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ['href' => route('admin.coupons.index'),           'label' => 'Promos',       'route' => 'admin.coupons.*',      'badge' => null,          'color' => '#ef4444',  'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
        ['href' => route('admin.marketing.campaigns'),     'label' => 'Marketing',    'route' => 'admin.marketing.*',    'badge' => null,          'color' => '#ec4899',  'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
        ['href' => route('admin.settings.index'),          'label' => 'Paramètres',   'route' => 'admin.settings.*',     'badge' => null,          'color' => '#6366f1',  'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
@endphp

<div class="lg:hidden" x-data="{ open: false }">

    {{-- ── OVERLAY ── --}}
    <div x-show="open" style="display:none" @click="open = false"
         class="fixed inset-0 bg-black/50 z-40"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    {{-- ── PANEL "PLUS" ── --}}
    <div x-show="open" style="display:none" @click.self="open = false"
         class="fixed inset-x-0 z-50"
         style="bottom: 64px; display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3">
        <div class="mx-3 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">

            <div class="flex items-center justify-between px-4 pt-3 pb-2 border-b border-gray-100">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Navigation</p>
                <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-4">
                @foreach($moreItems as $item)
                <a href="{{ $item['href'] }}" @click="open = false"
                   class="flex flex-col items-center gap-2 px-2 py-4 hover:bg-gray-50 active:bg-gray-100 transition-colors relative border-b border-r border-gray-50">
                    @if(!empty($item['badge']) && $item['badge'] > 0)
                        <span class="absolute top-2 right-2 min-w-[15px] h-[15px] px-0.5 flex items-center justify-center text-[9px] font-bold text-white bg-red-500 rounded-full">
                            {{ $item['badge'] > 9 ? '9+' : $item['badge'] }}
                        </span>
                    @endif
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                         style="background-color: {{ $item['color'] }}18;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75"
                             style="color: {{ $item['color'] }};" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">{{ $item['label'] }}</span>
                </a>
                @endforeach
            </div>

            <div class="p-3 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold text-red-500 bg-red-50 active:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── BARRE DE NAVIGATION ── --}}
    <nav class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 shadow-[0_-1px_8px_rgba(0,0,0,0.06)]"
         style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="grid grid-cols-5" style="height: 60px;">

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                     stroke-width="{{ request()->routeIs('admin.dashboard') ? '2.5' : '1.75' }}"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] font-semibold leading-none">Accueil</span>
            </a>

            {{-- Commandes --}}
            <a href="{{ route('admin.orders.index') }}"
               class="relative flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('admin.orders.*') ? 'text-orange-500' : 'text-gray-400' }}">
                @if($pendingOrders > 0)
                    <span class="absolute top-2 right-4 min-w-[15px] h-[15px] px-0.5 flex items-center justify-center text-[9px] font-bold text-white bg-red-500 rounded-full border-2 border-white">
                        {{ $pendingOrders > 9 ? '9+' : $pendingOrders }}
                    </span>
                @endif
                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                     stroke-width="{{ request()->routeIs('admin.orders.*') ? '2.5' : '1.75' }}"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="text-[10px] font-semibold leading-none">Commandes</span>
            </a>

            {{-- Produits --}}
            <a href="{{ route('admin.products.index') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('admin.products.*') ? 'text-emerald-600' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                     stroke-width="{{ request()->routeIs('admin.products.*') ? '2.5' : '1.75' }}"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[10px] font-semibold leading-none">Produits</span>
            </a>

            {{-- Clients --}}
            <a href="{{ route('admin.customers.index') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors {{ request()->routeIs('admin.customers.*') ? 'text-pink-600' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                     stroke-width="{{ request()->routeIs('admin.customers.*') ? '2.5' : '1.75' }}"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-none">Clients</span>
            </a>

            {{-- Plus --}}
            <button @click="open = !open"
                    class="flex flex-col items-center justify-center gap-1 w-full transition-colors"
                    :class="open ? 'text-indigo-600' : 'text-gray-400'">
                <svg class="w-6 h-6 transition-transform duration-200" :class="open ? 'rotate-90' : ''"
                     fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span class="text-[10px] font-semibold leading-none">Plus</span>
            </button>

        </div>
    </nav>

</div>