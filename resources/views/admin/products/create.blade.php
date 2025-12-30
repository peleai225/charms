@extends('layouts.admin')

@section('title', 'Nouveau produit')
@section('page-title', 'Ajouter un produit')

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Informations générales</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nom du produit *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="short_description" class="block text-sm font-medium text-slate-700 mb-1">Description courte</label>
                        <textarea name="short_description" id="short_description" rows="2"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('short_description') }}</textarea>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description complète</label>
                        <textarea name="description" id="description" rows="6"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('description') }}</textarea>
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
                            <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', 0) }}" step="0.01" min="0" required
                                class="w-full pl-4 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">F CFA</span>
                        </div>
                    </div>

                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-slate-700 mb-1">Prix de vente TTC *</label>
                        <div class="relative">
                            <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price', 0) }}" step="0.01" min="0" required
                                class="w-full pl-4 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">F CFA</span>
                        </div>
                    </div>

                    <div>
                        <label for="compare_price" class="block text-sm font-medium text-slate-700 mb-1">Prix barré</label>
                        <div class="relative">
                            <input type="number" name="compare_price" id="compare_price" value="{{ old('compare_price') }}" step="0.01" min="0"
                                class="w-full pl-4 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">F CFA</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="tax_rate" class="block text-sm font-medium text-slate-700 mb-1">Taux de TVA *</label>
                    <select name="tax_rate" id="tax_rate" class="w-full md:w-auto px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="20" {{ old('tax_rate', 20) == 20 ? 'selected' : '' }}>20% (Standard)</option>
                        <option value="10" {{ old('tax_rate') == 10 ? 'selected' : '' }}>10% (Intermédiaire)</option>
                        <option value="5.5" {{ old('tax_rate') == 5.5 ? 'selected' : '' }}>5.5% (Réduit)</option>
                        <option value="2.1" {{ old('tax_rate') == 2.1 ? 'selected' : '' }}>2.1% (Super réduit)</option>
                        <option value="0" {{ old('tax_rate') == 0 ? 'selected' : '' }}>0% (Exonéré)</option>
                    </select>
                </div>
            </div>

            <!-- Stock & Identifiants -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Stock & Identifiants</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="sku" class="block text-sm font-medium text-slate-700 mb-1">SKU *</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                        @error('sku')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="barcode" class="block text-sm font-medium text-slate-700 mb-1">Code-barres</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                    </div>

                    <div>
                        <label for="weight" class="block text-sm font-medium text-slate-700 mb-1">Poids (kg)</label>
                        <input type="number" name="weight" id="weight" value="{{ old('weight') }}" step="0.001" min="0"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantité en stock *</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="stock_alert_threshold" class="block text-sm font-medium text-slate-700 mb-1">Seuil d'alerte *</label>
                        <input type="number" name="stock_alert_threshold" id="stock_alert_threshold" value="{{ old('stock_alert_threshold', 5) }}" min="0" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Images du produit</h2>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Ajouter des images</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <p class="mt-1 text-xs text-slate-500">Formats: JPEG, PNG, WEBP. Max 5MB par image. La première image sera l'image principale.</p>
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
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-700">Mis en avant</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_new" value="1" {{ old('is_new', true) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-700">Nouveauté</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="has_variants" value="1" {{ old('has_variants') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-700">Produit avec variantes (couleurs, tailles...)</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex gap-3">
                        <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                            Créer le produit
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
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->full_path }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Couleurs disponibles -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Couleurs disponibles</h2>
                
                <div class="flex flex-wrap gap-2">
                    @foreach($colors as $color)
                        <div class="flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full border border-slate-200" style="background-color: {{ $color->color_code }}"></span>
                            <span class="text-sm text-slate-600">{{ $color->value }}</span>
                        </div>
                    @endforeach
                </div>
                
                <p class="mt-3 text-xs text-slate-500">
                    Après la création du produit, vous pourrez ajouter des variantes avec ces couleurs.
                </p>
            </div>
        </div>
    </div>
</form>
@endsection
