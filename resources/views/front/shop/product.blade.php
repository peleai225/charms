@extends('layouts.front')

@section('title', $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-primary-600">Boutique</a>
        @if($product->category)
            <span class="mx-2">/</span>
            <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-primary-600">{{ $product->category->name }}</a>
        @endif
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $product->name }}</span>
    </nav>

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
            <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

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
            <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" x-model="selectedVariantId">

                <div class="flex items-center gap-4">
                    <!-- Quantité -->
                    <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                        <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-3 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <input type="number" name="quantity" x-model="quantity" min="1" max="99" 
                            class="w-16 text-center border-0 focus:ring-0 font-medium">
                        <button type="button" @click="quantity = Math.min(99, quantity + 1)" class="px-4 py-3 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Bouton ajouter -->
                    <button type="submit" 
                        :disabled="({{ $product->has_variants ? 'true' : 'false' }} && !selectedVariantId) || variantStock <= 0"
                        class="flex-1 py-3 px-6 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Ajouter au panier
                    </button>
                </div>
            </form>

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
        }
    }
}
</script>
@endpush
@endsection

