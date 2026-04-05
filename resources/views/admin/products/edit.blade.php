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

<div class="space-y-6" x-data="{ tab: 'general' }">
    <!-- Navigation onglets -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <nav class="flex border-b border-slate-200">
            <button type="button" @click="tab = 'general'" :class="{ 'border-blue-500 text-blue-600': tab === 'general' }" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:text-blue-600 transition-colors">
                Informations
            </button>
            <button type="button" @click="tab = 'images'" :class="{ 'border-blue-500 text-blue-600': tab === 'images' }" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:text-blue-600 transition-colors">
                Images ({{ $product->images->count() }})
            </button>
            <button type="button" @click="tab = 'variants'" :class="{ 'border-blue-500 text-blue-600': tab === 'variants' }" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:text-blue-600 transition-colors">
                Variantes ({{ $product->variants->count() }})
            </button>
        </nav>
    </div>

    {{-- no-ajax : formulaire complexe avec champs file, éviter les soumissions AJAX qui peuvent mal gérer _method --}}
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="no-ajax" id="product-edit-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations générales -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6" x-show="tab==='general'">
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6" x-show="tab==='general'">
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6" x-show="tab==='general'">
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6" x-show="tab==='images'">
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
                                <button type="submit" form="form-primary-{{ $image->id }}" class="p-2 bg-white rounded-lg text-blue-600 hover:bg-blue-50" title="Définir comme principale">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                @endif
                                <button type="submit" form="form-delete-{{ $image->id }}" class="p-2 bg-white rounded-lg text-red-600 hover:bg-red-50" title="Supprimer" onclick="return confirm('Supprimer cette image ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
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
            <div class="space-y-6" x-show="tab==='general' || tab==='images'">
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

    {{-- ===== SECTION VARIANTES ===== --}}
    @php
        $allSizes  = $attributes->where('slug', 'taille')->first()?->values ?? collect();
        $allColors = $attributes->where('slug', 'couleur')->first()?->values ?? collect();
        $productSlug = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $product->name));
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
         x-show="tab==='variants'"
         x-data="{
            panel: 'bulk',
            mode: 'sizes',
            selectedSizes: [],
            selectedColors: [],
            productSlug: '{{ $productSlug }}',

            toggleSize(id) {
                const idx = this.selectedSizes.indexOf(id);
                if (idx >= 0) this.selectedSizes.splice(idx, 1);
                else this.selectedSizes.push(id);
            },
            toggleColor(id) {
                const idx = this.selectedColors.indexOf(id);
                if (idx >= 0) this.selectedColors.splice(idx, 1);
                else this.selectedColors.push(id);
            },
            autoSku(val, extra) {
                const base = this.productSlug.substring(0, 12);
                const v = val.replace(/\s+/g, '').toUpperCase().substring(0, 6);
                const e = extra ? '-' + extra.replace(/\s+/g, '').toUpperCase().substring(0, 5) : '';
                return base + '-' + v + e;
            }
         }">

        <!-- En-tête avec onglets internes -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h2 class="text-base font-semibold text-slate-900">
                Variantes
                <span class="ml-2 text-xs font-normal text-slate-500 bg-slate-200 rounded-full px-2 py-0.5">{{ $product->variants->count() }} existante(s)</span>
            </h2>
            <div class="flex gap-1 bg-white rounded-lg border border-slate-200 p-1">
                <button type="button" @click="panel='bulk'"
                    :class="panel==='bulk' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-all">
                    + Ajout en masse
                </button>
                <button type="button" @click="panel='single'"
                    :class="panel==='single' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-all">
                    + Variante unique
                </button>
                <button type="button" @click="panel='list'"
                    :class="panel==='list' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-all">
                    Gérer ({{ $product->variants->count() }})
                </button>
            </div>
        </div>

        {{-- ===== PANEL : AJOUT EN MASSE ===== --}}
        <div x-show="panel==='bulk'" class="p-6 space-y-5">

            {{-- Sélection du mode --}}
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="mode" value="sizes" class="text-blue-600">
                    <span class="text-sm font-medium text-slate-700">Tailles seulement</span>
                    <span class="text-xs text-slate-400">(vêtements enfant, âge...)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="mode" value="colors" class="text-blue-600">
                    <span class="text-sm font-medium text-slate-700">Couleurs seulement</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="mode" value="matrix" class="text-blue-600">
                    <span class="text-sm font-medium text-slate-700">Tailles × Couleurs</span>
                    <span class="text-xs text-slate-400">(grille complète)</span>
                </label>
            </div>

            {{-- Sélection des tailles (modes sizes + matrix) --}}
            <div x-show="mode==='sizes' || mode==='matrix'">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sélectionner les tailles</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($allSizes as $size)
                    <button type="button"
                        @click="toggleSize({{ $size->id }})"
                        :class="selectedSizes.includes({{ $size->id }}) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:border-blue-400'"
                        class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all">
                        {{ $size->value }}
                    </button>
                    @endforeach
                    @if($allSizes->isEmpty())
                        <p class="text-sm text-slate-400 italic">Aucune taille définie en base.</p>
                    @endif
                </div>
            </div>

            {{-- Sélection des couleurs (modes colors + matrix) --}}
            <div x-show="mode==='colors' || mode==='matrix'">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sélectionner les couleurs</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($allColors as $color)
                    <button type="button"
                        @click="toggleColor({{ $color->id }})"
                        :class="selectedColors.includes({{ $color->id }}) ? 'ring-2 ring-offset-1 ring-blue-600 opacity-100' : 'opacity-60 hover:opacity-90'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-sm font-medium transition-all">
                        @if($color->color_code)
                            <span class="w-4 h-4 rounded-full border border-slate-200 inline-block" style="background:{{ $color->color_code }}"></span>
                        @endif
                        {{ $color->value }}
                    </button>
                    @endforeach
                    @if($allColors->isEmpty())
                        <p class="text-sm text-slate-400 italic">Aucune couleur définie.</p>
                    @endif
                </div>
            </div>

            {{-- ===== GRILLE : MODE TAILLES ===== --}}
            <form method="POST" action="{{ route('admin.products.variants.bulk', $product) }}" class="no-ajax" x-show="mode==='sizes'">
                @csrf
                <template x-if="selectedSizes.length > 0">
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase w-28">Taille</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase w-28">Stock *</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase w-36">Prix spécial (FCFA)</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">SKU (modifiable)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allSizes as $i => $size)
                                <tr x-show="selectedSizes.includes({{ $size->id }})" class="hover:bg-blue-50/30">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="rows[{{ $i }}][size_id]" value="{{ $size->id }}">
                                        <span class="font-semibold text-slate-800">{{ $size->value }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="rows[{{ $i }}][stock_quantity]" min="0" value="0"
                                               class="w-24 px-2 py-1.5 border border-slate-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="rows[{{ $i }}][sale_price]" min="0" step="1"
                                               placeholder="{{ intval($product->sale_price) }}"
                                               class="w-32 px-2 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="rows[{{ $i }}][sku]"
                                               :value="autoSku('{{ addslashes($size->value) }}')"
                                               class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="selectedSizes.length === 0">
                    <p class="text-sm text-slate-400 italic py-2">Sélectionnez au moins une taille ci-dessus.</p>
                </template>
                <div class="flex justify-end mt-4">
                    <button type="submit"
                        x-bind:disabled="selectedSizes.length === 0"
                        :class="selectedSizes.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl transition-colors shadow-sm shadow-blue-600/20 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Créer <span x-text="selectedSizes.length"></span> variante(s)
                    </button>
                </div>
            </form>

            {{-- ===== GRILLE : MODE COULEURS ===== --}}
            <form method="POST" action="{{ route('admin.products.variants.bulk', $product) }}" class="no-ajax" x-show="mode==='colors'">
                @csrf
                <template x-if="selectedColors.length > 0">
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase w-36">Couleur</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase w-28">Stock *</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase w-36">Prix spécial (FCFA)</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allColors as $i => $color)
                                <tr x-show="selectedColors.includes({{ $color->id }})" class="hover:bg-blue-50/30">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="rows[{{ $i }}][color_id]" value="{{ $color->id }}">
                                        <div class="flex items-center gap-2">
                                            @if($color->color_code)
                                                <span class="w-5 h-5 rounded-full border border-slate-200 flex-shrink-0" style="background:{{ $color->color_code }}"></span>
                                            @endif
                                            <span class="font-semibold text-slate-800">{{ $color->value }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="rows[{{ $i }}][stock_quantity]" min="0" value="0"
                                               class="w-24 px-2 py-1.5 border border-slate-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="rows[{{ $i }}][sale_price]" min="0" step="1"
                                               placeholder="{{ intval($product->sale_price) }}"
                                               class="w-32 px-2 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="rows[{{ $i }}][sku]"
                                               :value="autoSku('{{ addslashes($color->value) }}')"
                                               class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </template>
                <template x-if="selectedColors.length === 0">
                    <p class="text-sm text-slate-400 italic py-2">Sélectionnez au moins une couleur ci-dessus.</p>
                </template>
                <div class="flex justify-end mt-4">
                    <button type="submit"
                        x-bind:disabled="selectedColors.length === 0"
                        :class="selectedColors.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl transition-colors shadow-sm shadow-blue-600/20 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Créer <span x-text="selectedColors.length"></span> variante(s)
                    </button>
                </div>
            </form>

            {{-- ===== GRILLE : MODE MATRICE Taille × Couleur ===== --}}
            <div x-show="mode==='matrix'">
                @php $matrixSizes = $allSizes; $matrixColors = $allColors; @endphp
                <template x-if="selectedSizes.length > 0 && selectedColors.length > 0">
                    <form method="POST" action="{{ route('admin.products.variants.bulk', $product) }}" class="no-ajax" x-data="{ rows: [] }">
                        @csrf
                        @php $rowIdx = 0; @endphp
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Taille</th>
                                        @foreach($matrixColors as $color)
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase" x-show="selectedColors.includes({{ $color->id }})">
                                            <div class="flex items-center justify-center gap-1">
                                                @if($color->color_code)<span class="w-3 h-3 rounded-full" style="background:{{ $color->color_code }}"></span>@endif
                                                {{ $color->value }}
                                            </div>
                                        </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($matrixSizes as $size)
                                    <tr x-show="selectedSizes.includes({{ $size->id }})" class="hover:bg-blue-50/30">
                                        <td class="px-4 py-3 font-semibold text-slate-800 w-28">{{ $size->value }}</td>
                                        @foreach($matrixColors as $color)
                                        <td class="px-3 py-3 text-center" x-show="selectedColors.includes({{ $color->id }})">
                                            <input type="hidden" name="rows[{{ $rowIdx }}][size_id]" value="{{ $size->id }}">
                                            <input type="hidden" name="rows[{{ $rowIdx }}][color_id]" value="{{ $color->id }}">
                                            <input type="hidden" name="rows[{{ $rowIdx }}][sku]"
                                                   :value="autoSku('{{ addslashes($size->value) }}', '{{ addslashes($color->value) }}')">
                                            <input type="hidden" name="rows[{{ $rowIdx }}][sale_price]" value="">
                                            <input type="number" name="rows[{{ $rowIdx }}][stock_quantity]" min="0" value="0"
                                                   placeholder="qté"
                                                   class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </td>
                                        @php $rowIdx++; @endphp
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Saisir 0 pour créer la variante sans stock. Les SKUs sont générés automatiquement.</p>
                        <div class="flex justify-end mt-3">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-sm shadow-blue-600/20 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Créer la matrice
                            </button>
                        </div>
                    </form>
                </template>
                <template x-if="selectedSizes.length === 0 || selectedColors.length === 0">
                    <p class="text-sm text-slate-400 italic py-2">Sélectionnez au moins une taille et une couleur.</p>
                </template>
            </div>
        </div>

        {{-- ===== PANEL : VARIANTE UNIQUE ===== --}}
        <div x-show="panel==='single'" class="p-6">
            <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" enctype="multipart/form-data" class="no-ajax space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Couleur</label>
                        <select name="color_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <option value="">Choisir...</option>
                            @foreach($colors as $color)
                                <option value="{{ $color->id }}" data-color="{{ $color->color_code }}">{{ $color->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Taille</label>
                        <select name="size_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <option value="">Aucune</option>
                            @foreach($allSizes as $size)
                                <option value="{{ $size->id }}">{{ $size->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">SKU *</label>
                        <input type="text" name="sku" required placeholder="EX: CARGO-ROUGE-4ANS" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Stock *</label>
                        <input type="number" name="stock_quantity" required min="0" value="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Prix spécial (FCFA)</label>
                        <input type="number" name="sale_price" step="1" min="0" placeholder="{{ intval($product->sale_price) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Image couleur</label>
                        <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors text-sm">
                        Ajouter la variante
                    </button>
                </div>
            </form>
        </div>

        {{-- ===== PANEL : LISTE / GESTION ===== --}}
        <div x-show="panel==='list'" class="p-6">
            @if($product->variants->count() > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Variante</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Prix</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Stock</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($product->variants as $variant)
                        @php
                            $vColor = $variant->attributeValues->firstWhere(fn($v) => $v->attribute->slug === 'couleur');
                            $vSize  = $variant->attributeValues->firstWhere(fn($v) => $v->attribute->slug === 'taille');
                        @endphp
                        <tr class="hover:bg-slate-50 group" x-data="{ editing: false, stock: {{ $variant->stock_quantity }}, saving: false }">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($variant->image)
                                        <img src="{{ asset('storage/' . $variant->image) }}" class="w-9 h-9 rounded-lg object-cover">
                                    @elseif($vColor && $vColor->color_code)
                                        <span class="w-9 h-9 rounded-lg border border-slate-200 flex-shrink-0 inline-block" style="background:{{ $vColor->color_code }}"></span>
                                    @else
                                        <span class="w-9 h-9 rounded-lg bg-slate-100 flex-shrink-0 inline-block"></span>
                                    @endif
                                    <div>
                                        @if($vColor)<span class="font-medium text-slate-900">{{ $vColor->value }}</span>@endif
                                        @if($vSize)<span class="ml-1 text-xs text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">{{ $vSize->value }}</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $variant->sku }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ format_price($variant->sale_price ?? $product->sale_price) }}
                            </td>
                            {{-- Stock : édition inline --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <template x-if="!editing">
                                        <span @click="editing=true"
                                              :class="stock <= 0 ? 'bg-red-100 text-red-700' : (stock <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700')"
                                              class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80 transition-opacity"
                                              title="Cliquer pour modifier">
                                            <span x-text="stock <= 0 ? 'Rupture' : stock + ' pcs'"></span>
                                            <svg class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="editing">
                                        <form @submit.prevent="
                                            saving=true;
                                            fetch('{{ route('admin.products.variants.update', [$product, $variant]) }}', {
                                                method: 'PATCH',
                                                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},
                                                body: JSON.stringify({stock_quantity: parseInt(stock)})
                                            }).then(r=>r.json()).then(d=>{ if(d.success){ editing=false; } }).finally(()=>{ saving=false; });
                                        " class="flex items-center gap-1">
                                            <input type="number" x-model="stock" min="0"
                                                   class="w-20 px-2 py-1 border-2 border-blue-400 rounded-lg text-sm text-center font-medium focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   @keydown.escape="editing=false" @click.stop>
                                            <button type="submit" :disabled="saving"
                                                    class="p-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                            <button type="button" @click="editing=false"
                                                    class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" class="inline no-ajax" onsubmit="return confirm('Supprimer cette variante ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-xs text-slate-500">{{ $product->variants->count() }} variante(s)</td>
                            <td class="px-4 py-2 text-xs font-semibold text-slate-700">
                                Total : {{ $product->variants->sum('stock_quantity') }} pcs
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                <p class="text-sm">Aucune variante pour le moment.</p>
                <button type="button" @click="panel='bulk'" class="mt-2 text-sm text-blue-600 hover:underline">Utilisez l'ajout en masse →</button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Formulaires pour actions images (hors du formulaire principal pour éviter HTML invalide) --}}
@foreach($product->images as $image)
@if(!$image->is_primary)
<form id="form-primary-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}" class="hidden no-ajax">
    @csrf
</form>
@endif
<form id="form-delete-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" class="hidden no-ajax">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection
