<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') - {{ \App\Models\Setting::get('site_name', config('app.name')) }}</title>
    
    @php
        $siteLogo = \App\Models\Setting::get('logo');
        $siteFavicon = \App\Models\Setting::get('favicon');
        $siteName = \App\Models\Setting::get('site_name', config('app.name'));
    @endphp
    
    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%234F46E5' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
    @endif
    
    @php
        $buildExists = is_dir(public_path('build/assets'));
    @endphp
    @if(app()->environment('local') && !$buildExists)
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-notifications.js'])
    @elseif($buildExists)
        @php
            $buildPath = public_path('build/assets');
            $files = scandir($buildPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (str_ends_with($file, '.css')) {
                        echo '<link rel="stylesheet" href="' . asset('build/assets/' . $file) . '">' . "\n    ";
                    } elseif (str_ends_with($file, '.js')) {
                        echo '<script type="module" src="' . asset('build/assets/' . $file) . '"></script>' . "\n    ";
                    }
                }
            }
        @endphp
    @else
        @php
            try {
                if (class_exists('\App\Helpers\ViteHelper')) {
                    $viteHelper = new \App\Helpers\ViteHelper();
                    echo $viteHelper->renderAssets(['resources/css/app.css', 'resources/js/app.js']);
                } else {
                    // Fallback: charger directement depuis build/assets
                    $buildPath = public_path('build/assets');
                    if (is_dir($buildPath)) {
                        $files = scandir($buildPath);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..') {
                                if (str_ends_with($file, '.css')) {
                                    echo '<link rel="stylesheet" href="' . asset('build/assets/' . $file) . '">' . "\n    ";
                                } elseif (str_ends_with($file, '.js')) {
                                    echo '<script type="module" src="' . asset('build/assets/' . $file) . '"></script>' . "\n    ";
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // En cas d'erreur, essayer de charger directement
                $buildPath = public_path('build/assets');
                if (is_dir($buildPath)) {
                    $files = scandir($buildPath);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            if (str_ends_with($file, '.css')) {
                                echo '<link rel="stylesheet" href="' . asset('build/assets/' . $file) . '">' . "\n    ";
                            } elseif (str_ends_with($file, '.js')) {
                                echo '<script type="module" src="' . asset('build/assets/' . $file) . '"></script>' . "\n    ";
                            }
                        }
                    }
                }
            }
        @endphp
    @endif
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.store('notify')) {
            Alpine.store('notify', {
                notifications: [],
                add(message, type = 'info', duration = 5000) {
                    const id = Date.now() + Math.random();
                    this.notifications.push({ id, message, type });
                    if (duration > 0) setTimeout(() => this.remove(id), duration);
                },
                remove(id) { this.notifications = this.notifications.filter(n => n.id !== id); },
                success(m, d = 5000) { this.add(m, 'success', d); },
                error(m, d = 6000) { this.add(m, 'error', d); },
                warning(m, d = 5000) { this.add(m, 'warning', d); }
            });
        }
        if (!Alpine.data('notification')) {
            Alpine.data('notification', () => ({
                get notifications() { return Alpine.store('notify')?.notifications ?? []; },
                remove(id) { Alpine.store('notify')?.remove(id); }
            }));
        }
    });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Sidebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #475569; }
        
        /* Animations */
        .fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bellShake {
            0%,100% { transform: rotate(0deg); }
            15%      { transform: rotate(15deg); }
            30%      { transform: rotate(-12deg); }
            45%      { transform: rotate(10deg); }
            60%      { transform: rotate(-8deg); }
            75%      { transform: rotate(5deg); }
            90%      { transform: rotate(-3deg); }
        }
        
        /* Alpine x-cloak */
        [x-cloak] { display: none !important; }
        
        /* Active menu indicator */
        .menu-active { position: relative; }
        .menu-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 0 4px 4px 0;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen" x-data="{ sidebarOpen: true, mobileMenuOpen: false, sidebarCollapsed: (() => { try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e) { return false } })(), toggleSidebar() { this.sidebarCollapsed = !this.sidebarCollapsed; try { localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed) } catch(e) {} }, searchOpen: false }">
    
    <!-- Notifications toast (sans rechargement) -->
    <div x-data="notification" class="fixed top-4 right-4 z-[9999] space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-8"
                :class="{
                    'bg-green-50 border-green-200 text-green-800': notification.type === 'success',
                    'bg-red-50 border-red-200 text-red-800': notification.type === 'error',
                    'bg-amber-50 border-amber-200 text-amber-800': notification.type === 'warning',
                    'bg-blue-50 border-blue-200 text-blue-800': notification.type === 'info'
                }"
                class="flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg min-w-[300px]"
            >
                <span x-text="notification.message" class="flex-1"></span>
                <button @click="remove(notification.id)" class="text-current opacity-50 hover:opacity-100 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
    
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 transform transition-all duration-300 lg:translate-x-0 shadow-2xl"
            :class="{
                '-translate-x-full': !mobileMenuOpen && false,
                'translate-x-0': mobileMenuOpen,
                'w-20': sidebarCollapsed && !mobileMenuOpen,
                'w-64': !sidebarCollapsed || mobileMenuOpen,
                '-translate-x-full lg:translate-x-0': !mobileMenuOpen
            }"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between border-b border-slate-700/50" :class="sidebarCollapsed ? 'px-3' : 'px-5'">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto rounded-lg flex-shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-shadow flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>
                        <span class="text-lg font-bold text-white block">{{ $siteName }}</span>
                        <span class="text-xs text-slate-400">Administration</span>
                    </div>
                </a>
                <button @click="mobileMenuOpen = false" class="lg:hidden text-slate-400 hover:text-white p-2 hover:bg-slate-700/50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="space-y-0.5 overflow-y-auto sidebar-scroll h-[calc(100vh-4rem-3.5rem)]" :class="sidebarCollapsed ? 'p-2' : 'p-3'">
                <!-- Dashboard -->
                <div class="relative group">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.dashboard') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <span class="font-medium" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Tableau de bord</span>
                    </a>
                    <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Tableau de bord</div>
                </div>

                <!-- Catalogue -->
                <div class="pt-6">
                    <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Catalogue</p>
                    <div x-show="sidebarCollapsed" class="border-t border-slate-700/50 my-3 mx-2"></div>
                    
                    <div class="relative group">
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.products.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-green-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Produits</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Produits</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.categories.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Catégories</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Catégories</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.attributes.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.attributes.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-fuchsia-500/20 to-pink-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Attributs</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Attributs (tailles, couleurs)</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.barcodes.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.barcodes.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500/20 to-teal-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Codes-barres</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Codes-barres</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.scanner.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.scanner.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-lime-500/20 to-green-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Scanner / Caisse</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Scanner / Caisse</div>
                    </div>
                </div>

                <!-- Ventes -->
                <div class="pt-6">
                    <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Ventes</p>
                    <div x-show="sidebarCollapsed" class="border-t border-slate-700/50 my-3 mx-2"></div>
                    
                    <div class="relative group">
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.orders.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500/20 to-amber-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Commandes</span>
                            @php $pendingOrders = \App\Models\Order::whereIn('status', ['pending', 'confirmed'])->count(); @endphp
                            @if($pendingOrders > 0)
                                <span data-pending-orders-count class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg shadow-red-500/30" :class="sidebarCollapsed ? '' : 'ml-auto'">{{ $pendingOrders }}</span>
                            @else
                                <span data-pending-orders-count class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg shadow-red-500/30 hidden" :class="sidebarCollapsed ? '' : 'ml-auto'">0</span>
                            @endif
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Commandes</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.refunds.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.refunds.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500/20 to-orange-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Remboursements</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Remboursements</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.customers.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500/20 to-rose-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Clients</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Clients</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.reviews.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500/20 to-yellow-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Avis clients</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Avis clients</div>
                    </div>
                    <div class="relative group">
                        <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.coupons.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-500/20 to-orange-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Codes promo</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Codes promo</div>
                    </div>
                </div>

                <!-- Stock -->
                <div class="pt-6">
                    <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Stock</p>
                    <div x-show="sidebarCollapsed" class="border-t border-slate-700/50 my-3 mx-2"></div>
                    
                    <div class="relative group">
                        <a href="{{ route('admin.stock.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.stock.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500/20 to-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Gestion stock</span>
                            @php
                                $stockAlerts = \App\Models\Product::active()
                                    ->where('track_stock', true)
                                    ->where(function($q) {
                                        $q->where('stock_quantity', 0)
                                          ->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
                                    })->count();
                            @endphp
                            @if($stockAlerts > 0)
                                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg shadow-amber-500/30" :class="sidebarCollapsed ? '' : 'ml-auto'">{{ $stockAlerts }}</span>
                            @endif
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Gestion stock</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.suppliers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.suppliers.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-500/20 to-gray-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Fournisseurs</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Fournisseurs</div>
                    </div>
                </div>

                <!-- Finances -->
                <div class="pt-6">
                    <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Finances</p>
                    <div x-show="sidebarCollapsed" class="border-t border-slate-700/50 my-3 mx-2"></div>

                    @if(auth()->user()->role === 'admin')
                    <div class="relative group">
                        <a href="{{ route('admin.accounting.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.accounting.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Comptabilité</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Comptabilité</div>
                    </div>
                    @endif

                    <div class="relative group">
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.reports.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/20 to-indigo-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Rapports</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Rapports</div>
                    </div>
                </div>

                <!-- Contenu -->
                <div class="pt-6">
                    <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Contenu</p>
                    <div x-show="sidebarCollapsed" class="border-t border-slate-700/50 my-3 mx-2"></div>
                    
                    <div class="relative group">
                        <a href="{{ route('admin.whatsapp.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.whatsapp.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>WhatsApp Business</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">WhatsApp Business</div>
                    </div>

                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    <div class="relative group">
                        <a href="{{ route('admin.banners.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.banners.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500/20 to-pink-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Bannières</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Bannières</div>
                    </div>
                    @endif
                </div>

                <!-- Configuration (admin uniquement) -->
                @if(auth()->user()->role === 'admin')
                <div class="pt-6">
                    <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Configuration</p>
                    <div x-show="sidebarCollapsed" class="border-t border-slate-700/50 my-3 mx-2"></div>

                    <div class="relative group">
                        <a href="{{ route('admin.import-export.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.import-export.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500/20 to-violet-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Import / Export</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Import / Export</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.users.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Utilisateurs</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Utilisateurs</div>
                    </div>

                    <div class="relative group">
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white transition-all {{ request()->routeIs('admin.settings.*') ? 'menu-active bg-white/10 text-white' : '' }}" :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-500/20 to-zinc-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Paramètres</span>
                        </a>
                        <div x-show="sidebarCollapsed" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-800 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">Paramètres</div>
                    </div>
                </div>
                @endif

                <!-- Spacer -->
                <div class="pt-6"></div>
            </nav>

            <!-- Sidebar Toggle Button -->
            <div class="hidden lg:flex h-14 items-center border-t border-slate-700/50" :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                <button @click="toggleSidebar()" class="flex items-center gap-3 w-full px-3 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <svg class="w-5 h-5 transition-transform duration-300 flex-shrink-0" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span class="text-sm font-medium" x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>Réduire</span>
                </button>
            </div>
        </aside>

        <!-- Overlay mobile -->
        <div 
            x-show="mobileMenuOpen" 
            @click="mobileMenuOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <!-- Contenu principal -->
        <div class="flex-1 transition-all duration-300" :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'">
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-xl border-b border-slate-200/50 sticky top-0 z-30 shadow-sm">
                <div class="h-16 flex items-center justify-between px-6">
                <!-- Menu mobile + Titre -->
                <div class="flex items-center gap-4">
                    <button @click="mobileMenuOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-xs text-slate-500 hidden sm:block">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                    </div>
                </div>

                <!-- Global Search -->
                <div class="hidden md:block flex-1 max-w-md mx-6">
                    <button @click="searchOpen = true" class="w-full flex items-center gap-3 bg-slate-100 hover:bg-slate-200 rounded-xl px-4 py-2 text-sm text-slate-500 transition-colors">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Rechercher... (Ctrl+K)</span>
                        <kbd class="ml-auto hidden lg:inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-slate-400 bg-white rounded-md border border-slate-200">Ctrl+K</kbd>
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <!-- Voir le site -->
                    <a href="{{ route('home') }}" target="_blank" class="hidden md:flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 px-3 py-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span>Voir le site</span>
                    </a>

                    <!-- Toggle son notifications -->
                    <button
                        x-data="{ enabled: true }"
                        x-init="try { enabled = localStorage.getItem('admin_sound_enabled') !== 'false' } catch(e) {}"
                        @click="enabled = !enabled; try { localStorage.setItem('admin_sound_enabled', enabled) } catch(e) {}; if (window.adminToggleSound) window.adminToggleSound();"
                        class="p-2 rounded-lg transition-colors"
                        :class="enabled ? 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' : 'text-slate-300 hover:text-slate-500 hover:bg-slate-100'"
                        :title="enabled ? 'Son activé (cliquer pour désactiver)' : 'Son désactivé (cliquer pour activer)'"
                    >
                        <svg x-show="enabled" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                        <svg x-show="!enabled" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                            :class="{{ $pendingOrders > 0 ? 'true' : 'false' }} ? 'text-orange-600' : ''"
                            id="notification-bell-btn">
                            <svg class="w-5 h-5 transition-transform" id="notification-bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            {{-- Count badge --}}
                            <span id="notification-count-badge"
                                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center ring-2 ring-white transition-all {{ $pendingOrders > 0 ? '' : 'hidden' }}"
                                data-notification-dot>{{ $pendingOrders > 0 ? $pendingOrders : '' }}</span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50">
                            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                                <h3 class="font-semibold text-slate-900">Notifications</h3>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @if($pendingOrders > 0)
                                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 p-4 hover:bg-slate-50 transition-colors border-b border-slate-100">
                                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">{{ $pendingOrders }} commande(s) en attente</p>
                                            <p class="text-xs text-slate-500">À traiter</p>
                                        </div>
                                    </a>
                                @endif
                                @if($stockAlerts > 0)
                                    <a href="{{ route('admin.stock.alerts') }}" class="flex items-center gap-3 p-4 hover:bg-slate-50 transition-colors">
                                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">{{ $stockAlerts }} alerte(s) stock</p>
                                            <p class="text-xs text-slate-500">Réapprovisionnement nécessaire</p>
                                        </div>
                                    </a>
                                @endif
                                @if($pendingOrders == 0 && $stockAlerts == 0)
                                    <div class="p-8 text-center text-slate-500">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-sm">Aucune notification</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- User menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-slate-100 rounded-xl p-2 transition-colors">
                            @if(auth()->user()->avatar ?? false)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg shadow-indigo-500/30">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-slate-700 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50"
                        >
                            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                                <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 rounded-xl transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Mon profil
                                </a>
                                @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 rounded-xl transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Paramètres
                                </a>
                                @endif
                            </div>
                            <div class="border-t border-slate-100 p-2">
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <!-- Breadcrumbs -->
                @hasSection('breadcrumbs')
                <div class="px-6 py-2 border-t border-slate-100 bg-white/50">
                    <nav class="flex items-center text-sm text-slate-500">
                        @yield('breadcrumbs')
                    </nav>
                </div>
                @endif
            </header>

            <!-- Command Palette Modal -->
            <div x-show="searchOpen" x-cloak @keydown.escape.window="searchOpen = false" @keydown.ctrl.k.window.prevent="searchOpen = !searchOpen" class="fixed inset-0 z-[9998] flex items-start justify-center pt-24">
                <div x-show="searchOpen" @click="searchOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-200">
                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" placeholder="Rechercher une page, commande, produit..." class="flex-1 bg-transparent border-none outline-none text-sm text-slate-900 placeholder-slate-400" autofocus>
                        <kbd class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-md border border-slate-200">Esc</kbd>
                    </div>
                    <div class="p-3 max-h-80 overflow-y-auto">
                        <p class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Raccourcis</p>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors text-sm text-slate-700">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Tableau de bord
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors text-sm text-slate-700">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Commandes
                        </a>
                        @if(in_array(auth()->user()->role, ['admin', 'manager']))
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors text-sm text-slate-700">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Produits
                        </a>
                        @endif
                        <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors text-sm text-slate-700">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Clients
                        </a>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors text-sm text-slate-700">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Paramètres
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contenu de la page -->
            <main class="p-6">
                <!-- Messages flash -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 flex items-center gap-3 fade-in shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Succès !</p>
                            <p class="text-sm text-green-600">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 flex items-center gap-3 fade-in shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Erreur</p>
                            <p class="text-sm text-red-600">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 text-amber-700 flex items-center gap-3 fade-in shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Attention</p>
                            <p class="text-sm text-amber-600">{{ session('warning') }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="p-6 text-center text-sm text-slate-500 border-t border-slate-200 bg-white/50">
                <p>© {{ date('Y') }} {{ $siteName }}. Tous droits réservés.</p>
            </footer>
        </div>
    </div>

    @stack('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('main form:not(.no-ajax)').forEach(f => f.classList.add('ajax-form'));
    });
    </script>

    {{-- =====================================================
         POLLING TEMPS RÉEL - Notifications admin sans Pusher
         Rafraîchit les compteurs toutes les 30 secondes
    ====================================================== --}}
    <script>
    (function() {
        'use strict';

        // Son de notification (beep discret généré en Web Audio API)
        function playNotifSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                osc.type = 'sine';
                gain.gain.setValueAtTime(0, ctx.currentTime);
                gain.gain.linearRampToValueAtTime(0.15, ctx.currentTime + 0.01);
                gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {}
        }

        let lastCheckTime = new Date().toISOString();
        let previousPendingCount = {{ \App\Models\Order::whereIn('status', ['pending','confirmed'])->count() }};

        async function pollAdminStats() {
            try {
                const url = '/api/admin/poll-stats?since=' + encodeURIComponent(lastCheckTime);
                const resp = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                if (!resp.ok) return;
                const data = await resp.json();

                // Mettre à jour le badge commandes en attente
                const badgeEl = document.querySelector('[data-pending-orders-count]');
                if (badgeEl) {
                    badgeEl.textContent = data.pending_orders;
                    badgeEl.classList.toggle('hidden', data.pending_orders === 0);
                }

                // Mettre à jour le point rouge sur la cloche
                const dotEl = document.querySelector('[data-notification-dot]');
                if (dotEl) {
                    dotEl.classList.toggle('hidden', data.pending_orders === 0 && data.stock_alerts === 0);
                }

                // Nouvelles commandes arrivées depuis le dernier poll
                if (data.new_orders && data.new_orders.length > 0) {
                    playNotifSound();
                    data.new_orders.forEach(order => {
                        const notify = window.Alpine?.store('notify');
                        if (notify) {
                            notify.add(
                                `🛍️ Nouvelle commande #${order.order_number} — ${order.total}`,
                                'info',
                                8000
                            );
                        }
                    });
                }

                // Alerte si bond de commandes en attente
                if (data.pending_orders > previousPendingCount) {
                    playNotifSound();
                }
                previousPendingCount = data.pending_orders;
                lastCheckTime = data.server_time || new Date().toISOString();

            } catch (e) {
                // Silencieux — ne pas déranger l'admin si le réseau est coupé
            }
        }

        // Démarrer après 5s (laisser la page charger) puis toutes les 30s
        setTimeout(() => {
            pollAdminStats();
            setInterval(pollAdminStats, 30000);
        }, 5000);
    })();
    </script>
</body>
</html>
