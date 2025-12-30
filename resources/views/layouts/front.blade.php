<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased" 
      x-data="{ 
          mobileMenuOpen: false, 
          searchOpen: false,
          cartOpen: false
      }"
      x-init="$store.cart.sync()">
    
    @php
        $cart = \App\Models\Cart::getOrCreate(session()->getId(), auth()->user()?->customer);
        $categories = \App\Models\Category::active()->whereNull('parent_id')->with('children')->orderBy('order')->take(6)->get();
        
        // Récupérer les informations de contact depuis les paramètres
        $sitePhone = \App\Models\Setting::get('contact_phone', '+225 07 00 00 00 00');
        $siteEmail = \App\Models\Setting::get('contact_email', 'contact@chamse.ci');
        $siteAddress = \App\Models\Setting::get('contact_address', 'Abidjan, Côte d\'Ivoire');
        
        // Récupérer la barre d'annonce active
        $announcementBanners = \App\Models\Banner::active()->position('announcement_bar')->orderBy('order')->get();
    @endphp

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
                class="flex items-center gap-3 px-4 py-3 rounded-lg border shadow-lg min-w-[300px]"
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
            dismissed: localStorage.getItem('announcement_dismissed_{{ $announcementBanners->first()->id ?? 0 }}') === 'true'
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
                    
                    <button @click="dismissed = true; localStorage.setItem('announcement_dismissed_{{ $announcementBanners->first()->id ?? 0 }}', 'true')" 
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

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <!-- Top bar avec informations dynamiques -->
        <div class="bg-slate-900 text-slate-300 text-xs py-2 hidden lg:block">
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
            <div class="flex items-center justify-between py-4">
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
                    <form action="{{ route('shop.index') }}" method="GET" class="relative w-full">
                        <input 
                            type="search" 
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher un produit..." 
                            class="w-full pl-12 pr-4 py-3 bg-slate-100 border-0 rounded-full text-sm focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all"
                        >
                        <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
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
                    <a href="#" class="p-2 text-slate-600 hover:text-primary-600 transition-colors relative group hidden sm:block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>
                    
                    <!-- Cart -->
                    <a 
                        href="{{ route('cart.index') }}"
                        class="p-2 text-slate-600 hover:text-primary-600 transition-colors relative"
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
                    </a>
                    
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
                <ul class="flex items-center gap-8 py-3">
                    <li>
                        <a href="{{ route('home') }}" class="text-sm font-medium {{ request()->routeIs('home') ? 'text-primary-600' : 'text-slate-900 hover:text-primary-600' }} transition-colors">
                            Accueil
                        </a>
                    </li>
                    <li x-data="{ open: false }" class="relative">
                        <button 
                            @click="open = !open" 
                            @click.away="open = false"
                            class="flex items-center gap-1 text-sm font-medium {{ request()->routeIs('shop.*') ? 'text-primary-600' : 'text-slate-700 hover:text-primary-600' }} transition-colors"
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
                        <a href="{{ route('shop.index') }}" class="text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors">
                            Boutique
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors">
                            Nouveautés
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors">
                            Promotions
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors">
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

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-3">
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
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl flex items-center gap-3">
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

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-16">
        <!-- Newsletter -->
        <div class="border-b border-slate-800">
            <div class="container mx-auto px-4 py-12">
                <div class="max-w-2xl mx-auto text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">Restez informé</h3>
                    <p class="text-slate-400 mb-6">Inscrivez-vous à notre newsletter pour recevoir nos offres exclusives</p>
                    <form class="flex flex-col sm:flex-row gap-3">
                        <input 
                            type="email" 
                            placeholder="Votre adresse email" 
                            class="flex-1 px-5 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >
                        <button type="submit" class="px-8 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                            S'inscrire
                        </button>
                    </form>
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
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto brightness-0 invert">
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
                    <div class="flex items-center gap-4">
                        <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-primary-600 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-primary-600 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-primary-600 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Links -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Liens utiles</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('about') }}" class="text-sm text-slate-400 hover:text-white transition-colors">À propos de nous</a></li>
                        <li><a href="{{ route('shop.index') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Boutique</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Conditions générales</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Politique de confidentialité</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Service client</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('contact') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Centre d'aide</a></li>
                        @auth
                            <li><a href="{{ route('account.orders') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Suivi de commande</a></li>
                        @endauth
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Retours & remboursements</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Livraison</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">FAQ</a></li>
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
                            <a href="tel:{{ $contactPhone }}" class="text-sm text-slate-400 hover:text-white transition-colors">{{ $contactPhone }}</a>
                        </li>
                        @endif
                        @if($contactEmail)
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $contactEmail }}" class="text-sm text-slate-400 hover:text-white transition-colors">{{ $contactEmail }}</a>
                        </li>
                        @endif
                        @if($socialWhatsapp)
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $socialWhatsapp) }}" target="_blank" class="text-sm text-slate-400 hover:text-green-400 transition-colors">WhatsApp</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Bottom bar -->
        <div class="border-t border-slate-800">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-slate-500">
                        © {{ date('Y') }} {{ $siteName }}. Tous droits réservés.
                    </p>
                    <div class="flex items-center gap-4">
                        <!-- Orange Money -->
                        <div class="h-8 px-3 bg-slate-800 rounded flex items-center text-orange-500 font-bold text-sm">
                            Orange Money
                        </div>
                        <!-- MTN MoMo -->
                        <div class="h-8 px-3 bg-slate-800 rounded flex items-center text-yellow-400 font-bold text-sm">
                            MTN MoMo
                        </div>
                        <!-- CinetPay -->
                        <div class="h-8 px-3 bg-slate-800 rounded flex items-center text-green-400 font-bold text-sm">
                            CinetPay
                        </div>
                    </div>
                </div>
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

            // Listen for notification events
            window.addEventListener('show-notification', (e) => {
                const notificationComponent = document.querySelector('[x-data="notification"]');
                if (notificationComponent && notificationComponent._x_dataStack) {
                    notificationComponent._x_dataStack[0].add(e.detail.message, e.detail.type);
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
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
