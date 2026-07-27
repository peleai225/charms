@extends('layouts.front')
@section('title', $product->name . ' — ' . \App\Models\Setting::get('site_name', config('app.name')))
@section('meta_description', $product->short_description ?: Str::limit(strip_tags($product->description ?? ''), 160))
@section('og_type', 'product')
@section('og_title', $product->name)
@section('og_description', $product->short_description ?: Str::limit(strip_tags($product->description ?? ''), 160))
@if($product->images->where('is_primary',true)->first())
    @section('og_image', asset('storage/' . $product->images->where('is_primary',true)->first()->path))
@endif
@section('canonical', route('shop.product', $product->slug))

@push('schema')
@php
    $productImg = $product->images->where('is_primary',true)->first() ?? $product->images->first();
    $schema = ['@context'=>'https://schema.org','@type'=>'Product','name'=>$product->name,'description'=>strip_tags($product->description??''),'sku'=>$product->sku,'url'=>route('shop.product',$product->slug),'image'=>$productImg?asset('storage/'.$productImg->path):null,'offers'=>['@type'=>'Offer','price'=>(string)$product->sale_price,'priceCurrency'=>'XOF','availability'=>$product->is_in_stock?'https://schema.org/InStock':'https://schema.org/OutOfStock']];
    if(($product->reviews_count??0)>0) $schema['aggregateRating']=['@type'=>'AggregateRating','ratingValue'=>number_format($product->average_rating,1),'reviewCount'=>$product->reviews_count];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
@php
    $starPath = 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z';
    $waNumber  = preg_replace('/\D/', '', \App\Models\Setting::get('social_whatsapp', ''));
    $waMessage = urlencode("Bonjour, je souhaite commander : *{$product->name}* — " . number_format($product->sale_price,0,',',' ') . " F CFA\nLien : " . route('shop.product', $product->slug));
@endphp
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10" x-data="productPage()">

    {{-- Lightbox --}}
    <div x-show="lightboxOpen" x-cloak @keydown.escape.window="closeLightbox()"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center p-4" @click.self="closeLightbox()">
        <button @click="closeLightbox()" class="absolute top-4 right-4 text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        @if($product->images->count() > 1)
        <button @click="prevLightbox()" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
        <button @click="nextLightbox()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">@foreach($product->images as $i=>$img)<button @click="setLightboxIndex({{ $i }})" :class="lightboxIndex==={{ $i }}?'bg-white':'bg-white/40'" class="w-2 h-2 rounded-full transition-colors"></button>@endforeach</div>
        @endif
        <img :src="lightboxImage" alt="{{ $product->name }}" class="max-w-full max-h-[90vh] object-contain rounded-xl select-none">
    </div>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-400 mb-7 flex-wrap" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
        <span class="text-slate-200">/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-slate-700 transition-colors">Boutique</a>
        @if($product->category)<span class="text-slate-200">/</span><a href="{{ route('shop.index',['category'=>$product->category->slug]) }}" class="hover:text-slate-700 transition-colors">{{ $product->category->name }}</a>@endif
        <span class="text-slate-200">/</span><span class="text-slate-700 font-medium truncate">{{ $product->name }}</span>
    </nav>

    @if(session('success'))<div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">{{ session('error') }}</div>@endif

    {{-- Grille principale 55/45 --}}
    <div class="grid grid-cols-1 lg:grid-cols-[55%_45%] gap-8 lg:gap-12">

        {{-- Galerie --}}
        <div class="space-y-3">
            <div class="relative aspect-square bg-slate-50 rounded-2xl overflow-hidden cursor-zoom-in border border-slate-100 group" @click="openLightbox()">
                <img :src="currentImage" alt="{{ $product->name }}" class="w-full h-full object-cover transition-opacity duration-200">
                <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                    @if($product->is_on_sale && $product->discount_percentage > 0)<span class="px-2.5 py-1 bg-red-500 text-white text-xs font-bold rounded-lg">-{{ $product->discount_percentage }}%</span>@endif
                    @if(!empty($product->is_new) && $product->is_new)<span class="px-2.5 py-1 bg-primary-600 text-white text-xs font-bold rounded-lg">Nouveau</span>@endif
                    @if(!$product->is_in_stock)<span class="px-2.5 py-1 bg-slate-500 text-white text-xs font-bold rounded-lg">Rupture</span>@endif
                </div>
                <div class="absolute bottom-3 right-3 bg-black/30 text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg></div>
            </div>
            @if($product->images->count() > 1)
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                @foreach($product->images->take(5) as $index => $image)
                <button @click="setImageAndIndex('{{ asset('storage/'.$image->path) }}', {{ $index }})"
                        :class="currentImage==='{{ asset('storage/'.$image->path) }}'?'border-slate-200':'border-slate-200 hover:border-slate-400'"
                        :style="currentImage==='{{ asset('storage/'.$image->path) }}'?'outline:2px solid var(--color-primary);border-color:var(--color-primary)':''"
                        class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden border-2 transition-all">
                    <img src="{{ asset('storage/'.$image->path) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Infos --}}
        <div class="space-y-5" id="buy-section">
            <div>
                @if($product->category)<a href="{{ route('shop.index',['category'=>$product->category->slug]) }}" class="text-xs font-semibold text-primary-600 uppercase tracking-widest hover:text-primary-700 transition-colors">{{ $product->category->name }}</a>@endif
                <h1 class="text-2xl font-bold text-slate-900 mt-1 leading-snug">{{ $product->name }}</h1>
            </div>

            @if(($product->reviews_count ?? 0) > 0)
            <div class="flex items-center gap-2">
                <div class="flex gap-0.5">@for($i=1;$i<=5;$i++)<svg class="w-4 h-4 {{ $i<=round($product->average_rating)?'text-amber-400':'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>@endfor</div>
                <a href="#reviews" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">{{ number_format($product->average_rating,1,',','') }} · {{ $product->reviews_count }} avis</a>
            </div>
            @endif

            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-bold text-slate-900" id="variant-price">{{ format_price($product->sale_price) }}</span>
                @if($product->compare_price)<span class="text-lg text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>@endif
                @if($product->is_on_sale && $product->discount_percentage > 0)<span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-md">-{{ $product->discount_percentage }}%</span>@endif
            </div>

            @if($product->short_description)<p class="text-slate-600 text-sm leading-relaxed">{{ $product->short_description }}</p>@endif

            {{-- Couleurs --}}
            @if($availableColors->count() > 0)
            <div>
                <p class="text-sm font-medium text-slate-900 mb-2.5">Couleur : <span class="font-normal text-slate-500" x-text="selectedColorName||'Choisir'"></span></p>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableColors as $color)
                    <button type="button"
                            @click="selectColor({{ $color->id }},'{{ $color->label??$color->value }}','{{ $color->value }}',{{ json_encode($variantsByColor[$color->id]??[]) }})"
                            :class="selectedColorId==={{ $color->id }}?'':'hover:scale-110'"
                            :style="selectedColorId==={{ $color->id }}?'box-shadow:0 0 0 2px #fff,0 0 0 4px var(--color-primary)':''"
                            class="w-9 h-9 rounded-full border-2 border-slate-200 transition-all"
                            style="background-color:{{ $color->value }}" title="{{ $color->label??$color->value }}">
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Taille / attribut secondaire --}}
            <div x-show="availableSizes.length > 0" x-cloak>
                <p class="text-sm font-medium text-slate-900 mb-2.5">{{ $secondaryAttributeName??'Taille' }} : <span class="font-normal text-slate-500" x-text="selectedSizeName||'Choisir'"></span></p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="size in availableSizes" :key="size.id">
                        <button type="button" @click="selectSize(size)" :disabled="size.stock<=0"
                                :class="{'bg-primary-600 border-primary-600 text-white':selectedSizeId===size.id,'border-slate-200 text-slate-700 hover:border-primary-600':selectedSizeId!==size.id&&size.stock>0,'border-slate-100 text-slate-300 cursor-not-allowed line-through':size.stock<=0}"
                                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"><span x-text="size.name"></span></button>
                    </template>
                </div>
            </div>

            {{-- Stock --}}
            <div class="text-sm">
                <template x-if="variantStock!==null">
                    <p :class="variantStock>0?'text-emerald-600':'text-red-500'" class="font-medium">
                        <span x-show="variantStock>5">En stock</span>
                        <span x-show="variantStock>0&&variantStock<=5">Plus que <span x-text="variantStock"></span> restant(s)</span>
                        <span x-show="variantStock<=0">Rupture de stock</span>
                    </p>
                </template>
                <template x-if="variantStock===null">
                    @if($product->is_in_stock)<p class="text-emerald-600 font-medium">En stock</p>@else<p class="text-red-500 font-medium">Rupture de stock</p>@endif
                </template>
            </div>

            {{-- Quantité + actions --}}
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden">
                        <button type="button" @click="quantity=Math.max(1,quantity-1)" class="px-4 py-3.5 hover:bg-slate-50 text-slate-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg></button>
                        <input type="number" x-model="quantity" min="1" max="99" class="w-14 text-center text-sm font-semibold border-0 focus:ring-0 py-3.5" aria-label="Quantité">
                        <button type="button" @click="quantity=Math.min(99,quantity+1)" class="px-4 py-3.5 hover:bg-slate-50 text-slate-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button>
                    </div>
                    <form method="POST" action="{{ route('wishlist.toggle', $product->id) }}" @submit.prevent>
                        @csrf
                        <button type="submit" class="w-12 h-12 rounded-xl border border-slate-200 hover:border-slate-300 flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors" aria-label="Favoris">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>
                    </form>
                </div>

                <button type="button" @click="addToCart()"
                        :disabled="isAdding||(!{{ $product->has_variants?'true':'false' }}&&!{{ $product->is_in_stock?'true':'false' }})||({{ $product->has_variants?'true':'false' }}&&variantStock!==null&&variantStock<=0)"
                        class="w-full py-4 bg-primary-600 hover:bg-primary-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg x-show="!isAdding" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <svg x-show="isAdding" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="isAdding?'Ajout en cours...':(showSuccess?'Ajouté !':'Ajouter au panier')"></span>
                </button>

                @if($waNumber)
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waMessage }}" target="_blank" rel="noopener"
                   class="w-full py-4 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Commander sur WhatsApp
                </a>
                @endif

                <div x-show="showSuccess" x-cloak x-transition class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm text-center">Produit ajouté au panier !</div>
            </div>

            @if($pointsToEarn > 0)
            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>
                Gagnez <strong class="mx-1">{{ $pointsToEarn }} points</strong> avec cet achat
            </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-slate-100 text-sm text-slate-500">
                <div class="flex items-center gap-2"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>Livraison à Abidjan</div>
                <div class="flex items-center gap-2"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Retours sous 7 jours</div>
            </div>
            <p class="text-xs text-slate-400">Réf. : <span class="font-mono" x-text="selectedVariantSku||'{{ $product->sku }}'">{{ $product->sku }}</span></p>
        </div>
    </div>

    {{-- Sticky bar mobile --}}
    <div x-show="stickyVisible" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0"
         class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-slate-200 px-4 py-3">
        <div class="flex items-center gap-3 max-w-lg mx-auto">
            @php $thumb=$product->images->where('is_primary',true)->first()??$product->images->first(); @endphp
            @if($thumb)<img src="{{ asset('storage/'.$thumb->path) }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-slate-100" loading="lazy">@endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900 truncate">{{ $product->name }}</p>
                <p class="text-sm font-bold text-primary-600">{{ format_price($product->sale_price) }}</p>
            </div>
            <button type="button" @click="addToCart()" :disabled="isAdding" class="flex-shrink-0 py-2.5 px-5 bg-primary-600 hover:bg-primary-700 disabled:bg-slate-200 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                <svg x-show="!isAdding" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <svg x-show="isAdding" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="isAdding?'...':'Ajouter'"></span>
            </button>
        </div>
    </div>

    {{-- Onglets --}}
    <div class="mt-12 border-t border-slate-100" x-data="{tab:'description'}">
        <div class="flex border-b border-slate-100 overflow-x-auto scrollbar-none">
            @foreach(['description'=>'Description','specs'=>'Caractéristiques','reviews'=>('Avis ('.(($product->reviews_count??0)).')') ] as $key=>$label)
            <button @click="tab='{{ $key }}'" id="{{ $key }}"
                    :class="tab==='{{ $key }}'?'border-primary-600 text-primary-600 font-semibold':'border-transparent text-slate-500 hover:text-slate-700'"
                    class="px-5 py-4 text-sm border-b-2 -mb-px transition-colors whitespace-nowrap">{{ $label }}</button>
            @endforeach
        </div>

        <div x-show="tab==='description'" class="py-8 prose prose-slate max-w-none text-sm">
            @if($product->description){!! $product->description !!}@else<p class="text-slate-400 italic">Aucune description disponible.</p>@endif
        </div>

        <div x-show="tab==='specs'" x-cloak class="py-8">
            <dl class="space-y-3">
                @foreach(['SKU'=>$product->sku,'Poids'=>($product->weight?$product->weight.' kg':null),'Catégorie'=>($product->category?$product->category->name:null)] as $label=>$val)
                @if($val)<div class="flex items-center gap-3"><dt class="w-32 text-xs font-medium text-slate-400 uppercase tracking-wider flex-shrink-0">{{ $label }}</dt><dd class="text-sm text-slate-700 {{ $label==='SKU'?'font-mono':'' }}">{{ $val }}</dd></div>@endif
                @endforeach
            </dl>
        </div>

        <div x-show="tab==='reviews'" x-cloak class="py-8 space-y-5">
            @if($product->reviews->count() > 0)
            <div class="flex items-center gap-4 pb-5 border-b border-slate-100">
                <p class="text-5xl font-bold text-slate-900">{{ number_format($product->average_rating,1,',','') }}</p>
                <div><div class="flex gap-0.5">@for($i=1;$i<=5;$i++)<svg class="w-4 h-4 {{ $i<=round($product->average_rating)?'text-amber-400':'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>@endfor</div><p class="text-xs text-slate-400 mt-1">{{ $product->reviews_count }} avis</p></div>
            </div>
            <div class="space-y-4">
                @foreach($product->reviews as $review)
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <div class="flex gap-0.5">@for($i=1;$i<=5;$i++)<svg class="w-3.5 h-3.5 {{ $i<=$review->rating?'text-amber-400':'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>@endfor</div>
                            <span class="text-sm font-semibold text-slate-900">{{ $review->customer->first_name??($review->author_name??'Client') }}</span>
                        </div>
                        <span class="text-xs text-slate-400">{{ $review->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if(!empty($review->body))<p class="text-sm text-slate-600 leading-relaxed">{{ $review->body }}</p>@endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10">
                <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <p class="text-slate-500 text-sm">Aucun avis pour ce produit.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Produits similaires --}}
    @if($relatedProducts->count() > 0)
    <div class="mt-16">
        <h2 class="text-xl font-bold text-slate-900 mb-6">Vous aimerez aussi</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($relatedProducts as $related)
            @include('front.shop.partials.product-card', ['product'=>$related])
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function productPage() {
    return {
        currentImage: '{{ ($pi=$product->images->where("is_primary",true)->first()??$product->images->first())?asset("storage/".$pi->path):"" }}',
        allImages: @php echo json_encode($product->images->map(fn($i)=>asset('storage/'.$i->path))->values()->toArray()); @endphp,
        selectedColorId: null, selectedColorName: null, selectedSizeId: null, selectedSizeName: null,
        selectedVariantId: null, selectedVariantSku: null, availableSizes: [], variantStock: null,
        quantity: 1, isAdding: false, showSuccess: false, stickyVisible: false,
        lightboxOpen: false, lightboxImage: '', lightboxIndex: 0,
        variantsByColor: @php
            $vd=[]; $ss=$secondaryAttributeSlug??null;
            foreach($variantsByColor as $cid=>$vs){
                $vd[$cid]=[];
                foreach($vs as $v){
                    $sa=$v->attributeValues->first(fn($av)=>$av->attribute&&($ss?$av->attribute->slug===$ss:$av->attribute->slug!=='couleur'));
                    $vd[$cid][]=['id'=>$v->id,'sku'=>$v->sku,'stock'=>$v->stock_quantity,'price'=>$v->sale_price??$product->sale_price,'image'=>$v->image?asset('storage/'.$v->image):null,'size'=>$sa?['id'=>$sa->id,'value'=>$sa->value]:null];
                }
            }
            echo json_encode($vd);
        @endphp,
        init() {
            const dv=this.variantsByColor['default'];
            if(dv?.length>0){
                this.availableSizes=dv.filter(v=>v.size).map(v=>({id:v.size.id,name:v.size.value,stock:v.stock,variantId:v.id}));
                if(!this.availableSizes.length&&dv.length===1){this.selectedVariantId=dv[0].id;this.selectedVariantSku=dv[0].sku;this.variantStock=dv[0].stock;}
            }
            const bs=document.getElementById('buy-section');
            if(bs&&'IntersectionObserver'in window){const s=this;new IntersectionObserver(([e])=>{s.stickyVisible=!e.isIntersecting;},{threshold:0,rootMargin:'0px 0px -20px 0px'}).observe(bs);}
        },
        setImageAndIndex(src,i){this.currentImage=src;this.lightboxIndex=i;},
        openLightbox(){const i=this.allImages.indexOf(this.currentImage);this.lightboxIndex=i>=0?i:0;this.lightboxImage=this.currentImage;this.lightboxOpen=true;document.body.style.overflow='hidden';},
        closeLightbox(){this.lightboxOpen=false;document.body.style.overflow='';},
        prevLightbox(){this.lightboxIndex=(this.lightboxIndex-1+this.allImages.length)%this.allImages.length;this.lightboxImage=this.allImages[this.lightboxIndex];this.currentImage=this.lightboxImage;},
        nextLightbox(){this.lightboxIndex=(this.lightboxIndex+1)%this.allImages.length;this.lightboxImage=this.allImages[this.lightboxIndex];this.currentImage=this.lightboxImage;},
        setLightboxIndex(i){this.lightboxIndex=i;this.lightboxImage=this.allImages[i]||'';this.currentImage=this.lightboxImage;},
        selectColor(id,name,code,variants){
            this.selectedColorId=id;this.selectedColorName=name;this.selectedSizeId=null;this.selectedSizeName=null;
            const cv=this.variantsByColor[id]||[];
            this.availableSizes=cv.filter(v=>v.size).map(v=>({id:v.size.id,name:v.size.value,stock:v.stock,variantId:v.id}));
            if(!this.availableSizes.length&&cv.length>0){const v=cv[0];this.selectedVariantId=v.id;this.selectedVariantSku=v.sku;this.variantStock=v.stock;document.getElementById('variant-price').textContent=new Intl.NumberFormat('fr-FR').format(v.price)+' F CFA';if(v.image)this.currentImage=v.image;}
            else{this.selectedVariantId=null;this.variantStock=null;const wi=cv.find(v=>v.image);if(wi)this.currentImage=wi.image;}
        },
        selectSize(size){
            this.selectedSizeId=size.id;this.selectedSizeName=size.name;this.selectedVariantId=size.variantId;this.variantStock=size.stock;
            const v=(this.variantsByColor[this.selectedColorId||'default']||[]).find(v=>v.id===size.variantId);
            if(v){this.selectedVariantSku=v.sku;document.getElementById('variant-price').textContent=new Intl.NumberFormat('fr-FR').format(v.price)+' F CFA';if(v.image)this.currentImage=v.image;}
        },
        async addToCart(){
            if(this.isAdding)return;
            if({{ $product->has_variants?'true':'false' }}&&!this.selectedVariantId){alert('Veuillez sélectionner une variante');return;}
            this.isAdding=true;this.showSuccess=false;
            try{
                const r=await fetch('{{ route("cart.add") }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({product_id:{{ $product->id }},variant_id:this.selectedVariantId||null,quantity:this.quantity})});
                const d=await r.json();
                if(d.success){if(Alpine.store('cart'))Alpine.store('cart').count=d.cart_count;window.dispatchEvent(new CustomEvent('open-cart-drawer'));this.showSuccess=true;setTimeout(()=>{this.showSuccess=false;},3000);window.dispatchEvent(new CustomEvent('cart-item-added'));}
                else alert(d.message||"Erreur lors de l'ajout au panier");
            }catch(e){alert("Erreur lors de l'ajout au panier");}
            finally{this.isAdding=false;}
        }
    };
}
document.addEventListener('DOMContentLoaded',function(){
    var _p={id:{{ $product->id }},name:{{ Js::from($product->name) }},price:{{ $product->sale_price??0 }}};
    if(window.trackPixel)window.trackPixel.viewContent(_p);
    if(window.trackGA4)window.trackGA4.viewItem(_p);
});
</script>
<style>.scrollbar-none::-webkit-scrollbar{display:none}.scrollbar-none{-ms-overflow-style:none;scrollbar-width:none}</style>
@endpush
@endsection
