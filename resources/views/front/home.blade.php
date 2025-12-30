@extends('layouts.front')

@section('title', 'Accueil - ' . config('app.name'))

@php
    $heroBanners = \App\Models\Banner::getForPosition('home_hero');
    $promoBanner = \App\Models\Banner::active()->position('home_middle')->first();
@endphp

@section('promo_banner')
    🎉 <strong>Bienvenue !</strong> Livraison gratuite dès 50 000 F CFA d'achat. <a href="{{ route('shop.index') }}" class="underline font-medium">Découvrir</a>
@endsection

@section('content')
    <!-- Hero Section avec Bannières dynamiques -->
    @if($heroBanners->count() > 0)
    <section class="relative" x-data="{ currentSlide: 0, totalSlides: {{ $heroBanners->count() }} }" x-init="setInterval(() => { currentSlide = (currentSlide + 1) % totalSlides }, 5000)">
        <!-- Slider -->
        <div class="relative overflow-hidden">
            @foreach($heroBanners as $index => $banner)
            <div 
                x-show="currentSlide === {{ $index }}"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 transform translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform -translate-x-full"
                class="relative"
            >
                <div class="relative h-[400px] md:h-[500px] lg:h-[600px]">
                    <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    
                    <!-- Overlay avec contenu -->
                    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent">
                        <div class="container mx-auto px-4 h-full flex items-center">
                            <div class="max-w-xl">
                                @if($banner->title)
                                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-4">
                                    {{ $banner->title }}
                                </h1>
                                @endif
                                
                                @if($banner->subtitle)
                                <p class="text-lg md:text-xl text-white/90 mb-8">
                                    {{ $banner->subtitle }}
                                </p>
                                @endif
                                
                                @if($banner->link && $banner->button_text)
                                <a href="{{ $banner->link }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all shadow-lg">
                                    {{ $banner->button_text }}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Navigation dots -->
        @if($heroBanners->count() > 1)
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
            @foreach($heroBanners as $index => $banner)
            <button 
                @click="currentSlide = {{ $index }}"
                :class="currentSlide === {{ $index }} ? 'bg-white w-8' : 'bg-white/50 w-3'"
                class="h-3 rounded-full transition-all duration-300"
            ></button>
            @endforeach
        </div>
        
        <!-- Navigation arrows -->
        <button @click="currentSlide = (currentSlide - 1 + totalSlides) % totalSlides" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-colors z-20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button @click="currentSlide = (currentSlide + 1) % totalSlides" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-colors z-20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        @endif
    </section>
    @else
    <!-- Hero Section par défaut (sans bannière) -->
    <section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-primary-900 overflow-hidden">
        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)"/>
            </svg>
        </div>
        
        <!-- Floating shapes -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-primary-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-amber-500/20 rounded-full blur-3xl animate-pulse"></div>
        
        <div class="container mx-auto px-4 py-16 lg:py-24 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="text-center lg:text-left">
                    <span class="inline-block px-4 py-2 bg-primary-500/20 text-primary-300 rounded-full text-sm font-medium mb-6">
                        ✨ Nouvelle collection disponible
                    </span>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                        Découvrez notre
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-amber-400">
                            univers unique
                        </span>
                    </h1>
                    
                    <p class="text-lg text-slate-300 mb-8 max-w-xl mx-auto lg:mx-0">
                        Des produits de qualité sélectionnés avec soin pour vous offrir 
                        une expérience shopping exceptionnelle.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-600/30 hover:shadow-xl hover:-translate-y-0.5">
                            Explorer la boutique
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex items-center justify-center lg:justify-start gap-8 mt-12 pt-8 border-t border-white/10">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-white">{{ \App\Models\Product::active()->count() }}</p>
                            <p class="text-sm text-slate-400">Produits</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-white">{{ \App\Models\Category::active()->count() }}</p>
                            <p class="text-sm text-slate-400">Catégories</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-white">{{ \App\Models\Order::count() }}</p>
                            <p class="text-sm text-slate-400">Commandes</p>
                        </div>
                    </div>
                </div>
                
                <!-- Hero image -->
                <div class="relative hidden lg:block">
                    <div class="relative z-10">
                        @if($featuredProducts->first()?->primary_image_url)
                            <img src="{{ $featuredProducts->first()->primary_image_url }}" alt="" class="w-full h-auto rounded-3xl shadow-2xl object-cover" style="max-height: 500px;">
                        @else
                            <div class="w-full h-96 bg-gradient-to-br from-primary-600 to-primary-800 rounded-3xl flex items-center justify-center">
                                <svg class="w-32 h-32 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Floating card -->
                        <div class="absolute -left-8 top-1/4 bg-white rounded-2xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">Livraison gratuite</p>
                                    <p class="text-sm text-slate-500">Dès 50 000 F</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating card 2 -->
                        <div class="absolute -right-4 bottom-1/4 bg-white rounded-2xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">Paiement sécurisé</p>
                                    <p class="text-sm text-slate-500">Mobile Money & CB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    
    <!-- Bannière promotionnelle au milieu (si configurée) -->
    @if($promoBanner)
    <section class="py-8">
        <div class="container mx-auto px-4">
            <a href="{{ $promoBanner->link ?? '#' }}" class="block relative rounded-2xl overflow-hidden group">
                <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}" class="w-full h-48 md:h-64 object-cover group-hover:scale-105 transition-transform duration-500">
                @if($promoBanner->title)
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                    <div class="p-8">
                        <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ $promoBanner->title }}</h3>
                        @if($promoBanner->subtitle)
                        <p class="text-white/80">{{ $promoBanner->subtitle }}</p>
                        @endif
                        @if($promoBanner->button_text)
                        <span class="inline-block mt-4 px-6 py-2 bg-white text-slate-900 font-medium rounded-lg group-hover:bg-primary-500 group-hover:text-white transition-colors">
                            {{ $promoBanner->button_text }}
                        </span>
                        @endif
                    </div>
                </div>
                @endif
            </a>
        </div>
    </section>
    @endif

    <!-- Catégories -->
    @if($featuredCategories->count() > 0)
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Nos catégories</h2>
                <p class="text-slate-600">Explorez notre sélection de produits par catégorie</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach($featuredCategories as $category)
                <a href="{{ route('shop.category', $category->slug) }}" class="group text-center">
                    <div class="aspect-square bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl mb-4 overflow-hidden group-hover:shadow-lg transition-all">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <h3 class="font-medium text-slate-900 group-hover:text-primary-600 transition-colors">{{ $category->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $category->products()->count() }} produits</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Produits mis en avant -->
    @if($featuredProducts->count() > 0)
    <section class="py-16 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Produits vedettes</h2>
                    <p class="text-slate-600">Nos produits les plus populaires</p>
                </div>
                <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium">
                    Voir tout
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
            
            <div class="text-center mt-8 md:hidden">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                    Voir tous les produits
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Nouveautés -->
    @if($newProducts->count() > 0)
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Nouveautés</h2>
                    <p class="text-slate-600">Les derniers produits ajoutés</p>
                </div>
                <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="hidden md:inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium">
                    Voir tout
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($newProducts as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Promotions -->
    @if($saleProducts->count() > 0)
    <section class="py-16 bg-gradient-to-r from-red-600 to-orange-500">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">🔥 Promotions</h2>
                    <p class="text-red-100">Profitez de nos meilleures offres</p>
                </div>
                <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-white font-medium hover:underline">
                    Voir tout
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($saleProducts as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Avantages -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Livraison rapide</h3>
                    <p class="text-slate-600 text-sm">Expédition sous 24-48h dans toute l'Afrique de l'Ouest</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Paiement sécurisé</h3>
                    <p class="text-slate-600 text-sm">Mobile Money, carte bancaire et paiement à la livraison</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Retours gratuits</h3>
                    <p class="text-slate-600 text-sm">30 jours pour changer d'avis</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">Support 24/7</h3>
                    <p class="text-slate-600 text-sm">Une équipe à votre écoute</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Newsletter -->
    <section class="py-16 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Restez informé</h2>
            <p class="text-primary-100 mb-8 max-w-xl mx-auto">Inscrivez-vous à notre newsletter pour recevoir nos offres exclusives et nos dernières nouveautés.</p>
            
            <form class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                <input type="email" placeholder="Votre adresse email" class="flex-1 px-6 py-4 rounded-xl focus:ring-2 focus:ring-white/50 focus:outline-none">
                <button type="submit" class="px-8 py-4 bg-white text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition-colors">
                    S'inscrire
                </button>
            </form>
        </div>
    </section>

    <!-- Message si pas de produits -->
    @if($featuredProducts->count() === 0 && $newProducts->count() === 0)
    <section class="py-24 bg-slate-50">
        <div class="container mx-auto px-4 text-center">
            <div class="w-24 h-24 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-4">Boutique en cours de création</h2>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">Nos produits arrivent bientôt ! En attendant, n'hésitez pas à vous inscrire à notre newsletter.</p>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ajouter des produits (Admin)
            </a>
        </div>
    </section>
    @endif
@endsection
