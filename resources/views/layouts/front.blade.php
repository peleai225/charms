<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Chamse'));
        $siteDescription = \App\Models\Setting::get('site_description', 'Découvrez notre boutique en ligne avec des produits de qualité');
        $siteLogo = \App\Models\Setting::get('logo');
        $siteFavicon = \App\Models\Setting::get('favicon');
        $primaryColor = \App\Models\Setting::get('primary_color', '#6366f1');
        $secondaryColor = \App\Models\Setting::get('secondary_color', '#8b5cf6');
        $accentColor = \App\Models\Setting::get('accent_color', '#f59e0b');
    @endphp
    
    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('meta_description', $siteDescription)">

    @php
        $ogTitle       = $__env->hasSection('og_title')       ? $__env->yieldContent('og_title')       : ($__env->hasSection('title') ? $__env->yieldContent('title') : $siteName);
        $ogDescription = $__env->hasSection('og_description') ? $__env->yieldContent('og_description') : ($__env->hasSection('meta_description') ? $__env->yieldContent('meta_description') : $siteDescription);
    @endphp
    {{-- Open Graph / Social Sharing --}}
    <meta property="og:site_name"   content="{{ $siteName }}">
    <meta property="og:type"        content="@yield('og_type', 'website')">
    <meta property="og:title"       content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url"         content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @elseif($siteLogo)
        <meta property="og:image" content="{{ asset('storage/' . $siteLogo) }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
    @endif

    {{-- PWA --}}
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="theme-color" content="{{ $primaryColor }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    <meta name="mobile-web-app-capable" content="yes">

    {{-- Canonical URL --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Sitemap --}}
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicon dynamique -->
    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='{{ urlencode($primaryColor) }}' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
    @endif
    
    <!-- Styles -->
    @php
        $buildExists = is_dir(public_path('build/assets'));
    @endphp
    @if(app()->environment('local') && !$buildExists)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    
    <!-- Couleurs dynamiques du thème -->
    <style>
        :root {
            --color-primary-500: {{ $primaryColor }};
            --color-primary-600: {{ $primaryColor }};
            --color-secondary-500: {{ $secondaryColor }};
            --color-accent-500: {{ $accentColor }};
        }
        
        /* Override Tailwind primary colors with custom color */
        .bg-primary-500, .bg-primary-600 { background-color: {{ $primaryColor }} !important; }
        .bg-primary-700 { background-color: color-mix(in srgb, {{ $primaryColor }} 85%, black) !important; }
        .text-primary-500, .text-primary-600 { color: {{ $primaryColor }} !important; }
        .text-primary-700 { color: color-mix(in srgb, {{ $primaryColor }} 85%, black) !important; }
        .border-primary-500, .border-primary-600 { border-color: {{ $primaryColor }} !important; }
        .ring-primary-500 { --tw-ring-color: {{ $primaryColor }} !important; }
        .hover\:bg-primary-600:hover { background-color: {{ $primaryColor }} !important; }
        .hover\:bg-primary-700:hover { background-color: color-mix(in srgb, {{ $primaryColor }} 85%, black) !important; }
        .hover\:text-primary-600:hover { color: {{ $primaryColor }} !important; }
        .from-primary-500, .from-primary-600 { --tw-gradient-from: {{ $primaryColor }} !important; }
        .to-primary-700, .to-primary-800 { --tw-gradient-to: color-mix(in srgb, {{ $primaryColor }} 70%, black) !important; }
        .shadow-primary-500\/30, .shadow-primary-600\/30 { --tw-shadow-color: {{ $primaryColor }}4d !important; }

        @keyframes gradient { 0%,100% { background-position: 0% center; } 50% { background-position: 100% center; } }
        .animate-gradient { animation: gradient 6s ease infinite; }
    </style>
    
    {{-- SEO Meta Tags --}}
    <meta name="theme-color" content="{{ $primaryColor }}">
    <meta name="format-detection" content="telephone=no">
    <meta name="author" content="{{ $siteName }}">
    <link rel="alternate" hreflang="fr" href="{{ url()->current() }}">

    @stack('styles')

    {{-- Structured Data JSON-LD --}}
    @include('front.partials.structured-data')

    {{-- Schemas additionnels injectés par les pages enfants --}}
    @stack('schema')
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen" 
      x-data="{ 
          mobileMenuOpen: false, 
          searchOpen: false,
          cartOpen: false
      }"
      x-init="$store.cart.sync()">
    
    @php
        $cart = \App\Models\Cart::getOrCreate(session()->getId(), auth()->user()?->customer);
        $categories = \App\Models\Category::active()->whereNull('parent_id')->with('children')->orderBy('order')->take(6)->get();
        $hideSiteChrome = trim($__env->yieldContent('hide_site_chrome')) === '1';
        
        // Récupérer les informations de contact depuis les paramètres
        $sitePhone = \App\Models\Setting::get('contact_phone', '+225 07 00 00 00 00');
        $siteEmail = \App\Models\Setting::get('contact_email', 'contact@chamse.ci');
        $siteAddress = \App\Models\Setting::get('contact_address', 'Abidjan, Côte d\'Ivoire');
        
        // Récupérer la barre d'annonce active
        $announcementBanners = \App\Models\Banner::active()->position('announcement_bar')->orderBy('order')->get();
        
        // Récupérer les popups actives (première uniquement pour l'UX)
        $popupBanner = \App\Models\Banner::active()->position('popup_center')->orderBy('order')->first();
    @endphp

    {{-- Barre admin rapide (visible uniquement pour les utilisateurs admin/manager/staff) --}}
    @auth
        @if(in_array(auth()->user()->role, ['admin', 'manager', 'staff']))
        <div id="admin-bar" class="fixed top-0 inset-x-0 z-[300] bg-slate-900 text-white text-xs" style="height: 36px;">
            <div class="max-w-7xl mx-auto px-3 h-full flex items-center justify-between gap-3">
                {{-- Gauche : logo + rôle --}}
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-5 h-5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-md flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <span class="text-slate-400 hidden sm:inline truncate">Connecté en tant que</span>
                    <span class="font-semibold text-indigo-300">{{ auth()->user()->name }}</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                        @if(auth()->user()->role === 'admin') bg-red-500/20 text-red-300
                        @elseif(auth()->user()->role === 'manager') bg-amber-500/20 text-amber-300
                        @else bg-slate-500/20 text-slate-300 @endif">
                        {{ auth()->user()->role }}
                    </span>
                </div>

                {{-- Centre : raccourcis rapides --}}
                <div class="flex items-center gap-1 overflow-x-auto scrollbar-none">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span class="hidden sm:inline">Tableau de bord</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="hidden sm:inline">Commandes</span>
                    </a>
                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span class="hidden sm:inline">Produits</span>
                    </a>
                    @endif
                    <a href="{{ route('admin.stock.index') }}" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-slate-700 text-slate-300 hover:text-white transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        <span class="hidden sm:inline">Stock</span>
                    </a>
                    <a href="{{ route('admin.scanner.index') }}" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-indigo-700 bg-indigo-600/30 text-indigo-300 hover:text-white transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span class="hidden sm:inline">Caisse</span>
                    </a>
                </div>

                {{-- Droite : fermer --}}
                <button onclick="document.getElementById('admin-bar').remove(); document.body.style.paddingTop=''"
                    class="shrink-0 p-1 text-slate-500 hover:text-slate-300 transition-colors ml-1" title="Masquer la barre">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <script>
            // Pousser le contenu vers le bas pour éviter que la barre overlay le header
            document.addEventListener('DOMContentLoaded', function() {
                var bar = document.getElementById('admin-bar');
                if (bar) document.body.style.paddingTop = '36px';
            });
        </script>
        @endif
    @endauth

    @if(!$hideSiteChrome)
    <!-- Notification Container -->
    <div x-data="notification" class="fixed top-4 right-4 z-[100] space-y-2">
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
                class="flex items-center gap-3 px-4 py-3 rounded-xl border shadow-xl min-w-[calc(100vw-2rem)] sm:min-w-[300px] backdrop-blur-sm"
            >
                <span x-text="notification.message" class="flex-1"></span>
                <button @click="remove(notification.id)" class="text-current opacity-50 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Barre d'annonce promotionnelle -->
    @if($announcementBanners->count() > 0)
        <div x-data="{ 
            currentIndex: 0, 
            banners: {{ $announcementBanners->count() }},
            dismissed: (typeof safeLocalStorage !== 'undefined' ? safeLocalStorage.getItem('announcement_dismissed_{{ $announcementBanners->first()->id ?? 0 }}') === 'true' : false)
        }" 
        x-show="!dismissed"
        x-transition
        class="bg-gradient-to-r from-primary-600 via-primary-500 to-primary-700 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <div class="container mx-auto px-4 py-2.5 relative">
                <div class="flex items-center justify-center gap-3">
                    @if($announcementBanners->count() > 1)
                        <button @click="currentIndex = (currentIndex - 1 + banners) % banners" 
                                class="p-1 hover:bg-white/20 rounded-full transition-colors hidden sm:block">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    @endif
                    
                    <div class="flex-1 text-center">
                        @foreach($announcementBanners as $index => $banner)
                            <div x-show="currentIndex === {{ $index }}"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="flex items-center justify-center gap-2 text-sm font-medium">
                                <span>{{ $banner->title }}</span>
                                @if($banner->subtitle)
                                    <span class="hidden sm:inline text-white/80">{{ $banner->subtitle }}</span>
                                @endif
                                @if($banner->link)
                                    <a href="{{ $banner->link }}" 
                                       class="inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full text-xs font-semibold transition-colors">
                                        {{ $banner->button_text ?? 'Découvrir' }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    @if($announcementBanners->count() > 1)
                        <button @click="currentIndex = (currentIndex + 1) % banners" 
                                class="p-1 hover:bg-white/20 rounded-full transition-colors hidden sm:block">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endif
                    
                    <button @click="dismissed = true; if (typeof safeLocalStorage !== 'undefined') { safeLocalStorage.setItem('announcement_dismissed_{{ $announcementBanners->first()->id ?? 0 }}', 'true'); }" 
                            class="absolute right-2 sm:right-4 p-1 hover:bg-white/20 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                @if($announcementBanners->count() > 1)
                    <div class="flex items-center justify-center gap-1 mt-1">
                        @foreach($announcementBanners as $index => $banner)
                            <button @click="currentIndex = {{ $index }}" 
                                    :class="currentIndex === {{ $index }} ? 'bg-white' : 'bg-white/40'"
                                    class="w-1.5 h-1.5 rounded-full transition-colors"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Popup bannière (centre écran) -->
    @if($popupBanner)
    <div x-data="{
        show: false,
        dismissed: (typeof safeLocalStorage !== 'undefined' ? safeLocalStorage.getItem('popup_dismissed_{{ $popupBanner->id }}') === 'true' : false),
        init() {
            if (this.dismissed) return;
            setTimeout(() => { this.show = true; }, 1200);
        },
        close() {
            this.show = false;
            if (typeof safeLocalStorage !== 'undefined') {
                safeLocalStorage.setItem('popup_dismissed_{{ $popupBanner->id }}', 'true');
            }
        }
    }"
    x-show="show && !dismissed"
    x-cloak
    x-transition:enter="transition ease-out duration-400"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-250"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    @keydown.escape.window="close()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="popup-title">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-md" @click="close()"></div>

        <!-- Modal popup -->
        <div x-show="show && !dismissed"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md overflow-hidden rounded-2xl shadow-2xl ring-1 ring-white/10"
             style="box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05);"
             @click.stop>
            <!-- Bouton fermer -->
            <button type="button" @click="close()" 
                class="absolute top-3 right-3 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-white/80 hover:bg-white text-slate-500 hover:text-slate-800 shadow-lg hover:scale-110 transition-all duration-200 backdrop-blur-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            @if($popupBanner->image)
            <!-- Image -->
            <div class="relative aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/' . $popupBanner->image) }}" alt="{{ $popupBanner->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    @if($popupBanner->title)
                    <h3 id="popup-title" class="text-xl font-bold mb-1 drop-shadow-lg">{{ $popupBanner->title }}</h3>
                    @endif
                    @if($popupBanner->subtitle)
                    <p class="text-white/95 text-sm drop-shadow-md">{{ $popupBanner->subtitle }}</p>
                    @endif
                </div>
            </div>
            @if($popupBanner->link && $popupBanner->button_text)
            <div class="p-5 bg-white">
                <a href="{{ $popupBanner->link }}" class="inline-flex items-center justify-center gap-2 w-full px-6 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                    {{ $popupBanner->button_text }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            @endif
            @else
            <!-- Contenu sans image -->
            <div class="p-8 md:p-10 text-center bg-white">
                @if($popupBanner->title)
                <h3 id="popup-title" class="text-2xl md:text-3xl font-bold text-slate-900 mb-3 leading-tight">{{ $popupBanner->title }}</h3>
                @endif
                @if($popupBanner->subtitle)
                <p class="text-slate-600 text-base md:text-lg mb-6 max-w-sm mx-auto leading-relaxed">{{ $popupBanner->subtitle }}</p>
                @endif
                @if($popupBanner->link && $popupBanner->button_text)
                <a href="{{ $popupBanner->link }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                    {{ $popupBanner->button_text }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-xl shadow-lg shadow-slate-200/50 sticky top-0 z-50 border-b border-white/20">
        <!-- Barre accent couleur primaire -->
        <div class="h-0.5 bg-gradient-to-r from-primary-500 via-accent-500 to-primary-500 bg-[length:200%_auto] animate-gradient"></div>
        <!-- Top bar avec informations dynamiques -->
        <div class="bg-slate-900 text-slate-300 text-xs py-2.5 hidden lg:block">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($sitePhone)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $sitePhone }}
                    </span>
                    @endif
                    @if($siteEmail)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $siteEmail }}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('contact') }}" class="hover:text-white transition-colors">Aide</a>
                    @auth
                        <a href="{{ route('account.orders') }}" class="hover:text-white transition-colors">Suivi de commande</a>
                    @endauth
                </div>
            </div>
        </div>
        
        <!-- Main header -->
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4 lg:py-5">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                            <span class="text-white font-bold text-xl">{{ substr($siteName, 0, 1) }}</span>
                        </div>
                        <span class="text-2xl font-bold text-slate-900 hidden sm:block">{{ $siteName }}</span>
                    @endif
                </a>
                
                <!-- Search bar (desktop) -->
                <div class="hidden lg:flex flex-1 max-w-xl mx-8">
                    <form action="{{ route('shop.index') }}" method="GET" class="relative w-full group no-ajax" role="search">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher un produit..."
                            class="w-full pl-12 pr-24 py-3 bg-slate-50 border border-slate-200/80 rounded-full text-sm focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 focus:bg-white transition-all duration-200 placeholder:text-slate-400"
                        >
                        <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 z-10 px-5 py-1.5 bg-primary-600 text-white text-xs font-semibold rounded-full hover:bg-primary-700 transition-colors">
                            Rechercher
                        </button>
                    </form>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Search mobile toggle -->
                    <button @click="searchOpen = !searchOpen" class="lg:hidden p-2 text-slate-600 hover:text-primary-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    
                    <!-- Account -->
                    @auth
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-2 text-slate-600 hover:text-primary-600 transition-colors relative group">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="font-medium text-slate-900">{{ auth()->user()->name }}</p>
                                    <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('account.dashboard') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                    Mon compte
                                </a>
                                <a href="{{ route('account.orders') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                    Mes commandes
                                </a>
                                <a href="{{ route('account.addresses') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                    Mes adresses
                                </a>
                                <div class="border-t border-slate-100 mt-2 pt-2">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            Déconnexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="p-2 text-slate-600 hover:text-primary-600 transition-colors relative group">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="hidden sm:block absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs bg-slate-900 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                Connexion
                            </span>
                        </a>
                    @endauth
                    
                    <!-- Wishlist -->
                    <a href="{{ auth()->check() ? route('account.wishlist.index') : route('login') }}"
                       class="p-2 text-slate-600 hover:text-red-500 transition-colors relative group hidden sm:block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>
                    
                    <!-- Cart (ouvre le drawer) -->
                    <button
                        @click="$store.cartDrawer.open()"
                        class="p-2 text-slate-600 hover:text-primary-600 transition-colors relative"
                        aria-label="Ouvrir le panier"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span x-text="$store.cart.count"
                              x-show="$store.cart.count > 0"
                              x-transition:enter="transition ease-out duration-200"
                              x-transition:enter-start="opacity-0 scale-50"
                              x-transition:enter-end="opacity-100 scale-100"
                              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center"></span>
                    </button>
                    
                    <!-- Mobile menu toggle -->
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 text-slate-600 hover:text-primary-600 transition-colors"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile search bar -->
            <div x-show="searchOpen" x-cloak class="lg:hidden pb-4">
                <form action="{{ route('shop.index') }}" method="GET" class="relative">
                    <input 
                        type="search" 
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Rechercher un produit..." 
                        class="w-full pl-10 pr-4 py-3 bg-slate-100 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500"
                    >
                    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Navigation (desktop) -->
        <nav class="hidden lg:block border-t border-slate-100">
            <div class="container mx-auto px-4">
                <ul class="flex items-center gap-1 py-0">
                    <li>
                        <a href="{{ route('home') }}" class="relative block px-4 py-3.5 text-sm font-medium {{ request()->routeIs('home') ? 'text-primary-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors">
                            Accueil
                            @if(request()->routeIs('home'))<span class="absolute bottom-0 left-4 right-4 h-0.5 bg-primary-600 rounded-full"></span>@endif
                        </a>
                    </li>
                    <li x-data="{ open: false }" class="relative">
                        <button
                            @click="open = !open"
                            @click.away="open = false"
                            class="relative flex items-center gap-1 px-4 py-3.5 text-sm font-medium {{ request()->routeIs('shop.*') ? 'text-primary-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors"
                        >
                            Catégories
                            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            x-cloak
                            class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50"
                        >
                            @foreach($categories as $category)
                                <a href="{{ route('shop.category', $category->slug) }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600 transition-colors">
                                    {{ $category->name }}
                                    @if($category->products_count ?? $category->products()->count() > 0)
                                        <span class="text-slate-400 text-xs">({{ $category->products()->count() }})</span>
                                    @endif
                                </a>
                            @endforeach
                            @if($categories->count() > 0)
                                <div class="border-t border-slate-100 mt-2 pt-2">
                                    <a href="{{ route('shop.index') }}" class="block px-4 py-2.5 text-sm font-medium text-primary-600 hover:bg-primary-50 transition-colors">
                                        Voir toutes les catégories
                                    </a>
                                </div>
                            @endif
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('shop.index') }}" class="relative block px-4 py-3.5 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                            Boutique
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="relative block px-4 py-3.5 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                            Nouveautés
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="relative block px-4 py-3.5 text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                            Promotions
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="relative block px-4 py-3.5 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Mobile menu -->
        <div 
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-full"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-full"
            x-cloak
            class="lg:hidden absolute top-full left-0 right-0 bg-white border-t border-slate-100 shadow-xl"
        >
            <div class="container mx-auto px-4 py-4">
                <!-- Mobile nav links -->
                <nav class="space-y-1">
                    <a href="{{ route('home') }}" class="block px-4 py-3 text-slate-900 font-medium rounded-lg hover:bg-slate-50 transition-colors {{ request()->routeIs('home') ? 'bg-primary-50 text-primary-600' : '' }}">
                        Accueil
                    </a>
                    <a href="{{ route('shop.index') }}" class="block px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Boutique
                    </a>
                    
                    <!-- Categories mobile -->
                    <div x-data="{ categoriesOpen: false }">
                        <button @click="categoriesOpen = !categoriesOpen" class="flex items-center justify-between w-full px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            <span>Catégories</span>
                            <svg class="w-4 h-4 transition-transform" :class="categoriesOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="categoriesOpen" x-cloak class="pl-4 space-y-1 mt-1">
                            @foreach($categories as $category)
                                <a href="{{ route('shop.category', $category->slug) }}" class="block px-4 py-2 text-sm text-slate-600 rounded-lg hover:bg-slate-50 transition-colors">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    
                    <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="block px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Nouveautés
                    </a>
                    <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="block px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Promotions
                    </a>
                    <a href="{{ route('contact') }}" class="block px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Contact
                    </a>
                </nav>
                
                <!-- Mobile account links -->
                <div class="border-t border-slate-100 mt-4 pt-4 space-y-1">
                    @auth
                        <a href="{{ route('account.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mon compte
                        </a>
                        <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Mes commandes
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Déconnexion
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-3 text-primary-600 font-medium rounded-lg hover:bg-primary-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Créer un compte
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>
    @endif

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-5 py-4 rounded-xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    @if(!$hideSiteChrome)
    <!-- Back to top button -->
    <div x-data="{ showTop: false }"
         x-init="window.addEventListener('scroll', () => { showTop = window.scrollY > 600 })"
         class="fixed bottom-6 right-6 z-40">
        <button x-show="showTop" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-11 h-11 bg-white text-slate-600 rounded-full shadow-lg shadow-slate-200/60 border border-slate-200 flex items-center justify-center hover:bg-primary-600 hover:text-white hover:border-primary-600 hover:shadow-primary-500/30 transition-all duration-300 hover:-translate-y-0.5">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </div>

    <!-- Footer -->
    <!-- Decorative footer top border -->
    <div class="h-px bg-gradient-to-r from-transparent via-primary-500 to-transparent"></div>

    <footer class="bg-gradient-to-b from-slate-900 to-slate-950 text-slate-300">
        <!-- Newsletter -->
        <div class="border-b border-slate-800/80">
            <div class="container mx-auto px-4 py-14">
                <div class="max-w-2xl mx-auto text-center relative">
                    <!-- Subtle background pattern -->
                    <div class="absolute inset-0 -m-8 rounded-2xl opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M20 20.5V18H0v-2h20v-2l2 3-2 3zM0 20.5V18h20v-2H0v-2l-2 3 2 3z&quot;/%3E%3C/g%3E%3C/svg%3E');"></div>
                    <div class="relative">
                        <span class="inline-block px-4 py-1.5 bg-primary-500/10 text-primary-400 text-xs font-semibold uppercase tracking-wider rounded-full mb-4">Newsletter</span>
                        <h3 class="text-2xl font-bold text-white mb-2">Restez inform&eacute;</h3>
                        <p class="text-slate-400 mb-8">Recevez nos offres exclusives et nouveaut&eacute;s directement dans votre bo&icirc;te mail</p>
                        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                            @csrf
                            <input
                                type="email"
                                name="email"
                                required
                                placeholder="Votre adresse email"
                                class="flex-1 px-5 py-3.5 bg-slate-800/80 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent focus:bg-slate-800 transition-colors"
                            >
                            <button type="submit" class="px-8 py-3.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all hover:shadow-lg hover:shadow-primary-500/25">
                                S'inscrire
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer content -->
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-xl">{{ substr($siteName, 0, 1) }}</span>
                            </div>
                            <span class="text-xl font-bold text-white">{{ $siteName }}</span>
                        @endif
                    </div>
                    <p class="text-slate-400 text-sm mb-4">
                        {{ \App\Models\Setting::get('site_description', 'Votre boutique en ligne de confiance pour des produits de qualité à des prix imbattables.') }}
                    </p>
                    @php
                        $socialFacebook  = \App\Models\Setting::get('social_facebook');
                        $socialInstagram = \App\Models\Setting::get('social_instagram');
                        $socialTwitter   = \App\Models\Setting::get('social_twitter');
                        $socialTiktok    = \App\Models\Setting::get('social_tiktok');
                    @endphp
                    <div class="flex items-center gap-4">
                        @if($socialFacebook)
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        @endif
                        @if($socialInstagram)
                        <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-500 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        @endif
                        @if($socialTwitter)
                        <a href="{{ $socialTwitter }}" target="_blank" rel="noopener" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-black hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        @endif
                        @if($socialTiktok)
                        <a href="{{ $socialTiktok }}" target="_blank" rel="noopener" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-black hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.46V13.2a8.16 8.16 0 005.58 2.17v-3.44a4.85 4.85 0 01-3.77-1.48V6.69z"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Liens utiles</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('about') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">À propos de nous</a></li>
                        <li><a href="{{ route('shop.index') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Boutique</a></li>
                        <li><a href="{{ route('legal', 'conditions-generales') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Conditions générales</a></li>
                        <li><a href="{{ route('legal', 'politique-de-confidentialite') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Politique de confidentialité</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Contact</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Service client</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('contact') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Centre d'aide</a></li>
                        <li><a href="{{ route('order-tracking.index') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Suivi de commande</a></li>
                        <li><a href="{{ route('legal', 'retours-remboursements') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Retours & remboursements</a></li>
                        <li><a href="{{ route('legal', 'livraison') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">Livraison</a></li>
                        <li><a href="{{ route('legal', 'faq') }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">FAQ</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    @php
                        $contactAddress = \App\Models\Setting::get('contact_address', 'Abidjan, Cocody, Côte d\'Ivoire');
                        $contactPhone = \App\Models\Setting::get('contact_phone', '+225 07 00 00 00 00');
                        $contactEmail = \App\Models\Setting::get('contact_email', 'contact@chamse.ci');
                        $socialWhatsapp = \App\Models\Setting::get('social_whatsapp');
                    @endphp
                    <ul class="space-y-3">
                        @if($contactAddress)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm text-slate-400">{!! nl2br(e($contactAddress)) !!}</span>
                        </li>
                        @endif
                        @if($contactPhone)
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $contactPhone }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">{{ $contactPhone }}</a>
                        </li>
                        @endif
                        @if($contactEmail)
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $contactEmail }}" class="text-sm text-slate-400 hover:text-white hover:translate-x-1 transition-transform inline-block">{{ $contactEmail }}</a>
                        </li>
                        @endif
                        @if($socialWhatsapp)
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $socialWhatsapp) }}" target="_blank" class="text-sm text-slate-400 hover:text-green-400 hover:translate-x-1 transition-transform inline-block">WhatsApp</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t border-slate-800/50">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-slate-500">
                        &copy; {{ date('Y') }} {{ $siteName }}. Tous droits r&eacute;serv&eacute;s.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <!-- Orange Money -->
                        <div class="inline-flex items-center gap-1.5 sm:gap-2 h-7 sm:h-8 px-2.5 sm:px-4 bg-slate-800/60 border border-slate-700/50 rounded-full">
                            <span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-orange-500"></span>
                            <span class="text-[10px] sm:text-xs font-medium text-slate-400">Orange Money</span>
                        </div>
                        <!-- MTN MoMo -->
                        <div class="inline-flex items-center gap-1.5 sm:gap-2 h-7 sm:h-8 px-2.5 sm:px-4 bg-slate-800/60 border border-slate-700/50 rounded-full">
                            <span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-yellow-400"></span>
                            <span class="text-[10px] sm:text-xs font-medium text-slate-400">MTN MoMo</span>
                        </div>
                        <!-- CinetPay -->
                        <div class="inline-flex items-center gap-1.5 sm:gap-2 h-7 sm:h-8 px-2.5 sm:px-4 bg-slate-800/60 border border-slate-700/50 rounded-full">
                            <span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-green-400"></span>
                            <span class="text-[10px] sm:text-xs font-medium text-slate-400">CinetPay</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Branding peleAi --}}
        <div class="border-t border-slate-800/60 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-2">
                <span class="text-xs text-slate-500">Solution e-commerce par</span>
                <a href="https://peleai.online" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    peleAi
                </a>
            </div>
        </div>
    </footer>

    @stack('scripts')
    
    <script>
        // Global Alpine.js stores and components
        document.addEventListener('alpine:init', () => {
            // Notification system
            Alpine.data('notification', () => ({
                notifications: [],
                add(message, type = 'info') {
                    const id = Date.now();
                    this.notifications.push({ id, message, type });
                    setTimeout(() => this.remove(id), 5000);
                },
                remove(id) {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }
            }));

            // Global Cart Store
            Alpine.store('cart', {
                count: {{ \App\Models\Cart::getOrCreate(session()->getId(), auth()->user()?->customer)->items_count ?? 0 }},
                items: [],
                subtotal: 0,
                total: 0,
                isLoading: false,
                lastSync: null,

                async sync() {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    try {
                        const response = await fetch('/api/cart', {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.count = data.items_count;
                            this.items = data.items;
                            this.subtotal = data.subtotal;
                            this.total = data.total;
                            this.lastSync = new Date();
                            
                            // Dispatch event for other components
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                        }
                    } catch (error) {
                        console.error('Cart sync error:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async add(productId, variantId = null, quantity = 1) {
                    try {
                        const response = await fetch('/panier/ajouter', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                product_id: productId,
                                variant_id: variantId,
                                quantity: quantity
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                this.count = data.cart_count;
                                window.dispatchEvent(new CustomEvent('cart-item-added', { detail: data }));
                                this.showNotification('Produit ajouté au panier', 'success');
                            }
                        }
                    } catch (error) {
                        console.error('Add to cart error:', error);
                        this.showNotification('Erreur lors de l\'ajout', 'error');
                    }
                },

                async updateQuantity(itemId, quantity) {
                    try {
                        const response = await fetch(`/api/cart/items/${itemId}`, {
                            method: 'PATCH',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ quantity: quantity })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.count = data.items_count;
                            await this.sync();
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                        }
                    } catch (error) {
                        console.error('Update cart error:', error);
                    }
                },

                async remove(itemId) {
                    try {
                        const response = await fetch(`/api/cart/items/${itemId}`, {
                            method: 'DELETE',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.count = data.items_count;
                            await this.sync();
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                            this.showNotification('Produit retiré du panier', 'info');
                        }
                    } catch (error) {
                        console.error('Remove from cart error:', error);
                    }
                },

                showNotification(message, type) {
                    // Trigger notification component
                    window.dispatchEvent(new CustomEvent('show-notification', { 
                        detail: { message, type } 
                    }));
                }
            });

            // Cart Drawer Store
            Alpine.store('cartDrawer', {
                isOpen: false,
                loading: false,
                items: [],
                subtotal_fmt: '',
                discount_fmt: null,
                total_fmt: '',
                coupon_code: null,
                checkout_url: '{{ route("checkout.index") }}',

                async open() {
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                    await this.fetch();
                },

                close() {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                },

                async fetch() {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route("cart.drawer") }}', {
                            credentials: 'same-origin',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.items        = data.items;
                            this.subtotal_fmt = data.subtotal_fmt;
                            this.discount_fmt = data.discount_fmt;
                            this.total_fmt    = data.total_fmt;
                            this.coupon_code  = data.coupon_code;
                            Alpine.store('cart').count = data.count;
                        }
                    } catch (e) { console.error('Drawer fetch error:', e); }
                    finally { this.loading = false; }
                },

                async remove(itemId) {
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    await fetch(`/panier/${itemId}`, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    await this.fetch();
                },

                async updateQty(itemId, qty) {
                    if (qty < 1) { await this.remove(itemId); return; }
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    await fetch(`/panier/${itemId}`, {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ quantity: qty })
                    });
                    await this.fetch();
                }
            });

            // Ouvrir le drawer après un ajout au panier
            window.addEventListener('cart-item-added', () => {
                Alpine.store('cartDrawer').open();
            });

            // Listen for notification events (utilise le store global)
            window.addEventListener('show-notification', (e) => {
                if (window.Alpine?.store('notify')) {
                    Alpine.store('notify').add(e.detail.message, e.detail.type || 'info');
                }
            });
        });

        // Update cart count globally (legacy support)
        function updateCartCount(count) {
            if (Alpine.store('cart')) {
                Alpine.store('cart').count = count;
            }
        }

        // Sync cart on page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && Alpine.store('cart')) {
                Alpine.store('cart').sync();
            }
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form:not(.no-ajax)').forEach(f => {
            const action = (f.getAttribute('action') || '').toLowerCase();
            const method = (f.getAttribute('method') || 'GET').toUpperCase();
            if (method === 'GET') return; // GET forms should navigate normally
            if (!action.includes('process-payment') && !f.closest('[data-no-ajax]')) {
                f.classList.add('ajax-form');
            }
        });
    });
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    @php
        $ga4Id      = \App\Models\Setting::get('ga4_id');
        $pixelId    = \App\Models\Setting::get('meta_pixel_id');
        $tiktokPixel = \App\Models\Setting::get('tiktok_pixel_id');
    @endphp

    {{-- Google Analytics 4 --}}
    @if($ga4Id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $ga4Id }}', { send_page_view: true });

        // Helpers pour les événements e-commerce GA4
        window.trackGA4 = {
            viewItem: (product) => gtag('event', 'view_item', {
                currency: 'XOF',
                value: product.price,
                items: [{ item_id: product.id, item_name: product.name, price: product.price }]
            }),
            addToCart: (product, qty) => gtag('event', 'add_to_cart', {
                currency: 'XOF',
                value: product.price * qty,
                items: [{ item_id: product.id, item_name: product.name, price: product.price, quantity: qty }]
            }),
            beginCheckout: (value) => gtag('event', 'begin_checkout', { currency: 'XOF', value }),
            purchase: (orderId, value) => gtag('event', 'purchase', { transaction_id: orderId, currency: 'XOF', value }),
        };
    </script>
    @endif

    {{-- Meta Pixel (Facebook / Instagram) --}}
    @if($pixelId)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView');

        // Helpers Meta Pixel pour les événements produit
        window.trackPixel = {
            viewContent: (product) => fbq('track', 'ViewContent', {
                content_ids: [product.id], content_type: 'product',
                value: product.price, currency: 'XOF'
            }),
            addToCart: (product, qty) => fbq('track', 'AddToCart', {
                content_ids: [product.id], content_type: 'product',
                value: product.price * qty, currency: 'XOF', num_items: qty
            }),
            initiateCheckout: (value, numItems) => fbq('track', 'InitiateCheckout', {
                value, currency: 'XOF', num_items: numItems
            }),
            purchase: (orderId, value) => fbq('track', 'Purchase', {
                transaction_id: orderId, value, currency: 'XOF'
            }),
        };
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- TikTok Pixel --}}
    @if($tiktokPixel)
    <script>
        !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
        ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
        ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
        for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
        ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
        ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";
        ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;
        ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");
        o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;
        var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
        ttq.load('{{ $tiktokPixel }}');ttq.page();}(window,document,'ttq');
    </script>
    @endif

    @stack('tracking')

    {{-- ===== CART DRAWER (slide-over) ===== --}}
    <div x-data
         x-show="$store.cartDrawer.isOpen"
         x-cloak
         class="relative z-[300]">

        {{-- Backdrop --}}
        <div x-show="$store.cartDrawer.isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$store.cartDrawer.close()"
             class="fixed inset-0 bg-black/50"></div>

        {{-- Drawer panel --}}
        <div x-show="$store.cartDrawer.isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 w-full max-w-md flex flex-col bg-white shadow-2xl transform">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">
                    Mon panier
                    <span x-show="$store.cart.count > 0"
                          class="ml-2 inline-flex items-center justify-center w-6 h-6 bg-primary-600 text-white text-xs rounded-full"
                          x-text="$store.cart.count"></span>
                </h2>
                <button @click="$store.cartDrawer.close()"
                        class="p-2 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="$store.cartDrawer.loading" class="flex-1 flex items-center justify-center">
                <svg class="w-8 h-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            {{-- Empty state --}}
            <div x-show="!$store.cartDrawer.loading && $store.cartDrawer.items.length === 0"
                 class="flex-1 flex flex-col items-center justify-center text-center px-6">
                <svg class="w-16 h-16 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="text-slate-500 font-medium mb-1">Votre panier est vide</p>
                <p class="text-sm text-slate-400 mb-6">Découvrez nos produits et ajoutez-en au panier.</p>
                <a href="{{ route('shop.index') }}"
                   @click="$store.cartDrawer.close()"
                   class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                    Voir la boutique
                </a>
            </div>

            {{-- Items list --}}
            <div x-show="!$store.cartDrawer.loading && $store.cartDrawer.items.length > 0"
                 class="flex-1 overflow-y-auto divide-y divide-slate-100 px-5 py-2">
                <template x-for="item in $store.cartDrawer.items" :key="item.id">
                    <div class="py-4 flex gap-3">
                        {{-- Image --}}
                        <a :href="'/produits/' + item.slug" @click="$store.cartDrawer.close()">
                            <img :src="item.image || '/images/placeholder.png'"
                                 :alt="item.name"
                                 class="w-16 h-16 rounded-lg object-cover border border-slate-100 flex-shrink-0">
                        </a>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <a :href="'/produits/' + item.slug"
                               @click="$store.cartDrawer.close()"
                               class="text-sm font-medium text-slate-900 hover:text-primary-600 line-clamp-2"
                               x-text="item.name"></a>
                            <p x-show="item.variant" x-text="item.variant"
                               class="text-xs text-slate-400 mt-0.5"></p>
                            <p class="text-sm font-bold text-primary-600 mt-1" x-text="item.price_fmt"></p>
                        </div>
                        {{-- Qty + remove --}}
                        <div class="flex flex-col items-end justify-between gap-2">
                            <button @click="$store.cartDrawer.remove(item.id)"
                                    class="text-slate-300 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <div class="flex items-center gap-1 border border-slate-200 rounded-lg overflow-hidden">
                                <button @click="$store.cartDrawer.updateQty(item.id, item.quantity - 1)"
                                        class="px-2 py-1 text-slate-500 hover:bg-slate-100 transition-colors text-sm">−</button>
                                <span class="px-2 text-sm font-medium text-slate-800" x-text="item.quantity"></span>
                                <button @click="$store.cartDrawer.updateQty(item.id, item.quantity + 1)"
                                        class="px-2 py-1 text-slate-500 hover:bg-slate-100 transition-colors text-sm">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer totals + CTA --}}
            <div x-show="!$store.cartDrawer.loading && $store.cartDrawer.items.length > 0"
                 class="border-t border-slate-200 px-5 py-4 space-y-3 bg-slate-50">
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Sous-total</span>
                        <span x-text="$store.cartDrawer.subtotal_fmt"></span>
                    </div>
                    <div x-show="$store.cartDrawer.discount_fmt" class="flex justify-between text-green-600">
                        <span>Réduction (<span x-text="$store.cartDrawer.coupon_code"></span>)</span>
                        <span>− <span x-text="$store.cartDrawer.discount_fmt"></span></span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 text-base pt-1 border-t border-slate-200">
                        <span>Total</span>
                        <span x-text="$store.cartDrawer.total_fmt"></span>
                    </div>
                </div>
                <a :href="$store.cartDrawer.checkout_url"
                   class="block w-full text-center py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    Passer la commande →
                </a>
                <a href="{{ route('cart.index') }}"
                   @click="$store.cartDrawer.close()"
                   class="block w-full text-center py-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
                    Voir le panier complet
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Bouton WhatsApp flottant (toutes pages) --}}
    @php $waNumber = \App\Models\Setting::get('social_whatsapp'); @endphp
    @if($waNumber && !$hideSiteChrome)
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $waNumber) }}?text={{ urlencode('Bonjour ! Je souhaite des informations.') }}"
       target="_blank" rel="noopener"
       aria-label="Contacter sur WhatsApp"
       class="group fixed bottom-5 right-5 z-[150] flex items-center gap-3"
       style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <span class="hidden md:block bg-white text-slate-800 text-xs font-semibold px-3 py-2 rounded-xl shadow-lg opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300 whitespace-nowrap">
            Besoin d'aide ?
        </span>
        <span class="relative flex items-center justify-center w-14 h-14 bg-[#25D366] rounded-full shadow-2xl shadow-[#25D366]/40 hover:scale-110 transition-transform duration-300">
            <span class="absolute inset-0 rounded-full bg-[#25D366] animate-ping opacity-30"></span>
            <svg class="w-7 h-7 text-white relative z-10" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </span>
    </a>
    @endif

    {{-- PWA Install Banner --}}
    <div x-data="pwaInstall()" x-show="showBanner" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0" x-cloak class="fixed bottom-0 inset-x-0 z-[200]" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="mx-auto max-w-lg px-4 pb-4">
            <div class="bg-white rounded-2xl shadow-2xl shadow-slate-900/20 border border-slate-200 p-4 sm:p-5">
                {{-- Android / Chrome --}}
                <template x-if="platform === 'android'">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-900">Installer l'application</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Accès rapide depuis votre écran d'accueil, mode plein écran.</p>
                            <div class="flex items-center gap-2 mt-3">
                                <button @click="installApp()" class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                                    Installer
                                </button>
                                <button @click="dismiss()" class="px-4 py-2 text-xs font-medium text-slate-500 hover:text-slate-700 transition-colors">
                                    Plus tard
                                </button>
                            </div>
                        </div>
                        <button @click="dismiss()" class="flex-shrink-0 p-1 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                {{-- iOS Safari --}}
                <template x-if="platform === 'ios'">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-900">Installer l'application</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Appuyez sur
                                <svg class="inline w-4 h-4 text-blue-500 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                puis <strong>"Sur l'écran d'accueil"</strong>
                            </p>
                            <div class="mt-3">
                                <button @click="dismiss()" class="px-4 py-2 text-xs font-medium text-slate-500 hover:text-slate-700 transition-colors">
                                    J'ai compris
                                </button>
                            </div>
                        </div>
                        <button @click="dismiss()" class="flex-shrink-0 p-1 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Service Worker + PWA Install Script --}}
    <script>
        // Kill-switch : on désinscrit tout SW existant et on purge les caches.
        // Le fichier /sw.js a été temporairement remplacé par un SW d'auto-nettoyage,
        // mais on agit aussi côté client au cas où le SW expiré ne serait pas re-fetch.
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((registrations) => {
                registrations.forEach((registration) => registration.unregister());
            }).catch(() => {});

            if (window.caches && caches.keys) {
                caches.keys().then((keys) => {
                    keys.forEach((key) => caches.delete(key));
                }).catch(() => {});
            }
        }

        // PWA Install prompt
        let pwaInstallPrompt = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            pwaInstallPrompt = e;
            window.dispatchEvent(new CustomEvent('pwa-installable'));
        });

        function pwaInstall() {
            return {
                showBanner: false,
                platform: 'android',

                init() {
                    // Already installed as PWA?
                    if (window.matchMedia('(display-mode: standalone)').matches || navigator.standalone) return;
                    // Dismissed recently?
                    const dismissed = localStorage.getItem('pwa-dismiss');
                    if (dismissed && (Date.now() - parseInt(dismissed)) < 3 * 24 * 60 * 60 * 1000) return;

                    // Detect platform
                    const ua = navigator.userAgent;
                    const isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                    const isSafari = /Safari/.test(ua) && !/Chrome/.test(ua);

                    if (isIOS && isSafari) {
                        this.platform = 'ios';
                        setTimeout(() => { this.showBanner = true; }, 3000);
                    } else {
                        this.platform = 'android';
                        if (pwaInstallPrompt) {
                            setTimeout(() => { this.showBanner = true; }, 2000);
                        }
                        window.addEventListener('pwa-installable', () => {
                            setTimeout(() => { this.showBanner = true; }, 2000);
                        });
                    }
                },

                async installApp() {
                    if (!pwaInstallPrompt) return;
                    pwaInstallPrompt.prompt();
                    const { outcome } = await pwaInstallPrompt.userChoice;
                    if (outcome === 'accepted') {
                        this.showBanner = false;
                        localStorage.setItem('pwa-installed', '1');
                    }
                    pwaInstallPrompt = null;
                },

                dismiss() {
                    this.showBanner = false;
                    localStorage.setItem('pwa-dismiss', Date.now().toString());
                }
            };
        }
    </script>
</body>
</html>
