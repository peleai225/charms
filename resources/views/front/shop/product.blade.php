@extends('layouts.front')

@section('title', $product->name)

@section('content')
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
        <!-- Galerie images -->
        <div class="space-y-4">
            <!-- Image principale -->
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                <img :src="currentImage" alt="{{ $product->name }}" class="w-full h-full object-cover transition-all duration-300" id="main-image">
            </div>

            <!-- Thumbnails -->
            @if($product->images->count() > 1)
            <div class="flex gap-2 overflow-x-auto pb-2">
                @foreach($product->images as $image)
                    <button @click="setImage('{{ asset('storage/' . $image->path) }}')"
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
            <div class="space-y-4">
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

    <!-- Produits similaires -->
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
        
        setImage(src) {
            this.currentImage = src;
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
                        await Alpine.store('cart').sync();
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

