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
        $primaryColor = \App\Models\Setting::get('primary_color', '#ba0d5d');
        $secondaryColor = \App\Models\Setting::get('secondary_color', '#ba0d5d');
        $accentColor = \App\Models\Setting::get('accent_color', '#e4ff5c');

        // Calcul des nuances en PHP (hex pur, compatible tous navigateurs)
        $hexMix = function(string $hex, float $ratio, bool $withBlack = false): string {
            $hex = ltrim($hex, '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $mix = $withBlack ? [0, 0, 0] : [255, 255, 255];
            $r = (int) round($r * $ratio + $mix[0] * (1 - $ratio));
            $g = (int) round($g * $ratio + $mix[1] * (1 - $ratio));
            $b = (int) round($b * $ratio + $mix[2] * (1 - $ratio));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };

        $p50  = $hexMix($primaryColor, 0.06);
        $p100 = $hexMix($primaryColor, 0.12);
        $p200 = $hexMix($primaryColor, 0.22);
        $p300 = $hexMix($primaryColor, 0.40);
        $p400 = $hexMix($primaryColor, 0.65);
        $p500 = $hexMix($primaryColor, 0.90);
        $p600 = $primaryColor;
        $p700 = $hexMix($primaryColor, 0.85, true);
        $p800 = $hexMix($primaryColor, 0.70, true);
        $p900 = $hexMix($primaryColor, 0.55, true);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .font-serif-display { font-family: 'Fraunces', Georgia, serif; font-optical-sizing: auto; }
    </style>
    
    <!-- Favicon dynamique -->
    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='{{ urlencode($primaryColor) }}' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
    @endif
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Couleurs dynamiques du thème -->
    <style>
        :root {
            --color-primary-50:  {{ $p50 }};
            --color-primary-100: {{ $p100 }};
            --color-primary-200: {{ $p200 }};
            --color-primary-300: {{ $p300 }};
            --color-primary-400: {{ $p400 }};
            --color-primary-500: {{ $p500 }};
            --color-primary-600: {{ $p600 }};
            --color-primary-700: {{ $p700 }};
            --color-primary-800: {{ $p800 }};
            --color-primary-900: {{ $p900 }};
        }
        @keyframes gradient { 0%,100% { background-position: 0% center; } 50% { background-position: 100% center; } }
        .animate-gradient { animation: gradient 6s ease infinite; }
    </style>
    
    {{-- SEO Meta Tags --}}
    <meta name="theme-color" content="{{ $primaryColor }}">
    <meta name="format-detection" content="telephone=no">
    <meta name="author" content="{{ $siteName }}">
    <link rel="alternate" hreflang="fr" href="{{ url()->current() }}">

    @stack('styles')

    {{-- Data pour JS --}}
    <meta name="cart-count" content="{{ \App\Models\Cart::getOrCreate(session()->getId(), auth()->user()?->customer)->items_count ?? 0 }}">
    <meta name="checkout-url" content="{{ route('checkout.index') }}">
    <meta name="cart-drawer-url" content="{{ route('cart.drawer') }}">

    {{-- Structured Data JSON-LD --}}
    @include('front.partials.structured-data')

    {{-- Schemas additionnels injectés par les pages enfants --}}
    @stack('schema')
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen overflow-x-hidden" 
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
                    <div class="w-5 h-5 bg-indigo-600 rounded-md flex items-center justify-center shrink-0">
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
                    'bg-primary-50 border-primary-200 text-primary-800': notification.type === 'info'
                }"
                class="flex items-center gap-3 px-4 py-3 rounded-xl border shadow-md min-w-[calc(100vw-2rem)] sm:min-w-[300px]"
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
        class="bg-primary-600 text-white relative overflow-hidden">
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
        <div class="absolute inset-0 bg-slate-900/75" @click="close()"></div>

        <!-- Modal popup -->
        <div x-show="show && !dismissed"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md overflow-hidden rounded-xl shadow-xl border border-slate-200"
             @click.stop>
            <!-- Bouton fermer -->
            <button type="button" @click="close()"
                class="absolute top-3 right-3 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-white hover:bg-slate-100 text-slate-500 hover:text-slate-800 shadow-sm transition-colors duration-150 border border-slate-200">
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
    @include('partials.front.header')
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
    <main class="min-h-screen pb-16 lg:pb-0">
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

    @include('partials.front.footer')
    @stack('scripts')

    <script>
        // legacy helper — stores are in app.js
        function updateCartCount(count) {
            if (window.Alpine?.store('cart')) Alpine.store('cart').count = count;
        }
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
                content_ids: [String(product.id)], content_type: 'product',
                content_name: product.name || '', content_category: product.category || '',
                value: product.price, currency: 'XOF'
            }),
            addToCart: (product, qty) => fbq('track', 'AddToCart', {
                content_ids: [String(product.id)], content_type: 'product',
                content_name: product.name || '',
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


    {{-- ===== CART DRAWER Livewire ===== --}}
    @livewire('cart.drawer')


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
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-900">Installer l'application</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Accès rapide depuis votre écran d'accueil, mode plein écran.</p>
                            <div class="flex items-center gap-2 mt-3">
                                <button @click="installApp()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors duration-150">
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
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-900">Installer l'application</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Appuyez sur
                                <svg class="inline w-4 h-4 text-primary-500 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
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
        // Enregistrement du Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .catch(() => {});
            });
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
    @endif

    {{-- ── Bottom Nav Mobile (PWA) ───────────────────────────────────────────── --}}
    @if(!request()->is('admin/*') && !request()->is('login') && !request()->is('register'))
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/90 backdrop-blur-xl border-t border-slate-900/[0.07] shadow-[0_-1px_12px_rgba(0,0,0,0.07)]"
         style="padding-bottom:env(safe-area-inset-bottom,0px)">
        <div class="grid grid-cols-4 h-14">

            {{-- Accueil --}}
            <a href="{{ route('home') }}"
               class="front-bnav-item {{ request()->routeIs('home') ? 'front-bnav-active' : '' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('home') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Accueil</span>
            </a>

            {{-- Boutique --}}
            <a href="{{ route('shop.index') }}"
               class="front-bnav-item {{ request()->routeIs('shop.*') ? 'front-bnav-active' : '' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('shop.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Boutique</span>
            </a>

            {{-- Panier --}}
            <button x-data="{ get count() { return window.Alpine?.store('cart')?.count ?? 0; } }"
                    @click="$dispatch('open-cart-drawer')"
                    class="front-bnav-item relative">
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="count > 0" x-text="count"
                          class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-primary-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none"></span>
                </div>
                <span>Panier</span>
            </button>

            {{-- Compte --}}
            @auth
            <a href="{{ route('account.dashboard') }}"
               class="front-bnav-item {{ request()->routeIs('account.*') ? 'front-bnav-active' : '' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('account.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Compte</span>
            </a>
            @else
            <a href="{{ route('login') }}"
               class="front-bnav-item {{ request()->routeIs('login') ? 'front-bnav-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span>Connexion</span>
            </a>
            @endauth

        </div>
    </nav>
    <style>
        .front-bnav-item {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 3px; font-size: 10px; font-weight: 600; color: #94a3b8;
            transition: color 0.2s; -webkit-tap-highlight-color: transparent;
            touch-action: manipulation; user-select: none; cursor: pointer;
            border: none; background: none; width: 100%;
        }
        .front-bnav-item:active { transform: scale(0.93); }
        .front-bnav-active { color: var(--color-primary-600) !important; }
        @supports (backdrop-filter: blur(20px)) {
            .front-bnav-item svg { transition: transform 0.2s; }
            .front-bnav-active svg { transform: scale(1.1); }
        }
    </style>
    @endif

</body>
</html>
