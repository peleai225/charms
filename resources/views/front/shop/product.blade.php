@extends('layouts.front')

@section('title', $product->name . ' — ' . \App\Models\Setting::get('site_name', config('app.name')))
@section('meta_description', $product->short_description ?: Str::limit(strip_tags($product->description ?? ''), 160))
@section('og_type', 'product')
@section('og_title', $product->name)
@section('og_description', $product->short_description ?: Str::limit(strip_tags($product->description ?? ''), 160))
@if($product->primaryImage->first())
    @section('og_image', asset('storage/' . $product->primaryImage->first()->path))
@endif
@section('canonical', route('shop.product', $product->slug))

@section('content')
@include('front.partials.product-structured-data', ['product' => $product])
<div class="container mx-auto px-4 py-8 md:py-10">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-500 mb-8 flex items-center gap-2 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Accueil</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-primary-600 transition-colors">Boutique</a>
        @if($product->category)
            <span class="text-slate-300">/</span>
            <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-primary-600 transition-colors">{{ $product->category->name }}</a>
        @endif
        <span class="text-slate-300">/</span>
        <span class="text-slate-900 font-medium line-clamp-1">{{ $product->name }}</span>
    </nav>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12" x-data="productGallery()">

        {{-- ===== LIGHTBOX ===== --}}
        <div x-show="lightboxOpen"
             x-cloak
             @keydown.escape.window="closeLightbox()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center p-4"
             @click.self="closeLightbox()">
            <button @click="closeLightbox()"
                    class="absolute top-4 right-4 text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            @if($product->images->count() > 1)
            <button @click="prevLightbox()" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button @click="nextLightbox()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            @endif
            <img :src="lightboxImage" alt="{{ $product->name }}"
                 class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl select-none">
            @if($product->images->count() > 1)
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                @foreach($product->images as $i => $img)
                <button @click="setLightboxIndex({{ $i }})"
                        :class="lightboxIndex === {{ $i }} ? 'bg-white' : 'bg-white/40'"
                        class="w-2 h-2 rounded-full transition-colors"></button>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Galerie images -->
        <div class="space-y-4">
            <!-- Image principale (cliquable → lightbox) -->
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden cursor-zoom-in group relative"
                 @click="openLightbox()">
                <img :src="currentImage" alt="{{ $product->name }}"
                     class="w-full h-full object-cover transition-all duration-300" id="main-image">
                <!-- Indicateur zoom -->
                <div class="absolute bottom-3 right-3 bg-black/40 text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                    </svg>
                </div>
            </div>

            <!-- Thumbnails -->
            @if($product->images->count() > 1)
            <div class="flex gap-2 overflow-x-auto pb-2">
                @foreach($product->images as $index => $image)
                    <button @click="setImageAndIndex('{{ asset('storage/' . $image->path) }}', {{ $index }})"
                        :class="{ 'ring-2 ring-primary-500': currentImage === '{{ asset('storage/' . $image->path) }}' }"
                        class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-gray-300 transition-all">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Infos produit -->
        <div class="space-y-6">
            <!-- Badges -->
            <div class="flex gap-2">
                @if($product->is_new)
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">Nouveau</span>
                @endif
                @if($product->is_on_sale)
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full">-{{ $product->discount_percentage }}%</span>
                @endif
            </div>

            <!-- Catégorie -->
            @if($product->category)
                <a href="{{ route('shop.category', $product->category->slug) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    {{ $product->category->name }}
                </a>
            @endif

            <!-- Nom -->
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900">{{ $product->name }}</h1>

            @if($product->reviews_count > 0)
            <div class="flex items-center gap-2 text-sm">
                <div class="flex gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($product->average_rating) ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-gray-600">{{ number_format($product->average_rating, 1, ',', '') }} ({{ $product->reviews_count }} avis)</span>
            </div>
            @endif

            <!-- Prix -->
            <div class="flex items-baseline gap-3">
                <span class="text-3xl font-bold text-gray-900" id="variant-price">{{ format_price($product->sale_price) }}</span>
                @if($product->compare_price)
                    <span class="text-xl text-gray-500 line-through">{{ format_price($product->compare_price) }}</span>
                @endif
            </div>

            <!-- Description courte -->
            @if($product->short_description)
                <p class="text-gray-600">{{ $product->short_description }}</p>
            @endif

            <!-- Sélecteur de couleur -->
            @if($availableColors->count() > 0)
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-3">
                    Couleur : <span class="font-normal text-gray-600" x-text="selectedColorName">Choisir</span>
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach($availableColors as $color)
                        <button type="button"
                            @click="selectColor({{ $color->id }}, '{{ $color->value }}', '{{ $color->color_code }}', {{ json_encode($variantsByColor[$color->id] ?? []) }})"
                            :class="{ 'ring-2 ring-offset-2 ring-primary-500': selectedColorId === {{ $color->id }} }"
                            class="w-10 h-10 rounded-full border-2 border-gray-200 hover:border-gray-400 transition-all relative group"
                            style="background-color: {{ $color->color_code }}"
                            title="{{ $color->value }}">
                            <span class="sr-only">{{ $color->value }}</span>
                            <!-- Tooltip -->
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $color->value }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sélecteur de taille (si applicable) -->
            <div x-show="availableSizes.length > 0" x-cloak>
                <label class="block text-sm font-medium text-gray-900 mb-3">
                    Taille : <span class="font-normal text-gray-600" x-text="selectedSizeName || 'Choisir'"></span>
                </label>
                <div class="flex flex-wrap gap-2">
                    <template x-for="size in availableSizes" :key="size.id">
                        <button type="button"
                            @click="selectSize(size)"
                            :class="{ 
                                'border-primary-500 bg-primary-50 text-primary-700': selectedSizeId === size.id,
                                'border-gray-300 hover:border-gray-400': selectedSizeId !== size.id,
                                'opacity-50 cursor-not-allowed': size.stock <= 0
                            }"
                            :disabled="size.stock <= 0"
                            class="px-4 py-2 border-2 rounded-lg font-medium transition-all">
                            <span x-text="size.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Stock -->
            <div>
                <template x-if="variantStock !== null">
                    <p :class="variantStock > 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                        <span x-show="variantStock > 5">✓ En stock</span>
                        <span x-show="variantStock > 0 && variantStock <= 5">⚠ Plus que <span x-text="variantStock"></span> en stock</span>
                        <span x-show="variantStock <= 0">✗ Rupture de stock</span>
                    </p>
                </template>
                <template x-if="variantStock === null && !{{ $product->has_variants ? 'true' : 'false' }}">
                    @if($product->is_in_stock)
                        <p class="text-sm font-medium text-green-600">✓ En stock</p>
                    @else
                        <p class="text-sm font-medium text-red-600">✗ Rupture de stock</p>
                    @endif
                </template>
            </div>

            <!-- Quantité et Ajouter au panier -->
            <div class="space-y-4" id="buy-section">
                <div class="flex items-center gap-4">
                    <!-- Quantité -->
                    <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                        <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-3 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <input type="number" x-model="quantity" min="1" max="99" 
                            class="w-16 text-center border-0 focus:ring-0 font-medium">
                        <button type="button" @click="quantity = Math.min(99, quantity + 1)" class="px-4 py-3 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Bouton ajouter -->
                    <button type="button" 
                        @click="addToCart()"
                        :disabled="isAdding || ({{ $product->has_variants ? 'true' : 'false' }} && !selectedVariantId) || ({{ $product->has_variants ? 'true' : 'false' }} && variantStock !== null && variantStock <= 0) || (!{{ $product->has_variants ? 'true' : 'false' }} && {{ $product->stock_quantity <= 0 ? 'true' : 'false' }} && !{{ $product->allow_backorder ? 'true' : 'false' }})"
                        class="flex-1 py-3 px-6 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg x-show="!isAdding" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <svg x-show="isAdding" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isAdding ? 'Ajout...' : (showSuccess ? '✓ Ajouté !' : 'Ajouter au panier')"></span>
                    </button>
                </div>
                
                <!-- Message de succès -->
                <div x-show="showSuccess" x-transition class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    Produit ajouté au panier avec succès !
                </div>

                {{-- Points de fidélité à gagner --}}
                @if($pointsToEarn > 0)
                <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                    <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>Vous gagnerez <strong>{{ $pointsToEarn }} points de fidélité</strong> pour cet achat</span>
                </div>
                @endif

                {{-- Bouton WhatsApp Commander --}}
                @php
                    $waNumber = preg_replace('/\D/', '', \App\Models\Setting::get('social_whatsapp', ''));
                    $waMessage = urlencode("Bonjour, je souhaite commander : *{$product->name}* — " . number_format($product->sale_price, 0, ',', ' ') . " F CFA\nLien : " . route('shop.product', $product->slug));
                @endphp
                @if($waNumber)
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waMessage }}"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 w-full py-3 px-6 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Commander via WhatsApp
                </a>
                @endif
            </div>

            <!-- SKU -->
            <p class="text-sm text-gray-500">
                SKU : <span class="font-mono" x-text="selectedVariantSku || '{{ $product->sku }}'">{{ $product->sku }}</span>
            </p>

            <!-- Accordéon description -->
            <div class="border-t border-gray-200 pt-6 space-y-4">
                <details class="group" open>
                    <summary class="flex items-center justify-between cursor-pointer py-2">
                        <span class="font-semibold text-gray-900">Description</span>
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="py-4 text-gray-600 prose prose-sm max-w-none">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </details>

                <details class="group border-t border-gray-200">
                    <summary class="flex items-center justify-between cursor-pointer py-4">
                        <span class="font-semibold text-gray-900">Avis clients</span>
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="py-4">
                        @if($product->reviews->count() > 0)
                            <div class="space-y-4 mb-6">
                                @foreach($product->reviews as $review)
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex gap-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $review->author_name }}</span>
                                        @if($review->is_verified_purchase)
                                            <span class="text-xs text-green-600 font-medium">✓ Achat vérifié</span>
                                        @endif
                                        <span class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($review->title)
                                        <p class="font-medium text-gray-800 mb-1">{{ $review->title }}</p>
                                    @endif
                                    <p class="text-gray-600 text-sm">{{ $review->content }}</p>
                                    @if($review->admin_response)
                                        <div class="mt-3 pl-4 border-l-2 border-primary-200">
                                            <p class="text-sm font-medium text-gray-700">Réponse du vendeur</p>
                                            <p class="text-sm text-gray-600">{{ $review->admin_response }}</p>
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm mb-4">Aucun avis pour le moment. Soyez le premier à donner votre avis !</p>
                        @endif

                        <form action="{{ route('review.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div x-data="{ rating: 0 }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Votre note *</label>
                                <div class="flex gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" @click="rating = {{ $i }}" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-gray-300'" class="hover:text-amber-400 transition-colors">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" :value="rating" required>
                                @error('rating')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Votre nom *</label>
                                <input type="text" name="author_name" value="{{ old('author_name', auth()->user()?->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-xl" placeholder="Votre nom">
                                @error('author_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Votre email *</label>
                                <input type="email" name="author_email" value="{{ old('author_email', auth()->user()?->email ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-xl" placeholder="votre@email.com">
                                @error('author_email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Titre (optionnel)</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl" placeholder="Résumé de votre avis">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Votre avis *</label>
                                <textarea name="content" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-xl" placeholder="Partagez votre expérience...">{{ old('content') }}</textarea>
                                @error('content')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700">Publier mon avis</button>
                        </form>
                    </div>
                </details>

                <details class="group border-t border-gray-200">
                    <summary class="flex items-center justify-between cursor-pointer py-4">
                        <span class="font-semibold text-gray-900">Livraison</span>
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="py-4 text-gray-600 text-sm">
                        <ul class="space-y-2">
                            <li>🚚 Livraison gratuite dès 50 000 F CFA d'achat</li>
                            <li>📦 Expédition sous 24-48h</li>
                            <li>↩️ Retours gratuits sous 30 jours</li>
                        </ul>
                    </div>
                </details>
            </div>
        </div>
    </div>

    {{-- ===== STICKY MOBILE ADD-TO-CART BAR ===== --}}
    {{-- stickyVisible est géré dans productGallery() via IntersectionObserver --}}
    <div x-show="stickyVisible"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-2xl px-4 py-3 safe-area-pb">
        <div class="flex items-center gap-3 max-w-lg mx-auto">
            {{-- Image miniature --}}
            @php $thumb = $product->images->where('is_primary', true)->first() ?? $product->images->first(); @endphp
            @if($thumb)
            <img src="{{ asset('storage/' . $thumb->path) }}"
                 alt="{{ $product->name }}"
                 class="w-12 h-12 rounded-lg object-cover flex-shrink-0 border border-gray-100">
            @endif
            {{-- Nom + prix --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                <p class="text-sm font-bold text-primary-600">
                    {{ number_format($product->sale_price, 0, ',', ' ') }} F CFA
                </p>
            </div>
            {{-- Bouton --}}
            <button type="button"
                    @click="addToCart()"
                    :disabled="isAdding"
                    class="flex-shrink-0 py-2.5 px-5 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                <svg x-show="!isAdding" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <svg x-show="isAdding" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isAdding ? 'Ajout...' : (showSuccess ? '✓' : 'Ajouter')"></span>
            </button>
        </div>
    </div>

    {{-- Upsell : version premium --}}
    @if(isset($upsellProducts) && $upsellProducts->count() > 0)
    <div class="mt-14 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-6 border border-indigo-100">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Passez à la version premium</h2>
                <p class="text-sm text-slate-500">Des options supérieures pour aller encore plus loin</p>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($upsellProducts as $up)
            <a href="{{ route('shop.product', $up->slug) }}"
               class="flex items-center gap-4 bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow border border-white hover:border-indigo-200">
                @php $upImg = $up->images->where('is_primary', true)->first() ?? $up->images->first(); @endphp
                @if($upImg)
                    <img src="{{ asset('storage/' . $upImg->path) }}" alt="{{ $up->name }}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                @else
                    <div class="w-16 h-16 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 text-sm line-clamp-2">{{ $up->name }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="font-bold text-indigo-600">{{ number_format($up->sale_price, 0, ',', ' ') }} F</span>
                        @php $diff = round(($up->sale_price - $product->sale_price) / $product->sale_price * 100); @endphp
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">+{{ $diff }}%</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Cross-sell : Vous aimerez aussi -->
    @if($relatedProducts->count() > 0)
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Vous aimerez aussi</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            @foreach($relatedProducts as $related)
                @include('front.shop.partials.product-card', ['product' => $related])
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function productGallery() {
    return {
        // Images
        currentImage: '{{ $product->images->where("is_primary", true)->first() ? asset("storage/" . $product->images->where("is_primary", true)->first()->path) : "" }}',
        defaultImage: '{{ $product->images->where("is_primary", true)->first() ? asset("storage/" . $product->images->where("is_primary", true)->first()->path) : "" }}',
        
        // Sélection
        selectedColorId: null,
        selectedColorName: 'Choisir',
        selectedSizeId: null,
        selectedSizeName: null,
        selectedVariantId: null,
        selectedVariantSku: null,
        
        // Données
        availableSizes: [],
        variantStock: null,
        quantity: 1,
        isAdding: false,
        showSuccess: false,

        // Lightbox
        lightboxOpen: false,
        lightboxImage: '',
        lightboxIndex: 0,
        allImages: @php
            echo json_encode($product->images->map(fn($img) => asset('storage/' . $img->path))->values()->toArray());
        @endphp,

        // Sticky bar
        stickyVisible: false,
        
        // Variants data from PHP
        variantsByColor: @php
            $variantsData = [];
            foreach ($variantsByColor as $colorId => $variants) {
                $variantsData[$colorId] = [];
                foreach ($variants as $v) {
                    $sizeAttr = $v->attributeValues->first(function($av) {
                        return $av->attribute && $av->attribute->slug === 'taille';
                    });
                    $variantsData[$colorId][] = [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'stock' => $v->stock_quantity,
                        'price' => $v->sale_price ?? $product->sale_price,
                        'image' => $v->image ? asset('storage/' . $v->image) : null,
                        'size' => $sizeAttr ? ['id' => $sizeAttr->id, 'value' => $sizeAttr->value] : null,
                    ];
                }
            }
            echo json_encode($variantsData);
        @endphp,
        
        init() {
            // Sticky bar : visible quand le bouton d'achat sort du viewport
            const buySection = document.getElementById('buy-section');
            if (buySection && 'IntersectionObserver' in window) {
                const self = this;
                const observer = new IntersectionObserver(([entry]) => {
                    self.stickyVisible = !entry.isIntersecting;
                }, { threshold: 0, rootMargin: '0px 0px -20px 0px' });
                observer.observe(buySection);
            }
        },

        setImage(src) {
            this.currentImage = src;
        },

        setImageAndIndex(src, index) {
            this.currentImage = src;
            this.lightboxIndex = index;
            this.lightboxImage = src;
        },

        openLightbox() {
            this.lightboxImage = this.currentImage;
            const idx = this.allImages.indexOf(this.currentImage);
            this.lightboxIndex = idx >= 0 ? idx : 0;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },

        prevLightbox() {
            if (this.allImages.length === 0) return;
            this.lightboxIndex = (this.lightboxIndex - 1 + this.allImages.length) % this.allImages.length;
            this.lightboxImage = this.allImages[this.lightboxIndex];
            this.currentImage = this.lightboxImage;
        },

        nextLightbox() {
            if (this.allImages.length === 0) return;
            this.lightboxIndex = (this.lightboxIndex + 1) % this.allImages.length;
            this.lightboxImage = this.allImages[this.lightboxIndex];
            this.currentImage = this.lightboxImage;
        },

        setLightboxIndex(index) {
            this.lightboxIndex = index;
            this.lightboxImage = this.allImages[index] || '';
            this.currentImage = this.lightboxImage;
        },
        
        selectColor(colorId, colorName, colorCode, variants) {
            this.selectedColorId = colorId;
            this.selectedColorName = colorName;
            this.selectedSizeId = null;
            this.selectedSizeName = null;
            
            // Récupérer les variantes pour cette couleur
            const colorVariants = this.variantsByColor[colorId] || [];
            
            // Extraire les tailles disponibles
            this.availableSizes = colorVariants
                .filter(v => v.size)
                .map(v => ({
                    id: v.size.id,
                    name: v.size.value,
                    stock: v.stock,
                    variantId: v.id
                }));
            
            // Si pas de taille, sélectionner directement la variante
            if (this.availableSizes.length === 0 && colorVariants.length > 0) {
                const variant = colorVariants[0];
                this.selectedVariantId = variant.id;
                this.selectedVariantSku = variant.sku;
                this.variantStock = variant.stock;
                
                // Mettre à jour le prix
                document.getElementById('variant-price').textContent = 
                    new Intl.NumberFormat('fr-FR').format(variant.price) + ' F CFA';
                
                // Changer l'image si disponible
                if (variant.image) {
                    this.currentImage = variant.image;
                }
            } else {
                this.selectedVariantId = null;
                this.variantStock = null;
                
                // Changer l'image vers la première variante avec image de cette couleur
                const variantWithImage = colorVariants.find(v => v.image);
                if (variantWithImage) {
                    this.currentImage = variantWithImage.image;
                }
            }
        },
        
        selectSize(size) {
            this.selectedSizeId = size.id;
            this.selectedSizeName = size.name;
            this.selectedVariantId = size.variantId;
            this.variantStock = size.stock;
            
            // Trouver la variante complète
            const colorVariants = this.variantsByColor[this.selectedColorId] || [];
            const variant = colorVariants.find(v => v.id === size.variantId);
            
            if (variant) {
                this.selectedVariantSku = variant.sku;
                
                // Mettre à jour le prix
                document.getElementById('variant-price').textContent = 
                    new Intl.NumberFormat('fr-FR').format(variant.price) + ' F CFA';
                
                // Changer l'image si disponible
                if (variant.image) {
                    this.currentImage = variant.image;
                }
            }
        },
        
        async addToCart() {
            if (this.isAdding) return;
            
            // Vérifier que la variante est sélectionnée si nécessaire
            if ({{ $product->has_variants ? 'true' : 'false' }} && !this.selectedVariantId) {
                alert('Veuillez sélectionner une couleur et une taille');
                return;
            }
            
            this.isAdding = true;
            this.showSuccess = false;
            
            try {
                const response = await fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: {{ $product->id }},
                        variant_id: this.selectedVariantId || null,
                        quantity: this.quantity
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mettre à jour le store panier
                    if (Alpine.store('cart')) {
                        Alpine.store('cart').count = data.cart_count;
                    }

                    // Ouvrir le drawer panier
                    if (Alpine.store('cartDrawer')) {
                        Alpine.store('cartDrawer').open();
                    }

                    this.showSuccess = true;
                    setTimeout(() => {
                        this.showSuccess = false;
                    }, 3000);
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout au panier');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout au panier');
            } finally {
                this.isAdding = false;
            }
        }
    }
}
</script>
@endpush
@endsection

