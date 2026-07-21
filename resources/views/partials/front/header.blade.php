<header
    x-data="{ mobileMenuOpen: false, searchOpen: false }"
    class="bg-white sticky top-0 z-50 border-b border-slate-100 shadow-sm"
>
    {{-- Top bar desktop --}}
    <div class="hidden lg:block border-b border-slate-100">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2 text-xs text-slate-500">
                <div class="flex items-center gap-5">
                    @if($sitePhone)
                    <a href="tel:{{ $sitePhone }}" class="flex items-center gap-1.5 hover:text-slate-800 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $sitePhone }}
                    </a>
                    @endif
                    @if($siteEmail)
                    <a href="mailto:{{ $siteEmail }}" class="flex items-center gap-1.5 hover:text-slate-800 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $siteEmail }}
                    </a>
                    @endif
                </div>
                <div class="flex items-center gap-5">
                    <a href="{{ route('order-tracking.index') }}" wire:navigate class="hover:text-slate-800 transition-colors">Suivi commande</a>
                    <a href="{{ route('contact') }}" wire:navigate class="hover:text-slate-800 transition-colors">Aide</a>
                    @php $waTop = \App\Models\Setting::get('social_whatsapp'); @endphp
                    @if($waTop)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $waTop) }}" target="_blank" class="flex items-center gap-1 text-[#25D366] hover:opacity-80 transition-opacity font-medium">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main header row --}}
    <div class="container mx-auto px-4">
        <div class="flex items-center gap-3 py-3.5">

            {{-- Logo --}}
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0 mr-2">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-9 w-auto">
                @else
                    <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <span class="text-white font-bold text-base leading-none">{{ substr($siteName, 0, 1) }}</span>
                    </div>
                    <span class="text-xl font-semibold text-slate-900 tracking-tight hidden sm:block">{{ $siteName }}</span>
                @endif
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-0.5 flex-1">
                <a href="{{ route('home') }}"
                   class="px-3.5 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Accueil
                </a>
                <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                    <button @click="open = !open" @click.away="open = false"
                            class="flex items-center gap-1 px-3.5 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('shop.category') ? 'text-blue-600 bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        Catégories
                        <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="absolute top-full left-0 mt-1.5 w-60 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50">
                        @foreach($categories->take(5) as $category)
                        <a href="{{ route('shop.category', $category->slug) }}"
                           class="flex items-center justify-between px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                            <span>{{ $category->name }}</span>
                            @if(($category->products_count ?? 0) > 0)
                            <span class="text-xs text-slate-400 tabular-nums">{{ $category->products_count }}</span>
                            @endif
                        </a>
                        @endforeach
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <a href="{{ route('shop.index') }}" wire:navigate class="block px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                                Toutes les catégories →
                            </a>
                        </div>
                    </div>
                </div>
                <a href="{{ route('shop.index') }}"
                   class="px-3.5 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('shop.index') ? 'text-blue-600 bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Boutique
                </a>
                <a href="{{ route('shop.index', ['sort' => 'newest']) }}"
                   class="px-3.5 py-2 text-sm font-medium rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                    Nouveautés
                </a>
                <a href="{{ route('shop.index', ['on_sale' => 1]) }}"
                   class="px-3.5 py-2 text-sm font-medium rounded-md text-red-500 hover:text-red-600 hover:bg-red-50 transition-colors">
                    Promotions
                </a>
            </nav>

            {{-- Desktop search --}}
            <div class="hidden lg:flex flex-1 max-w-sm" x-data="searchSuggest()" @click.away="showResults = false">
                <form action="{{ route('shop.index') }}" method="GET" class="relative w-full" @submit="showResults = false">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="search" name="search" value="{{ request('search') }}"
                           placeholder="Rechercher…"
                           class="w-full pl-9 pr-10 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:bg-white transition-all"
                           x-model="query"
                           @input.debounce.300ms="search()"
                           @focus="if(results.length) showResults = true"
                           @keydown.escape="showResults = false"
                           autocomplete="off">
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors" aria-label="Rechercher">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                    <div x-show="showResults && results.length > 0" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden z-50 max-h-72 overflow-y-auto">
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">
                                <div class="w-9 h-9 rounded-md bg-slate-100 overflow-hidden shrink-0">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" x-show="item.image">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-slate-400" x-text="item.category"></p>
                                </div>
                                <span class="text-sm font-semibold text-blue-600 shrink-0" x-text="item.price"></span>
                            </a>
                        </template>
                        <a :href="'{{ route('shop.index') }}?search=' + encodeURIComponent(query)"
                           class="block text-center py-2.5 text-xs font-semibold text-blue-600 hover:bg-slate-50 transition-colors border-t border-slate-100">
                            Voir tous les résultats
                        </a>
                    </div>
                </form>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-0.5 ml-auto lg:ml-2">

                {{-- Mobile search --}}
                <button @click="searchOpen = !searchOpen"
                        class="lg:hidden w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors"
                        aria-label="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>

                {{-- Wishlist --}}
                @php
                    $wishlistCount = auth()->check() && optional(auth()->user()->customer)->id
                        ? \App\Models\Wishlist::where('customer_id', auth()->user()->customer->id)->count()
                        : 0;
                @endphp
                <a href="{{ route('account.wishlist.index') }}"
                   class="relative hidden sm:flex w-9 h-9 items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors"
                   aria-label="Liste de souhaits">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    @if($wishlistCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $wishlistCount }}</span>
                    @endif
                </a>

                {{-- Cart --}}
                <button x-data="{ count: {{ \App\Models\Cart::getOrCreate(session()->getId(), auth()->user()?->customer)->items_count ?? 0 }} }"
                        @cart-count-updated.window="count = $event.detail.count"
                        @click="$dispatch('open-cart-drawer')"
                        class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors"
                        aria-label="Ouvrir le panier">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span x-text="count" x-show="count > 0"
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0 scale-50"
                          x-transition:enter-end="opacity-100 scale-100"
                          class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-blue-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none"></span>
                </button>

                {{-- Account --}}
                @auth
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button @click="open = !open" @click.away="open = false"
                            class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors"
                            aria-label="Mon compte">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </button>
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50"
                         style="transform-origin: top right;">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('account.dashboard') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Mon compte
                        </a>
                        <a href="{{ route('account.orders') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Mes commandes
                        </a>
                        <a href="{{ route('account.wishlist.index') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Ma wishlist
                        </a>
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}"
                   class="hidden sm:flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50 rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Connexion
                </a>
                @endauth

                {{-- Mobile burger --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors"
                        aria-label="Menu">
                    <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile search bar --}}
        <div x-show="searchOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden pb-3">
            <form action="{{ route('shop.index') }}" method="GET" class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher un produit…"
                       class="w-full pl-9 pr-12 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:bg-white transition-all"
                       @keydown.escape="searchOpen = false">
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors" aria-label="Rechercher">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="mobileMenuOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-3"
         class="lg:hidden absolute top-full left-0 right-0 bg-white border-t border-slate-100 shadow-xl z-40">
        <div class="container mx-auto px-4 py-3 space-y-0.5">
            <a href="{{ route('home') }}"
               class="block px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-600' : 'text-slate-700 hover:bg-slate-50' }}">
                Accueil
            </a>
            <a href="{{ route('shop.index') }}" wire:navigate class="block px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                Boutique
            </a>
            <div x-data="{ catOpen: false }">
                <button @click="catOpen = !catOpen"
                        class="flex items-center justify-between w-full px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    <span>Catégories</span>
                    <svg class="w-4 h-4 transition-transform duration-150" :class="catOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="catOpen" x-cloak class="pl-4 mt-0.5 space-y-0.5">
                    @foreach($categories as $category)
                    <a href="{{ route('shop.category', $category->slug) }}"
                       class="block px-3 py-2 text-sm text-slate-600 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" wire:navigate class="block px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                Nouveautés
            </a>
            <a href="{{ route('shop.index', ['on_sale' => 1]) }}" wire:navigate class="block px-3 py-2.5 text-sm text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                Promotions
            </a>
            <a href="{{ route('contact') }}" wire:navigate class="block px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                Contact
            </a>
        </div>
        <div class="border-t border-slate-100 mx-4 py-3 space-y-0.5">
            @auth
            <a href="{{ route('account.dashboard') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Mon compte
            </a>
            <a href="{{ route('account.orders') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Mes commandes
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 text-sm text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Déconnexion
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Connexion
            </a>
            <a href="{{ route('register') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Créer un compte
            </a>
            @endauth
        </div>
    </div>
</header>
