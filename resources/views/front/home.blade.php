@extends('layouts.front')

@section('title', \App\Models\Setting::get('site_name', config('app.name')))

@php
    $heroBanners  = \App\Models\Banner::getForPosition('home_hero');
    $promoBanner  = \App\Models\Banner::active()->position('home_middle')->first();
    $productCount = \App\Models\Product::active()->count();
    $whatsapp     = \App\Models\Setting::get('social_whatsapp');
    $siteName     = \App\Models\Setting::get('site_name', config('app.name'));
@endphp

@section('promo_banner')
    🎉 <strong>Livraison gratuite</strong> dès 50 000 F CFA d'achat.&nbsp;
    <a href="{{ route('shop.index') }}" class="underline font-medium">Découvrir →</a>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     HERO — avec produits vedettes
═══════════════════════════════════════════════ --}}
@if($heroBanners->count() > 0)
<section class="relative"
         x-data="{ slide: 0, total: {{ $heroBanners->count() }}, progress: 0 }"
         x-init="setInterval(() => { progress += 0.5; if (progress >= 100) { progress = 0; slide = (slide + 1) % total; } }, 30)">
    <div class="relative overflow-hidden">
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="relative h-[400px] md:h-[480px] lg:h-[540px]">
            @if($banner->image)
                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-slate-900 via-primary-950 to-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent">
                <div class="container mx-auto px-6 h-full flex items-center">
                    <div class="max-w-lg">
                        @if($banner->title)
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-3">{!! nl2br(e($banner->title)) !!}</h1>
                        @endif
                        @if($banner->subtitle)
                        <p class="text-white/75 text-sm md:text-base mb-5">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-lg transition-all text-sm">
                            {{ $banner->button_text }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if($heroBanners->count() > 1)
    <div class="absolute bottom-0 left-0 right-0 z-20 h-0.5 bg-white/10">
        <div class="h-full bg-primary-500 transition-all duration-75" :style="'width:' + progress + '%'"></div>
    </div>
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-1.5">
        @foreach($heroBanners as $i => $banner)
        <button @click="slide = {{ $i }}; progress = 0"
                :class="slide === {{ $i }} ? 'w-6 bg-white' : 'w-2 bg-white/40'"
                class="h-1.5 rounded-full transition-all duration-300"></button>
        @endforeach
    </div>
    @endif
</section>
@else
{{-- Hero par défaut avec grille produits --}}
<section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-primary-950 overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>

    <div class="container mx-auto px-6 py-12 lg:py-16 relative z-10">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            {{-- Texte gauche --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary-500/15 border border-primary-500/25 text-primary-300 rounded-full text-xs font-semibold mb-5">
                    <span class="w-1.5 h-1.5 bg-primary-400 rounded-full animate-pulse"></span>
                    Bienvenue chez {{ $siteName }}
                </div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-4">
                    Découvrez nos
                    <span class="text-primary-400">meilleurs produits</span>
                </h1>
                <p class="text-slate-300 text-sm md:text-base leading-relaxed mb-6 max-w-md">
                    Qualité, prix imbattables et livraison rapide partout en Afrique de l'Ouest.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-bold rounded-lg hover:-translate-y-0.5 transition-all text-sm">
                        Explorer la boutique
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    @if($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-3 border border-white/20 text-white font-semibold rounded-lg hover:bg-white/10 transition-all text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                </div>
                {{-- Stats --}}
                <div class="flex items-center gap-6 mt-8 pt-6 border-t border-white/10">
                    <div>
                        <p class="text-2xl font-black text-white">{{ number_format($productCount, 0, ',', ' ') }}+</p>
                        <p class="text-xs text-slate-400">Produits</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-2xl font-black text-white">24h</p>
                        <p class="text-xs text-slate-400">Livraison</p>
                    </div>
                    <div class="w-px h-8 bg-white/15"></div>
                    <div>
                        <p class="text-2xl font-black text-white">98%</p>
                        <p class="text-xs text-slate-400">Satisfaits</p>
                    </div>
                </div>
            </div>

            {{-- Grille produits droite --}}
            <div class="hidden lg:grid grid-cols-2 gap-3 relative">
                @forelse($featuredProducts->take(4) as $i => $product)
                @php $img = $product->images->where('is_primary', true)->first() ?? $product->images->first(); @endphp
                <a href="{{ route('shop.product', $product->slug) }}"
                   class="group relative rounded-xl overflow-hidden {{ $i === 0 ? 'row-span-2' : '' }} bg-white/5 border border-white/10 hover:border-primary-400/40 transition-all duration-300">
                    @if($img)
                    <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $product->name }}"
                         class="w-full h-full object-cover {{ $i === 0 ? 'min-h-[300px]' : 'h-40' }} group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                    <div class="w-full {{ $i === 0 ? 'min-h-[300px]' : 'h-40' }} bg-gradient-to-br from-primary-800 to-primary-900 flex items-center justify-center">
                        <span class="text-3xl font-black text-white/20">{{ mb_substr($product->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="absolute bottom-0 inset-x-0 p-2.5 bg-gradient-to-t from-black/70 to-transparent">
                        <p class="text-white text-xs font-medium truncate">{{ $product->name }}</p>
                        <p class="text-primary-300 text-xs font-bold">{{ format_price($product->sale_price) }}</p>
                    </div>
                </a>
                @empty
                <div class="col-span-2 h-[300px] rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-white/20 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="text-white/30 text-sm">Produits à venir</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BARRE DE CONFIANCE
═══════════════════════════════════════════════ --}}
<section class="bg-white border-b border-slate-100">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">
            <div class="flex items-center gap-3 py-4 px-4">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Livraison rapide</p>
                    <p class="text-[10px] text-slate-400 hidden sm:block">24–48h partout</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-4 px-4">
                <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Paiement sécurisé</p>
                    <p class="text-[10px] text-slate-400 hidden sm:block">Mobile Money & CB</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-4 px-4">
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Satisfait ou remboursé</p>
                    <p class="text-[10px] text-slate-400 hidden sm:block">30 jours</p>
                </div>
            </div>
            <div class="flex items-center gap-3 py-4 px-4">
                <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Support 7j/7</p>
                    <p class="text-[10px] text-slate-400 hidden sm:block">Réponse rapide</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CATÉGORIES — Design moderne circulaire
═══════════════════════════════════════════════ --}}
@if($featuredCategories->count() > 0)
<section class="py-10 bg-white">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">Nos catégories</h2>
            <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1">
                Tout voir <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="flex gap-6 overflow-x-auto pb-2 scrollbar-hide">
            @foreach($featuredCategories as $category)
            <a href="{{ route('shop.category', $category->slug) }}" class="group flex-shrink-0 text-center w-24">
                <div class="w-20 h-20 mx-auto rounded-full overflow-hidden border-2 border-slate-100 group-hover:border-primary-400 transition-all duration-300 group-hover:shadow-lg group-hover:shadow-primary-100/50 mb-2">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    @else
                        @php $catIdx = $loop->index % 6; @endphp
                        <div class="w-full h-full flex items-center justify-center
                            @if($catIdx === 0) bg-gradient-to-br from-indigo-400 to-indigo-600
                            @elseif($catIdx === 1) bg-gradient-to-br from-amber-400 to-amber-600
                            @elseif($catIdx === 2) bg-gradient-to-br from-emerald-400 to-emerald-600
                            @elseif($catIdx === 3) bg-gradient-to-br from-rose-400 to-rose-600
                            @elseif($catIdx === 4) bg-gradient-to-br from-blue-400 to-blue-600
                            @else bg-gradient-to-br from-cyan-400 to-cyan-600
                            @endif">
                            <span class="text-lg font-black text-white/80">{{ mb_substr($category->name, 0, 2) }}</span>
                        </div>
                    @endif
                </div>
                <h3 class="font-medium text-slate-700 group-hover:text-primary-600 transition-colors text-xs leading-tight line-clamp-2">{{ $category->name }}</h3>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PRODUITS VEDETTES
═══════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0)
<section class="py-10 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">Produits populaires</h2>
                <p class="text-slate-400 text-xs mt-0.5">Les plus demandés par nos clients</p>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1">
                Tout voir <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            @foreach($featuredProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANNIÈRE PROMO
═══════════════════════════════════════════════ --}}
@if($promoBanner)
<section class="py-4 bg-white">
    <div class="container mx-auto px-6">
        <a href="{{ $promoBanner->link ?? '#' }}" class="group relative flex rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all">
            @if($promoBanner->image)
                <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}" class="w-full h-40 md:h-52 object-cover group-hover:scale-[1.02] transition-transform duration-500" loading="lazy">
            @else
                <div class="w-full h-40 md:h-52 bg-gradient-to-r from-primary-700 to-violet-700"></div>
            @endif
            @if($promoBanner->title)
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/25 to-transparent flex items-center">
                <div class="p-5 md:p-8">
                    @if($promoBanner->subtitle)
                    <p class="text-primary-300 text-xs font-bold uppercase tracking-widest mb-1">{{ $promoBanner->subtitle }}</p>
                    @endif
                    <h3 class="text-lg md:text-2xl font-extrabold text-white mb-2">{{ $promoBanner->title }}</h3>
                    @if($promoBanner->button_text)
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-slate-900 font-bold rounded-lg text-xs group-hover:bg-primary-500 group-hover:text-white transition-all">
                        {{ $promoBanner->button_text }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
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
     VENTES FLASH — avec produits
═══════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="py-10 bg-white">
    <div class="container mx-auto px-6">
        {{-- Header avec countdown --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6"
             x-data="{
                h: 0, m: 0, s: 0,
                init() {
                    const tick = () => {
                        const now = new Date(), end = new Date(now);
                        end.setHours(23,59,59,999);
                        const d = end - now;
                        this.h = Math.floor(d/3600000);
                        this.m = Math.floor((d%3600000)/60000);
                        this.s = Math.floor((d%60000)/1000);
                    };
                    tick(); setInterval(tick, 1000);
                }
             }">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">Ventes Flash</h2>
                    <p class="text-slate-400 text-xs">Offres limitées du jour</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 font-medium">Se termine dans :</span>
                <div class="flex items-center gap-1">
                    <span class="bg-slate-900 text-white text-sm font-bold px-2.5 py-1 rounded-md min-w-[36px] text-center" x-text="String(h).padStart(2,'0')">00</span>
                    <span class="text-slate-400 font-bold">:</span>
                    <span class="bg-slate-900 text-white text-sm font-bold px-2.5 py-1 rounded-md min-w-[36px] text-center" x-text="String(m).padStart(2,'0')">00</span>
                    <span class="text-slate-400 font-bold">:</span>
                    <span class="bg-red-500 text-white text-sm font-bold px-2.5 py-1 rounded-md min-w-[36px] text-center" x-text="String(s).padStart(2,'0')">00</span>
                </div>
                <a href="{{ route('shop.index', ['sale' => 1]) }}" class="text-sm font-semibold text-red-600 hover:text-red-700 flex items-center gap-1 ml-2">
                    Tout voir <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Produits en promo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            @foreach($saleProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     NOUVEAUTÉS
═══════════════════════════════════════════════ --}}
@if($newProducts->count() > 0)
<section class="py-10 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">Nouveautés</h2>
                    <p class="text-slate-400 text-xs">Fraîchement ajoutés</p>
                </div>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1">
                Tout voir <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            @foreach($newProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     POURQUOI NOUS CHOISIR
═══════════════════════════════════════════════ --}}
<section class="py-10 bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 text-center mb-8">Pourquoi nous choisir ?</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="group text-center p-5 rounded-xl border border-slate-100 hover:border-primary-200 hover:shadow-md transition-all">
                <div class="w-12 h-12 mx-auto rounded-xl bg-blue-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Qualité garantie</h3>
                <p class="text-xs text-slate-500">Produits vérifiés et sélectionnés</p>
            </div>
            <div class="group text-center p-5 rounded-xl border border-slate-100 hover:border-emerald-200 hover:shadow-md transition-all">
                <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Meilleurs prix</h3>
                <p class="text-xs text-slate-500">Promotions chaque semaine</p>
            </div>
            <div class="group text-center p-5 rounded-xl border border-slate-100 hover:border-amber-200 hover:shadow-md transition-all">
                <div class="w-12 h-12 mx-auto rounded-xl bg-amber-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Livraison express</h3>
                <p class="text-xs text-slate-500">24-48h en Afrique de l'Ouest</p>
            </div>
            <div class="group text-center p-5 rounded-xl border border-slate-100 hover:border-violet-200 hover:shadow-md transition-all">
                <div class="w-12 h-12 mx-auto rounded-xl bg-violet-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">SAV réactif</h3>
                <p class="text-xs text-slate-500">WhatsApp & téléphone 7j/7</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     WHATSAPP CTA
═══════════════════════════════════════════════ --}}
@if($whatsapp)
<section class="py-8 bg-[#075e54]">
    <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-white">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <div>
                <h3 class="font-bold text-base">Besoin d'aide ? Écrivez-nous</h3>
                <p class="text-white/70 text-xs">Réponse rapide · Conseil personnalisé</p>
            </div>
        </div>
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Bonjour ! Je souhaite des informations sur vos produits.') }}" target="_blank"
           class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-[#075e54] font-bold rounded-lg hover:bg-green-50 transition-all text-sm">
            Écrire sur WhatsApp
        </a>
    </div>
</section>
@endif

{{-- FOMO Popup --}}
<div x-data="{
        show: false,
        names: ['Aminata', 'Moussa', 'Fatou', 'Ibrahim', 'Awa', 'Ousmane', 'Mariam', 'Boubacar'],
        cities: ['Abidjan', 'Dakar', 'Bamako', 'Ouagadougou', 'Lomé', 'Cotonou'],
        times: ['2 min', '5 min', '8 min', '12 min', '15 min'],
        name: '', city: '', time: '',
        init() {
            const trigger = () => {
                this.name = this.names[Math.floor(Math.random() * this.names.length)];
                this.city = this.cities[Math.floor(Math.random() * this.cities.length)];
                this.time = this.times[Math.floor(Math.random() * this.times.length)];
                this.show = true;
                setTimeout(() => this.show = false, 4000);
            };
            setTimeout(trigger, 10000);
            setInterval(trigger, 30000);
        }
     }"
     x-show="show" x-cloak
     x-transition:enter="transition ease-out duration-400"
     x-transition:enter-start="opacity-0 translate-y-3"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0 translate-y-3"
     class="fixed bottom-4 left-4 z-50 bg-white rounded-lg shadow-xl border border-slate-100 p-2.5 max-w-[260px] flex items-center gap-2.5">
    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    </div>
    <div class="min-w-0">
        <p class="text-[11px] text-slate-800 font-medium"><span x-text="name"></span> de <span x-text="city"></span></p>
        <p class="text-[10px] text-slate-400">a commandé il y a <span x-text="time" class="text-emerald-600 font-medium"></span></p>
    </div>
    <button @click="show = false" class="flex-shrink-0 text-slate-300 hover:text-slate-500">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- État vide --}}
@if($featuredProducts->count() === 0 && $newProducts->count() === 0)
<section class="py-16 bg-slate-50">
    <div class="container mx-auto px-6 text-center">
        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <h2 class="text-lg font-bold text-slate-900 mb-2">Boutique en cours de création</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto">Nos produits arrivent très bientôt !</p>
    </div>
</section>
@endif

@endsection
