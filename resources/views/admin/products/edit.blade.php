@extends('layouts.admin')

@section('title', 'Modifier — ' . $product->name)
@section('page-title', 'Modifier le produit')

@section('content')

@if ($errors->any())
<div class="mb-5 flex gap-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
    <div>
        <p class="text-[13px] font-semibold text-orange-800">Erreurs de validation</p>
        <ul class="mt-1 space-y-0.5">
            @foreach ($errors->all() as $error)
            <li class="text-[12px] text-orange-700">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@if (session('success'))
<div class="mb-5 flex gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-[13px] text-green-800">{{ session('success') }}</p>
</div>
@endif

@if (session('error'))
<div class="mb-5 flex gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    <p class="text-[13px] text-red-800">{{ session('error') }}</p>
</div>
@endif

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="no-ajax" id="product-edit-form">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations générales</h2>
                <div class="space-y-3">
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">Nom du produit <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('name') border-red-300 @enderror">
                        @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="short_description" class="block text-xs font-medium text-gray-700 mb-1">Description courte</label>
                        <textarea name="short_description" id="short_description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none">{{ old('short_description', $product->short_description) }}</textarea>
                    </div>
                    <div>
                        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">Description complète</label>
                        <textarea name="description" id="description" rows="5"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-y">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Prix</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="purchase_price" class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat HT <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" step="0.01" min="0" required
                                class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                        </div>
                    </div>
                    <div>
                        <label for="sale_price" class="block text-xs font-medium text-gray-700 mb-1">Prix de vente TTC <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0" required
                                class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                        </div>
                    </div>
                    <div>
                        <label for="compare_price" class="block text-xs font-medium text-gray-700 mb-1">Prix barré</label>
                        <div class="relative">
                            <input type="number" name="compare_price" id="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0"
                                class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="tax_rate" class="block text-xs font-medium text-gray-700 mb-1">Taux de TVA</label>
                    <select name="tax_rate" id="tax_rate"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="20" {{ old('tax_rate', $product->tax_rate) == 20 ? 'selected' : '' }}>20% — Standard</option>
                        <option value="10" {{ old('tax_rate', $product->tax_rate) == 10 ? 'selected' : '' }}>10% — Intermédiaire</option>
                        <option value="5.5" {{ old('tax_rate', $product->tax_rate) == 5.5 ? 'selected' : '' }}>5.5% — Réduit</option>
                        <option value="2.1" {{ old('tax_rate', $product->tax_rate) == 2.1 ? 'selected' : '' }}>2.1% — Super réduit</option>
                        <option value="0" {{ old('tax_rate', $product->tax_rate) == 0 ? 'selected' : '' }}>0% — Exonéré</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock & Identifiants</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="sku" class="block text-xs font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="barcode" class="block text-xs font-medium text-gray-700 mb-1">Code-barres</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}"
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="weight" class="block text-xs font-medium text-gray-700 mb-1">Poids (kg)</label>
                        <input type="number" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" step="0.001" min="0"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                @if(!$product->has_variants)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div>
                        <label for="stock_quantity" class="block text-xs font-medium text-gray-700 mb-1">Quantité en stock <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="stock_alert_threshold" class="block text-xs font-medium text-gray-700 mb-1">Seuil d'alerte <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_alert_threshold" id="stock_alert_threshold" value="{{ old('stock_alert_threshold', $product->stock_alert_threshold) }}" min="0" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
                @else
                <div class="mt-3 flex items-start gap-2.5 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2.5">
                    <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[12px] text-blue-700">Le stock est géré par variante. Consultez la section variantes ci-dessous.</p>
                </div>
                <input type="hidden" name="stock_quantity" value="{{ $product->stock_quantity }}">
                <input type="hidden" name="stock_alert_threshold" value="{{ $product->stock_alert_threshold }}">
                @endif

                <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-[13px] text-gray-700">Suivre le stock de ce produit</span>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Images du produit</h2>

                @if($product->images->count() > 0)
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-700 mb-2">Images existantes</p>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        @foreach($product->images as $image)
                        <div class="relative group aspect-square">
                            <img src="{{ asset('storage/' . $image->path) }}" alt=""
                                class="w-full h-full object-cover rounded-lg border-2 {{ $image->is_primary ? 'border-orange-400' : 'border-gray-200' }}">
                            @if($image->is_primary)
                            <span class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-orange-500 text-white text-[10px] font-semibold rounded">Principale</span>
                            @endif
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-1.5">
                                @if(!$image->is_primary)
                                <button type="submit" form="form-primary-{{ $image->id }}"
                                    class="p-1.5 bg-white rounded-lg text-orange-600 hover:bg-orange-50 transition-colors" title="Définir comme principale">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                </button>
                                @endif
                                <button type="submit" form="form-delete-{{ $image->id }}"
                                    onclick="return confirm('Supprimer cette image ?')"
                                    class="p-1.5 bg-white rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Supprimer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div x-data="{
                    files: [],
                    dragging: false,
                    handleFiles(fileList) {
                        Array.from(fileList).filter(f => f.type.startsWith('image/')).forEach(file => {
                            this.files.push({ file, url: URL.createObjectURL(file) });
                        });
                        this.syncInput();
                    },
                    handleDrop(e) {
                        this.dragging = false;
                        if (e.dataTransfer && e.dataTransfer.files) this.handleFiles(e.dataTransfer.files);
                    },
                    remove(i) {
                        URL.revokeObjectURL(this.files[i].url);
                        this.files.splice(i, 1);
                        this.syncInput();
                    },
                    syncInput() {
                        const dt = new DataTransfer();
                        this.files.forEach(f => dt.items.add(f.file));
                        this.$refs.fileInput.files = dt.files;
                    }
                }">
                    <p class="text-xs font-medium text-gray-700 mb-2">Ajouter de nouvelles images</p>
                    <div @click="$refs.fileInput.click()"
                         @dragover.prevent="dragging = true"
                         @dragenter.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="dragging ? 'border-orange-400 bg-orange-50' : 'border-gray-200 bg-gray-50 hover:border-orange-300 hover:bg-orange-50/40'"
                         class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors select-none">
                        <svg class="w-7 h-7 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-[13px] font-medium text-gray-600">Cliquez ou glissez vos images ici</p>
                        <p class="text-[11px] text-gray-400 mt-1">JPEG, PNG, WEBP — max 5 Mo par image</p>
                        <input x-ref="fileInput" type="file" name="images[]" multiple accept="image/*"
                            @change="handleFiles($event.target.files)" class="hidden">
                    </div>
                    <div x-show="files.length > 0" x-cloak class="mt-3 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        <template x-for="(f, i) in files" :key="i">
                            <div class="relative group aspect-square">
                                <img :src="f.url" class="w-full h-full object-cover rounded-lg border border-gray-200">
                                <button type="button" @click.stop="remove(i)"
                                    class="absolute top-1 right-1 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-5">

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Publication</h2>
                <div class="space-y-3">
                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" id="status"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}>Archivé</option>
                        </select>
                    </div>
                    <div class="space-y-2 pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-[13px] text-gray-700">Mis en avant</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-[13px] text-gray-700">Nouveauté</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="has_variants" value="1" {{ old('has_variants', $product->has_variants) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-[13px] text-gray-700">Produit avec variantes</span>
                        </label>
                    </div>
                    <div class="pt-3 border-t border-gray-100 flex gap-2">
                        <button type="submit"
                            class="flex-1 h-9 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors">
                            Enregistrer
                        </button>
                        <a href="{{ route('admin.products.index') }}"
                            class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Catégorie</h2>
                <select name="category_id"
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Sans catégorie</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->full_path }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Statistiques</h2>
                <div class="space-y-2">
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                        <span class="text-[12px] text-gray-500">Stock total</span>
                        <span class="text-[13px] font-semibold text-gray-800">
                            {{ $product->has_variants ? $product->variants->sum('stock_quantity') : $product->stock_quantity }} pcs
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                        <span class="text-[12px] text-gray-500">Variantes</span>
                        <span class="text-[13px] font-semibold text-gray-800">{{ $product->variants->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                        <span class="text-[12px] text-gray-500">Vues</span>
                        <span class="text-[13px] font-semibold text-gray-800">{{ number_format($product->views_count ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-[12px] text-gray-500">Ventes</span>
                        <span class="text-[13px] font-semibold text-gray-800">{{ number_format($product->sales_count ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                 x-data="{ confirm: false }">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Zone de danger</h2>
                <p class="text-[12px] text-gray-500 mb-3">La suppression est irréversible. Toutes les images et variantes associées seront supprimées.</p>
                <template x-if="!confirm">
                    <button type="button" @click="confirm = true"
                        class="w-full h-9 border border-red-200 text-[13px] font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        Supprimer le produit
                    </button>
                </template>
                <template x-if="confirm">
                    <div class="space-y-2">
                        <p class="text-[12px] font-semibold text-red-700 text-center">Êtes-vous sûr ?</p>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="flex-1 no-ajax">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full h-9 bg-red-600 text-white text-[13px] font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Confirmer
                                </button>
                            </form>
                            <button type="button" @click="confirm = false"
                                class="flex-1 h-9 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Annuler
                            </button>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>
</form>

<div class="mt-5 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
        <div class="flex items-center gap-3">
            <h2 class="text-sm font-semibold text-gray-900">Variantes</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                {{ $product->variants->count() > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $product->variants->count() }} variante(s)
            </span>
        </div>
    </div>

    @if($product->variants->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Variante</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Prix</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($product->variants as $variant)
                @php
                    $vColor = $variant->attributeValues->firstWhere(fn($v) => $v->attribute && $v->attribute->slug === 'couleur');
                    $vOthers = $variant->attributeValues->filter(fn($v) => $v->attribute && $v->attribute->slug !== 'couleur')->values();
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            @if($variant->image)
                            <img src="{{ asset('storage/' . $variant->image) }}" class="w-8 h-8 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                            @elseif($vColor && $vColor->color_code)
                            <span class="w-8 h-8 rounded-lg border border-gray-200 flex-shrink-0" style="background:{{ $vColor->color_code }}"></span>
                            @else
                            <span class="w-8 h-8 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0"></span>
                            @endif
                            <div class="flex flex-wrap items-center gap-1">
                                @if($vColor)
                                <span class="font-medium text-gray-900">{{ $vColor->value }}</span>
                                @endif
                                @foreach($vOthers as $av)
                                <span class="text-[11px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $av->value }}</span>
                                @endforeach
                                @if(!$vColor && $vOthers->isEmpty())
                                <span class="text-gray-500">{{ $variant->name ?: 'Variante' }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-mono text-[12px] text-gray-500">{{ $variant->sku }}</td>
                    <td class="px-5 py-3 text-gray-800">
                        {{ $variant->sale_price !== null ? number_format($variant->sale_price, 0, ',', ' ') . ' F CFA' : '— (produit)' }}
                    </td>
                    <td class="px-5 py-3">
                        @if($variant->stock_quantity <= 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">Rupture</span>
                        @elseif($variant->stock_quantity <= 5)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">{{ $variant->stock_quantity }} pcs</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">{{ $variant->stock_quantity }} pcs</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $variant->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-100 bg-gray-50">
                    <td colspan="3" class="px-5 py-2.5 text-[12px] text-gray-500">{{ $product->variants->count() }} variante(s)</td>
                    <td class="px-5 py-2.5 text-[12px] font-semibold text-gray-700">{{ $product->variants->sum('stock_quantity') }} pcs au total</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="py-12 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
        <p class="text-[13px] text-gray-400">Aucune variante pour ce produit.</p>
    </div>
    @endif
</div>

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
