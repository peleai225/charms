@extends('layouts.admin')

@section('title', 'Modifier ' . $category->name)
@section('page-title', 'Modifier la catégorie')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('description', $category->description) }}</textarea>
            </div>

            <div>
                <label for="parent_id" class="block text-sm font-medium text-slate-700 mb-1">Catégorie parente</label>
                <select name="parent_id" id="parent_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Aucune (catégorie racine)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="order" class="block text-sm font-medium text-slate-700 mb-1">Ordre d'affichage</label>
                <input type="number" name="order" id="order" value="{{ old('order', $category->order) }}" min="0"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-slate-700 mb-1">Image</label>
                @if($category->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $category->image) }}" alt="" class="w-24 h-24 object-cover rounded-lg">
                    </div>
                @endif
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Active</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Mise en avant</span>
                </label>
            </div>
        </div>

        <!-- Tarification en gros -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6"
             x-data="{
                rules: {{ json_encode(old('bulk_pricing_rules') ? json_decode(old('bulk_pricing_rules'), true) : ($category->bulk_pricing_rules ?? [])) }},
                addRule() { this.rules.push({ min_qty: '', unit_price: '' }) },
                removeRule(idx) { this.rules.splice(idx, 1) },
                get rulesJson() {
                    const valid = this.rules.filter(r => r.min_qty && r.unit_price !== '' && r.unit_price !== null)
                    return valid.length ? JSON.stringify(valid) : ''
                }
             }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Tarification en gros</h3>
                <button type="button" @click="addRule()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">+ Ajouter un palier</button>
            </div>
            <template x-if="rules.length === 0">
                <p class="text-sm text-slate-400">Aucun palier. Le prix standard des produits s'applique.</p>
            </template>
            <div class="space-y-2">
                <template x-for="(rule, idx) in rules" :key="idx">
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label x-show="idx === 0" class="block text-xs text-slate-500 mb-1">Qte min.</label>
                            <input x-model.number="rule.min_qty" type="number" min="2" step="1" placeholder="ex: 3"
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        <div class="flex-1">
                            <label x-show="idx === 0" class="block text-xs text-slate-500 mb-1">Prix unitaire (F CFA)</label>
                            <input x-model.number="rule.unit_price" type="number" min="0" step="1" placeholder="ex: 3000"
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        <button type="button" @click="removeRule(idx)" class="px-2 py-2 text-slate-400 hover:text-red-500" title="Supprimer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <p class="text-xs text-slate-400 mt-3">Ces paliers s'appliquent a tous les produits de cette categorie qui n'ont pas leurs propres paliers.</p>
            <input type="hidden" name="bulk_pricing_rules" :value="rulesJson">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Enregistrer
            </button>
            <a href="{{ route('admin.categories.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

