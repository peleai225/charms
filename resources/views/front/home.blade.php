@extends('layouts.front')

@section('title', \App\Models\Setting::get('site_name', config('app.name')))

@php
    $heroBanners  = \App\Models\Banner::getForPosition('home_hero');
    $promoBanner  = \App\Models\Banner::active()->position('home_middle')->first();
    $productCount = \App\Models\Product::active()->count();
    $orderCount   = \App\Models\Order::count();
    $whatsapp     = \App\Models\Setting::get('social_whatsapp');
    $siteName     = \App\Models\Setting::get('site_name', config('app.name'));
@endphp

@section('promo_banner')
    🎉 <strong>Livraison gratuite</strong> dès 50 000 F CFA d'achat.&nbsp;
    <a href="{{ route('shop.index') }}" class="underline font-medium">Découvrir →</a>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════ --}}
@if($heroBanners->count() > 0)
<section class="relative"
         x-data="{ slide: 0, total: {{ $heroBanners->count() }}, timer: null, progress: 0 }"
         x-init="
            let dur = 6000;
            let step = 30;
            timer = setInterval(() => {
                progress += (step / dur) * 100;
                if (progress >= 100) { progress = 0; slide = (slide + 1) % total; }
            }, step);
         "
         @mouseenter="clearInterval(timer)"
         @mouseleave="
            let dur = 6000; let step = 30;
            timer = setInterval(() => {
                progress += (step / dur) * 100;
                if (progress >= 100) { progress = 0; slide = (slide + 1) % total; }
            }, step);
         ">
    <div class="relative overflow-hidden">
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="relative h-[420px] md:h-[520px] lg:h-[600px]">
            @if($banner->image)
                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-primary-700 via-primary-800 to-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent">
                <div class="container mx-auto px-6 h-full flex items-center">
                    <div class="max-w-xl">
                        @if($banner->title)
                        <h1 class="text-3xl md:text-5xl lg:text-6xl font-extrabold text-white leading-[1.1] mb-4 drop-shadow-lg animate-fade-in-up">
                            {!! nl2br(e($banner->title)) !!}
                        </h1>
                        @endif
                        @if($banner->subtitle)
                        <p class="text-base md:text-lg text-white/80 mb-6 leading-relaxed">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}"
                           class="inline-flex items-center gap-2 px-7 py-3.5 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-xl shadow-xl shadow-primary-900/40 hover:-translate-y-0.5 transition-all duration-300">
                            {{ $banner->button_text }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($heroBanners->count() > 1)
    {{-- Navigation arrows --}}
    <button @click="slide = (slide - 1 + total) % total; progress = 0"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/15 hover:bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all border border-white/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button @click="slide = (slide + 1) % total; progress = 0"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/15 hover:bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all border border-white/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    {{-- Progress bar --}}
    <div class="absolute bottom-0 left-0 right-0 z-20 h-1 bg-white/10">
        <div class="h-full bg-primary-500 transition-all duration-75" :style="'width:' + progress + '%'"></div>
    </div>
    {{-- Dots --}}
    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
        @foreach($heroBanners as $i => $banner)
        <button @click="slide = {{ $i }}; progress = 0"
                :class="slide === {{ $i }} ? 'w-7 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'"
                class="h-2 rounded-full transition-all duration-300"></button>
        @endforeach
    </div>
    @endif
</section>
@else
{{-- Hero par défaut --}}
<section class="relative h-[420px] md:h-[520px] lg:h-[600px] bg-gradient-to-br from-slate-950 via-slate-900 to-primary-950 overflow-hidden flex items-center">
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-primary-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-20 w-[600px] h-[600px] bg-violet-600/15 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-xl">
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500/15 border border-primary-500/25 text-primary-300 rounded-full text-sm font-semibold mb-6 backdrop-blur-sm">
                <span class="w-2 h-2 bg-primary-400 rounded-full animate-pulse"></span>
                Nouvelle collection disponible
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-[1.1] mb-5 tracking-tight">
                Votre boutique
                <span class="block text-primary-400">premium en ligne</span>
            </h1>
            <p class="text-slate-300 text-lg leading-relaxed mb-8 max-w-lg">
                Des produits de qualité sélectionnés avec soin, livrés rapidement partout en Afrique de l'Ouest.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center gap-2 px-7 py-3.5 bg-primary-600 text-white font-bold rounded-xl shadow-xl hover:-translate-y-0.5 transition-all">
                    Explorer la boutique
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                @if($whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-7 py-3.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-xl backdrop-blur-sm transition-all">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BARRE DE CONFIANCE (floating)
═══════════════════════════════════════════════ --}}
<section class="relative z-10 -mt-8 mb-6">
    <div class="container mx-auto px-6">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">
            <div class="flex items-center gap-3 py-4 px-5 group">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Livraison rapide</p>
                    <p class="text-[10px] text-slate-400">24–48h partout</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-4 px-5 group">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Paiement sécurisé</p>
                    <p class="text-[10px] text-slate-400">Mobile Money & CB</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-4 px-5 group">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Satisfait ou remboursé</p>
                    <p class="text-[10px] text-slate-400">30 jours pour changer</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-4 px-5 group">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Support 7j/7</p>
                    <p class="text-[10px] text-slate-400">Réponse rapide</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CATÉGORIES (scroll horizontal style)
═══════════════════════════════════════════════ --}}
@if($featuredCategories->count() > 0)
<section class="py-12 bg-white">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">Nos catégories</h2>
                <p class="text-slate-400 text-sm mt-1">Trouvez ce qu'il vous faut</p>
            </div>
            <a href="{{ route('shop.index') }}"
               class="hidden md:inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors group">
                Tout voir
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @php
            $catCount = $featuredCategories->count();
            $catGrid = match(true) {
                $catCount <= 3 => 'lg:grid-cols-3',
                $catCount == 4 => 'lg:grid-cols-4',
                $catCount == 5 => 'lg:grid-cols-5',
                default => 'lg:grid-cols-6',
            };
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 {{ $catGrid }} gap-4">
            @foreach($featuredCategories as $category)
            <a href="{{ route('shop.category', $category->slug) }}"
               class="group text-center">
                <div class="relative aspect-square rounded-2xl overflow-hidden bg-slate-50 border-2 border-slate-100 group-hover:border-primary-400 transition-all duration-300 group-hover:shadow-lg group-hover:shadow-primary-100/50 mb-2.5">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    @else
                        @php $catIdx = $loop->index % 6; @endphp
                        <div class="w-full h-full flex items-center justify-center
                            @if($catIdx === 0) bg-gradient-to-br from-primary-100 to-primary-200
                            @elseif($catIdx === 1) bg-gradient-to-br from-amber-100 to-amber-200
                            @elseif($catIdx === 2) bg-gradient-to-br from-emerald-100 to-emerald-200
                            @elseif($catIdx === 3) bg-gradient-to-br from-rose-100 to-rose-200
                            @elseif($catIdx === 4) bg-gradient-to-br from-blue-100 to-blue-200
                            @else bg-gradient-to-br from-cyan-100 to-cyan-200
                            @endif">
                            <span class="text-3xl font-black text-slate-300">{{ mb_substr($category->name, 0, 1) }}</span>
                        </div>
                    @endif
                    {{-- Overlay --}}
                    <div class="absolute inset-0 bg-primary-600/0 group-hover:bg-primary-600/10 transition-colors duration-300"></div>
                </div>
                <h3 class="font-semibold text-slate-700 group-hover:text-primary-600 transition-colors text-sm leading-tight">
                    {{ $category->name }}
                </h3>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $category->products_count ?? 0 }} article{{ ($category->products_count ?? 0) > 1 ? 's' : '' }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PRODUITS VEDETTES - avec tabs
═══════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0 || $newProducts->count() > 0 || $saleProducts->count() > 0)
<section class="py-14 bg-slate-50" x-data="{ activeTab: 'featured' }">
    <div class="container mx-auto px-6">
        {{-- Header avec tabs --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">Nos produits</h2>
                <p class="text-slate-400 text-sm mt-1">Découvrez notre sélection</p>
            </div>
            <div class="flex items-center gap-1 bg-white rounded-xl p-1 border border-slate-200 shadow-sm">
                @if($featuredProducts->count() > 0)
                <button @click="activeTab = 'featured'"
                        :class="activeTab === 'featured' ? 'bg-primary-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    Populaires
                </button>
                @endif
                @if($newProducts->count() > 0)
                <button @click="activeTab = 'new'"
                        :class="activeTab === 'new' ? 'bg-primary-600 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    Nouveautés
                </button>
                @endif
                @if($saleProducts->count() > 0)
                <button @click="activeTab = 'sale'"
                        :class="activeTab === 'sale' ? 'bg-red-500 text-white shadow-md' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    Promos
                </button>
                @endif
            </div>
        </div>

        {{-- Tab: Produits vedettes --}}
        @if($featuredProducts->count() > 0)
        <div x-show="activeTab === 'featured'" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
                @foreach($featuredProducts as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tab: Nouveautés --}}
        @if($newProducts->count() > 0)
        <div x-show="activeTab === 'new'" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
                @foreach($newProducts as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tab: Promotions --}}
        @if($saleProducts->count() > 0)
        <div x-show="activeTab === 'sale'" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
                @foreach($saleProducts as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
        @endif

        {{-- CTA mobile --}}
        <div class="text-center mt-8">
            <a href="{{ route('shop.index') }}"
               class="inline-flex items-center gap-2 px-7 py-3 bg-primary-600 text-white font-bold rounded-xl hover:-translate-y-0.5 transition-all shadow-lg shadow-primary-500/25 text-sm">
                Voir tous les produits
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANNIÈRE PROMO (milieu)
═══════════════════════════════════════════════ --}}
@if($promoBanner)
<section class="py-6 bg-white">
    <div class="container mx-auto px-6">
        <a href="{{ $promoBanner->link ?? '#' }}"
           class="group relative flex rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-500">
            @if($promoBanner->image)
                <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}"
                     class="w-full h-48 md:h-64 object-cover group-hover:scale-[1.02] transition-transform duration-700" loading="lazy">
            @else
                <div class="w-full h-48 md:h-64 bg-gradient-to-r from-primary-700 to-violet-700"></div>
            @endif
            @if($promoBanner->title)
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/25 to-transparent flex items-center">
                <div class="p-6 md:p-10">
                    @if($promoBanner->subtitle)
                    <p class="text-primary-300 text-xs font-bold uppercase tracking-widest mb-2">{{ $promoBanner->subtitle }}</p>
                    @endif
                    <h3 class="text-xl md:text-3xl font-extrabold text-white mb-3 leading-tight">{{ $promoBanner->title }}</h3>
                    @if($promoBanner->button_text)
                    <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-slate-900 font-bold rounded-lg group-hover:bg-primary-500 group-hover:text-white transition-all duration-300 text-sm">
                        {{ $promoBanner->button_text }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </a>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     FLASH SALE - Countdown Timer (Urgence)
═══════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="py-10 bg-gradient-to-r from-red-600 via-rose-600 to-orange-500 relative overflow-hidden">
    {{-- Decorative elements --}}
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-60 h-60 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10"
         x-data="{
            hours: 0, minutes: 0, seconds: 0,
            init() {
                const update = () => {
                    const now = new Date();
                    const end = new Date(now);
                    end.setHours(23, 59, 59, 999);
                    const diff = end - now;
                    this.hours = Math.floor(diff / 3600000);
                    this.minutes = Math.floor((diff % 3600000) / 60000);
                    this.seconds = Math.floor((diff % 60000) / 1000);
                };
                update();
                setInterval(update, 1000);
            }
         }">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="text-white">
                    <h3 class="text-xl md:text-2xl font-extrabold">Ventes Flash</h3>
                    <p class="text-white/80 text-sm">Offres limitées — se terminent aujourd'hui !</p>
                </div>
            </div>

            {{-- Countdown --}}
            <div class="flex items-center gap-2">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 text-center min-w-[60px]">
                    <span class="text-2xl font-black text-white block leading-none" x-text="String(hours).padStart(2, '0')">00</span>
                    <span class="text-[10px] text-white/70 uppercase tracking-wider font-medium">Heures</span>
                </div>
                <span class="text-2xl font-bold text-white/60">:</span>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 text-center min-w-[60px]">
                    <span class="text-2xl font-black text-white block leading-none" x-text="String(minutes).padStart(2, '0')">00</span>
                    <span class="text-[10px] text-white/70 uppercase tracking-wider font-medium">Min</span>
                </div>
                <span class="text-2xl font-bold text-white/60">:</span>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 text-center min-w-[60px]">
                    <span class="text-2xl font-black text-white block leading-none" x-text="String(seconds).padStart(2, '0')">00</span>
                    <span class="text-[10px] text-white/70 uppercase tracking-wider font-medium">Sec</span>
                </div>
            </div>

            <a href="{{ route('shop.index', ['sale' => 1]) }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-red-600 font-extrabold rounded-xl hover:bg-red-50 hover:-translate-y-0.5 transition-all shadow-lg text-sm whitespace-nowrap">
                Voir les offres
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     TÉMOIGNAGES (Social Proof)
═══════════════════════════════════════════════ --}}
<section class="py-14 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">Ce que disent nos clients</h2>
            <p class="text-slate-400 text-sm mt-1">Des milliers de clients satisfaits</p>
        </div>

        <div class="relative" x-data="{ current: 0, total: 4 }" x-init="setInterval(() => current = (current + 1) % total, 5000)">
            <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-out" :style="'transform: translateX(-' + (current * 100) + '%)'">

                    {{-- Témoignage 1 --}}
                    <div class="w-full flex-shrink-0 px-4">
                        <div class="max-w-2xl mx-auto text-center">
                            <div class="flex justify-center gap-1 mb-4">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-slate-600 text-base italic leading-relaxed mb-4">"Livraison super rapide et produits de qualité. Je recommande vivement cette boutique !"</p>
                            <p class="font-bold text-slate-800">Aminata K.</p>
                            <p class="text-xs text-slate-400">Abidjan, Côte d'Ivoire</p>
                        </div>
                    </div>

                    {{-- Témoignage 2 --}}
                    <div class="w-full flex-shrink-0 px-4">
                        <div class="max-w-2xl mx-auto text-center">
                            <div class="flex justify-center gap-1 mb-4">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-slate-600 text-base italic leading-relaxed mb-4">"Le service client est exceptionnel. J'avais un souci de taille et ils ont réglé ça en 24h. Bravo !"</p>
                            <p class="font-bold text-slate-800">Moussa D.</p>
                            <p class="text-xs text-slate-400">Dakar, Sénégal</p>
                        </div>
                    </div>

                    {{-- Témoignage 3 --}}
                    <div class="w-full flex-shrink-0 px-4">
                        <div class="max-w-2xl mx-auto text-center">
                            <div class="flex justify-center gap-1 mb-4">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-slate-600 text-base italic leading-relaxed mb-4">"Des produits authentiques et un emballage soigné. C'est la troisième commande et toujours aussi bien."</p>
                            <p class="font-bold text-slate-800">Fatou B.</p>
                            <p class="text-xs text-slate-400">Bamako, Mali</p>
                        </div>
                    </div>

                    {{-- Témoignage 4 --}}
                    <div class="w-full flex-shrink-0 px-4">
                        <div class="max-w-2xl mx-auto text-center">
                            <div class="flex justify-center gap-1 mb-4">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-slate-600 text-base italic leading-relaxed mb-4">"Je commande depuis le Burkina et je reçois toujours mes colis en bon état. Le suivi WhatsApp est top !"</p>
                            <p class="font-bold text-slate-800">Ibrahim S.</p>
                            <p class="text-xs text-slate-400">Ouagadougou, Burkina Faso</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dots --}}
            <div class="flex justify-center gap-2 mt-6">
                <template x-for="i in total" :key="i">
                    <button @click="current = i - 1"
                            :class="current === i - 1 ? 'w-6 bg-primary-600' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                            class="h-2 rounded-full transition-all duration-300"></button>
                </template>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     POURQUOI NOUS CHOISIR
═══════════════════════════════════════════════ --}}
<section class="py-14 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">Pourquoi nous choisir ?</h2>
            <p class="text-slate-400 text-sm mt-1">L'expérience {{ $siteName }}</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            {{-- Qualité --}}
            <div class="group bg-white p-5 md:p-6 rounded-2xl border border-slate-100 hover:border-primary-200 hover:shadow-xl hover:shadow-primary-100/40 hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-1 text-sm md:text-base">Qualité garantie</h3>
                <p class="text-xs md:text-sm text-slate-500 leading-relaxed">Chaque produit est vérifié et sélectionné avec soin</p>
            </div>
            {{-- Prix --}}
            <div class="group bg-white p-5 md:p-6 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-100/40 hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-emerald-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-1 text-sm md:text-base">Meilleurs prix</h3>
                <p class="text-xs md:text-sm text-slate-500 leading-relaxed">Prix compétitifs et promotions régulières</p>
            </div>
            {{-- Livraison --}}
            <div class="group bg-white p-5 md:p-6 rounded-2xl border border-slate-100 hover:border-amber-200 hover:shadow-xl hover:shadow-amber-100/40 hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-1 text-sm md:text-base">Livraison express</h3>
                <p class="text-xs md:text-sm text-slate-500 leading-relaxed">Expédition rapide dans toute l'Afrique de l'Ouest</p>
            </div>
            {{-- SAV --}}
            <div class="group bg-white p-5 md:p-6 rounded-2xl border border-slate-100 hover:border-violet-200 hover:shadow-xl hover:shadow-violet-100/40 hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-violet-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-1 text-sm md:text-base">SAV réactif</h3>
                <p class="text-xs md:text-sm text-slate-500 leading-relaxed">Support WhatsApp et téléphone 7j/7</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     BANNIÈRE WHATSAPP
═══════════════════════════════════════════════ --}}
@if($whatsapp)
<section class="py-10 bg-gradient-to-r from-[#075e54] to-[#128c7e] relative overflow-hidden">
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
    <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6 relative">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <div class="text-white">
                <h3 class="text-lg md:text-xl font-bold">Commandez sur WhatsApp</h3>
                <p class="text-white/80 text-sm">Réponse rapide · Conseil personnalisé · Mobile Money</p>
            </div>
        </div>
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Bonjour ! Je souhaite passer une commande sur ' . $siteName) }}"
           target="_blank"
           class="flex-shrink-0 inline-flex items-center gap-2 px-7 py-3.5 bg-white text-[#075e54] font-extrabold rounded-xl hover:bg-green-50 hover:-translate-y-0.5 transition-all shadow-xl text-sm">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Écrire sur WhatsApp
        </a>
    </div>
</section>
@endif

{{-- Newsletter est dans le footer layout, pas besoin de dupliquer ici --}}

{{-- ═══════════════════════════════════════════════
     NOTIFICATION FOMO (Social Proof - Popup)
═══════════════════════════════════════════════ --}}
<div x-data="{
        show: false,
        names: ['Aminata', 'Moussa', 'Fatou', 'Ibrahim', 'Awa', 'Ousmane', 'Mariam', 'Boubacar'],
        cities: ['Abidjan', 'Dakar', 'Bamako', 'Ouagadougou', 'Lomé', 'Cotonou', 'Niamey', 'Conakry'],
        times: ['2 min', '5 min', '8 min', '12 min', '15 min', '23 min'],
        name: '', city: '', time: '',
        init() {
            const trigger = () => {
                this.name = this.names[Math.floor(Math.random() * this.names.length)];
                this.city = this.cities[Math.floor(Math.random() * this.cities.length)];
                this.time = this.times[Math.floor(Math.random() * this.times.length)];
                this.show = true;
                setTimeout(() => this.show = false, 4000);
            };
            setTimeout(trigger, 8000);
            setInterval(trigger, 25000);
        }
     }"
     x-show="show" x-cloak
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-4 left-4 z-50 bg-white rounded-xl shadow-2xl shadow-slate-900/20 border border-slate-100 p-3 max-w-xs flex items-center gap-3">
    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
    </div>
    <div class="min-w-0">
        <p class="text-xs text-slate-800 font-semibold truncate"><span x-text="name"></span> de <span x-text="city"></span></p>
        <p class="text-[10px] text-slate-400">a commandé il y a <span x-text="time" class="text-emerald-600 font-medium"></span></p>
    </div>
    <button @click="show = false" class="flex-shrink-0 text-slate-300 hover:text-slate-500">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- État vide --}}
@if($featuredProducts->count() === 0 && $newProducts->count() === 0)
<section class="py-20 bg-slate-50">
    <div class="container mx-auto px-6 text-center">
        <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Boutique en cours de création</h2>
        <p class="text-slate-500 max-w-md mx-auto mb-6 text-sm">Nos produits arrivent très bientôt !</p>
        @auth @if(in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff']))
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-lg text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter des produits
        </a>
        @endif @endauth
    </div>
</section>
@endif

@endsection
