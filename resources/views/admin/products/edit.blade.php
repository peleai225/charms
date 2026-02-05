@extends('layouts.admin')

@section('title', 'Modifier ' . $product->name)
@section('page-title', 'Modifier le produit')

@section('content')
@if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        <strong class="font-bold">Erreurs de validation :</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        {{ session('error') }}
    </div>
@endif

<div class="space-y-6">
    <!-- Navigation onglets -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <nav class="flex border-b border-slate-200" x-data="{ tab: 'general' }">
            <button @click="tab = 'general'" :class="{ 'border-blue-500 text-blue-600': tab === 'general' }" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:text-blue-600 transition-colors">
                Informations
            </button>
            <button @click="tab = 'images'" :class="{ 'border-blue-500 text-blue-600': tab === 'images' }" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:text-blue-600 transition-colors">
                Images ({{ $product->images->count() }})
            </button>
            <button @click="tab = 'variants'" :class="{ 'border-blue-500 text-blue-600': tab === 'variants' }" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:text-blue-600 transition-colors">
                Variantes / Couleurs ({{ $product->variants->count() }})
            </button>
        </nav>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations générales -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Informations générales</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nom du produit *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="short_description" class="block text-sm font-medium text-slate-700 mb-1">Description courte</label>
                            <textarea name="short_description" id="short_description" rows="2"
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description complète</label>
                            <textarea name="description" id="description" rows="6"
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Prix -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Prix</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="purchase_price" class="block text-sm font-medium text-slate-700 mb-1">Prix d'achat HT *</label>
                            <div class="relative">
                                <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" step="0.01" min="0" required
                                    class="w-full pl-4 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">F CFA</span>
                            </div>
                        </div>

                        <div>
                            <label for="sale_price" class="block text-sm font-medium text-slate-700 mb-1">Prix de vente TTC *</label>
                            <div class="relative">
                                <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0" required
                                    class="w-full pl-4 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">F CFA</span>
                            </div>
                        </div>

                        <div>
                            <label for="compare_price" class="block text-sm font-medium text-slate-700 mb-1">Prix barré</label>
                            <div class="relative">
                                <input type="number" name="compare_price" id="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0"
                                    class="w-full pl-4 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">F CFA</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="tax_rate" class="block text-sm font-medium text-slate-700 mb-1">Taux de TVA *</label>
                        <select name="tax_rate" id="tax_rate" class="w-full md:w-auto px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="20" {{ old('tax_rate', $product->tax_rate) == 20 ? 'selected' : '' }}>20% (Standard)</option>
                            <option value="10" {{ old('tax_rate', $product->tax_rate) == 10 ? 'selected' : '' }}>10% (Intermédiaire)</option>
                            <option value="5.5" {{ old('tax_rate', $product->tax_rate) == 5.5 ? 'selected' : '' }}>5.5% (Réduit)</option>
                            <option value="2.1" {{ old('tax_rate', $product->tax_rate) == 2.1 ? 'selected' : '' }}>2.1% (Super réduit)</option>
                            <option value="0" {{ old('tax_rate', $product->tax_rate) == 0 ? 'selected' : '' }}>0% (Exonéré)</option>
                        </select>
                    </div>
                </div>

                <!-- Stock de base -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Stock & Identifiants</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-slate-700 mb-1">SKU *</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                        </div>

                        <div>
                            <label for="barcode" class="block text-sm font-medium text-slate-700 mb-1">Code-barres</label>
                            <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-slate-700 mb-1">Poids (kg)</label>
                            <input type="number" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" step="0.001" min="0"
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>

                    @if(!$product->has_variants)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantité en stock *</label>
                            <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="stock_alert_threshold" class="block text-sm font-medium text-slate-700 mb-1">Seuil d'alerte *</label>
                            <input type="number" name="stock_alert_threshold" id="stock_alert_threshold" value="{{ old('stock_alert_threshold', $product->stock_alert_threshold) }}" min="0" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>
                    @else
                    <div class="mt-4 p-4 bg-blue-50 rounded-xl text-blue-700 text-sm">
                        <strong>Note :</strong> Le stock est géré par variante. Voir l'onglet "Variantes / Couleurs".
                    </div>
                    <input type="hidden" name="stock_quantity" value="{{ $product->stock_quantity }}">
                    <input type="hidden" name="stock_alert_threshold" value="{{ $product->stock_alert_threshold }}">
                    @endif
                </div>

                <!-- Images -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Images du produit</h2>
                    
                    <!-- Images existantes -->
                    @if($product->images->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        @foreach($product->images as $image)
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="" class="w-full h-32 object-cover rounded-lg {{ $image->is_primary ? 'ring-2 ring-blue-500' : '' }}">
                            @if($image->is_primary)
                                <span class="absolute top-2 left-2 px-2 py-0.5 bg-blue-500 text-white text-xs font-medium rounded">Principal</span>
                            @endif
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                                @if(!$image->is_primary)
                                <form method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}">
                                    @csrf
                                    <button type="submit" class="p-2 bg-white rounded-lg text-blue-600 hover:bg-blue-50" title="Définir comme principale">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('Supprimer cette image ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-white rounded-lg text-red-600 hover:bg-red-50" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Upload nouvelles images -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Ajouter des images</label>
                        <input type="file" name="images[]" multiple accept="image/*"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <p class="mt-1 text-xs text-slate-500">Formats: JPEG, PNG, WEBP. Max 5MB par image.</p>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Publication</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                            <select name="status" id="status" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}>Archivé</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-slate-700">Mis en avant</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-slate-700">Nouveauté</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_variants" value="1" {{ old('has_variants', $product->has_variants) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-slate-700">Produit avec variantes</span>
                            </label>
                        </div>

                        <div class="pt-4 border-t border-slate-200 flex gap-3">
                            <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                                Enregistrer
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Catégorie -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Catégorie</h2>
                    
                    <select name="category_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">Sans catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->full_path }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Statistiques -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Statistiques</h2>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Stock total</span>
                            <span class="font-medium">{{ $product->has_variants ? $product->variants->sum('stock_quantity') : $product->stock_quantity }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Variantes</span>
                            <span class="font-medium">{{ $product->variants->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Vues</span>
                            <span class="font-medium">{{ number_format($product->views_count) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Ventes</span>
                            <span class="font-medium">{{ number_format($product->sales_count) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Section Variantes (en dehors du formulaire principal) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-slate-900">Variantes par couleur</h2>
        </div>

        <!-- Formulaire ajout variante -->
        <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" enctype="multipart/form-data" class="p-4 bg-slate-50 rounded-xl mb-6">
            @csrf
            <h3 class="font-medium text-slate-900 mb-4">Ajouter une variante</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Couleur *</label>
                    <select name="color_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Choisir...</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}" data-color="{{ $color->color_code }}">
                                {{ $color->value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Taille (optionnel)</label>
                    <select name="size_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Aucune</option>
                        @foreach($attributes->where('slug', 'taille')->first()?->values ?? [] as $size)
                            <option value="{{ $size->id }}">{{ $size->value }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SKU variante *</label>
                    <input type="text" name="sku" required placeholder="SKU-ROUGE" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stock *</label>
                    <input type="number" name="stock_quantity" required min="0" value="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Prix (si différent)</label>
                    <input type="number" name="sale_price" step="0.01" min="0" placeholder="{{ $product->sale_price }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Image de la couleur</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <p class="text-xs text-slate-500 mt-1">Cette image apparaîtra quand le client sélectionne cette couleur</p>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        Ajouter la variante
                    </button>
                </div>
            </div>
        </form>

        <!-- Liste des variantes existantes -->
        @if($product->variants->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Variante</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Prix</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($product->variants as $variant)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            @if($variant->image)
                                <img src="{{ asset('storage/' . $variant->image) }}" alt="" class="w-12 h-12 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                                    @php
                                        $colorValue = $variant->attributeValues->firstWhere('attribute.slug', 'couleur');
                                    @endphp
                                    @if($colorValue && $colorValue->color_code)
                                        <div class="w-8 h-8 rounded-full border-2 border-white shadow" style="background-color: {{ $colorValue->color_code }}"></div>
                                    @else
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"></path>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @foreach($variant->attributeValues as $attrValue)
                                    @if($attrValue->color_code)
                                        <span class="w-5 h-5 rounded-full border border-slate-200" style="background-color: {{ $attrValue->color_code }}" title="{{ $attrValue->value }}"></span>
                                    @endif
                                    <span class="text-sm font-medium text-slate-900">{{ $attrValue->value }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-sm text-slate-600">{{ $variant->sku }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ format_price($variant->sale_price ?? $product->sale_price) }}
                        </td>
                        <td class="px-4 py-3">
                            @if($variant->stock_quantity <= 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Rupture</span>
                            @elseif($variant->stock_quantity <= 5)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">{{ $variant->stock_quantity }}</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">{{ $variant->stock_quantity }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" class="inline" onsubmit="return confirm('Supprimer cette variante ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-slate-500">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
            <p>Aucune variante pour le moment</p>
            <p class="text-sm">Ajoutez des variantes pour proposer différentes couleurs et tailles</p>
        </div>
        @endif
    </div>
</div>
@endsection
