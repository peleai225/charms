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
                <div class="hidden lg:flex flex-1 max-w-xl mx-8" x-data="searchSuggest()" @click.away="showResults = false">
                    <form action="{{ route('shop.index') }}" method="GET" class="relative w-full group" @submit="showResults = false">
                        <input 
                            type="search" 
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher un produit, une categorie..." 
                            class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200/80 rounded-full text-sm focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 focus:bg-white transition-all duration-200 placeholder:text-slate-400"
                            x-model="query"
                            @input.debounce.300ms="search()"
                            @focus="if(results.length) showResults = true"
                            @keydown.escape="showResults = false"
                            autocomplete="off"
                        >
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-primary-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-primary-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>

                        <!-- Search suggestions dropdown -->
                        <div x-show="showResults && results.length > 0" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50 max-h-80 overflow-y-auto">
                            <template x-for="item in results" :key="item.id">
                                <a :href="item.url" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" x-show="item.image">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-800 truncate" x-text="item.name"></p>
                                        <p class="text-xs text-slate-400" x-text="item.category"></p>
                                    </div>
                                    <span class="text-sm font-bold text-primary-600 shrink-0" x-text="item.price"></span>
                                </a>
                            </template>
                            <a :href="'{{ route('shop.index') }}?search=' + encodeURIComponent(query)" class="block text-center py-2.5 text-sm font-semibold text-primary-600 hover:bg-primary-50 transition-colors border-t border-slate-100">
                                Voir tous les resultats
                            </a>
                        </div>
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
                    <a href="{{ route('account.wishlist.index') }}" class="p-2 text-slate-600 hover:text-rose-500 transition-colors relative group hidden sm:block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        @php
                            $wishlistCount = auth()->check() && optional(auth()->user()->customer)->id 
                                ? \App\Models\Wishlist::where('customer_id', auth()->user()->customer->id)->count() 
                                : 0;
                        @endphp
                        @if($wishlistCount > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $wishlistCount }}</span>
                        @endif
                    </a>
                    
                    <!-- Cart (ouvre le drawer) -->
                    <button
                        @click="$store.cartDrawer.open()"
                        class="p-2 text-slate-600 hover:text-primary-600 transition-colors relative"
                        aria-label="Ouvrir le panier"
                        data-cart-icon
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span x-text="$store.cart.count"
                              x-show="$store.cart.count > 0"
                              x-transition:enter="transition ease-out duration-200"
                              x-transition:enter-start="opacity-0 scale-50"
                              x-transition:enter-end="opacity-100 scale-100"
                              data-cart-count
                              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center transition-transform"></span>
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
            <div x-show="searchOpen" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="lg:hidden pb-4">
                <form action="{{ route('shop.index') }}" method="GET" class="relative">
                    <input 
                        type="search" 
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Rechercher un produit, une categorie..." 
                        class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 focus:bg-white transition-all"
                        x-ref="mobileSearch"
                        @keydown.escape="searchOpen = false"
                    >
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-primary-600 text-white rounded-xl px-3 py-2 hover:bg-primary-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
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
                                    @if(($category->products_count ?? 0) > 0)
                                        <span class="text-slate-400 text-xs">({{ $category->products_count }})</span>
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
