@extends('layouts.admin')

@section('title', 'Nouveau produit')
@section('page-title', 'Ajouter un produit')

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

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="no-ajax">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 space-y-5">

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations générales</h2>
                <div class="space-y-3">
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">Nom du produit <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('name') border-red-300 @enderror">
                        @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="short_description" class="block text-xs font-medium text-gray-700 mb-1">Description courte</label>
                        <textarea name="short_description" id="short_description" rows="2"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none">{{ old('short_description') }}</textarea>
                    </div>
                    <div>
                        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">Description complète</label>
                        <textarea name="description" id="description" rows="5"
                            class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-y">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Prix</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="purchase_price" class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat HT <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', 0) }}" step="0.01" min="0" required
                                class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                        </div>
                    </div>
                    <div>
                        <label for="sale_price" class="block text-xs font-medium text-gray-700 mb-1">Prix de vente TTC <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price', 0) }}" step="0.01" min="0" required
                                class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                        </div>
                    </div>
                    <div>
                        <label for="compare_price" class="block text-xs font-medium text-gray-700 mb-1">Prix barré</label>
                        <div class="relative">
                            <input type="number" name="compare_price" id="compare_price" value="{{ old('compare_price') }}" step="0.01" min="0"
                                class="w-full h-9 pl-3 pr-14 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">F CFA</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="tax_rate" class="block text-xs font-medium text-gray-700 mb-1">Taux de TVA</label>
                    <select name="tax_rate" id="tax_rate"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="20" {{ old('tax_rate', 20) == 20 ? 'selected' : '' }}>20% — Standard</option>
                        <option value="10" {{ old('tax_rate') == 10 ? 'selected' : '' }}>10% — Intermédiaire</option>
                        <option value="5.5" {{ old('tax_rate') == 5.5 ? 'selected' : '' }}>5.5% — Réduit</option>
                        <option value="2.1" {{ old('tax_rate') == 2.1 ? 'selected' : '' }}>2.1% — Super réduit</option>
                        <option value="0" {{ old('tax_rate') == 0 ? 'selected' : '' }}>0% — Exonéré</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock & Identifiants</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="sku" class="block text-xs font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('sku') border-red-300 @enderror">
                        @error('sku') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="barcode" class="block text-xs font-medium text-gray-700 mb-1">Code-barres</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="weight" class="block text-xs font-medium text-gray-700 mb-1">Poids (kg)</label>
                        <input type="number" name="weight" id="weight" value="{{ old('weight') }}" step="0.001" min="0"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="stock_quantity" class="block text-xs font-medium text-gray-700 mb-1">Quantité en stock <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="stock_alert_threshold" class="block text-xs font-medium text-gray-700 mb-1">Seuil d'alerte <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_alert_threshold" id="stock_alert_threshold" value="{{ old('stock_alert_threshold', 5) }}" min="0" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-[13px] text-gray-700">Suivre le stock de ce produit</span>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                 x-data="{
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
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Images du produit</h2>
                <div @click="$refs.fileInput.click()"
                     @dragover.prevent="dragging = true"
                     @dragenter.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="handleDrop($event)"
                     :class="dragging ? 'border-orange-400 bg-orange-50' : 'border-gray-200 bg-gray-50 hover:border-orange-300 hover:bg-orange-50/40'"
                     class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-colors select-none">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-[13px] font-medium text-gray-600">Cliquez ou glissez vos images ici</p>
                    <p class="text-[11px] text-gray-400 mt-1">JPEG, PNG, WEBP — max 5 Mo par image</p>
                    <input x-ref="fileInput" type="file" name="images[]" multiple accept="image/*"
                        @change="handleFiles($event.target.files)" class="hidden">
                </div>
                <div x-show="files.length > 0" x-cloak class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    <template x-for="(f, i) in files" :key="i">
                        <div class="relative group aspect-square">
                            <img :src="f.url" class="w-full h-full object-cover rounded-lg border border-gray-200">
                            <button type="button" @click.stop="remove(i)"
                                class="absolute top-1 right-1 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <template x-if="i === 0">
                                <span class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-orange-500 text-white text-[10px] font-semibold rounded">Principale</span>
                            </template>
                        </div>
                    </template>
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
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        </select>
                    </div>
                    <div class="space-y-2 pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-[13px] text-gray-700">Mis en avant</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_new" value="1" {{ old('is_new', true) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-[13px] text-gray-700">Nouveauté</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="has_variants" value="1" {{ old('has_variants') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span class="text-[13px] text-gray-700">Produit avec variantes</span>
                        </label>
                    </div>
                    <div class="pt-3 border-t border-gray-100 flex gap-2">
                        <button type="submit"
                            class="flex-1 h-9 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors">
                            Créer le produit
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
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->full_path }}
                    </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
</form>

@endsection
