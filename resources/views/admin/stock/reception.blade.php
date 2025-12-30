@extends('layouts.admin')

@section('title', 'Réception fournisseur')
@section('page-title', 'Réception de marchandises')

@section('content')
<form method="POST" action="{{ route('admin.stock.store-reception') }}" class="space-y-6" x-data="receptionForm()">
    @csrf

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.stock.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Enregistrer la réception
        </button>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Formulaire principal -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informations de réception</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fournisseur *</label>
                        <select name="supplier_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                            <option value="">Sélectionner un fournisseur</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">N° de référence (BL/Facture)</label>
                        <input type="text" name="reference" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Ex: BL-2024-001">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Notes additionnelles..."></textarea>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Produits à réceptionner</h3>
                    <button type="button" @click="addItem()" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-sm font-medium text-slate-500">Ligne #<span x-text="index + 1"></span></span>
                                <button type="button" @click="removeItem(index)" class="p-1 text-red-500 hover:bg-red-100 rounded-lg" x-show="items.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="grid md:grid-cols-4 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Produit *</label>
                                    <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="updateVariants(index)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" required>
                                        <option value="">Sélectionner</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-variants='@json($product->variants)'>{{ $product->name }} (Stock: {{ $product->stock_quantity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Quantité *</label>
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" min="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Coût unitaire</label>
                                    <input type="number" :name="'items[' + index + '][unit_cost]'" x-model="item.unit_cost" step="1" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="F CFA">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Résumé</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Lignes</dt>
                        <dd class="font-semibold text-slate-900" x-text="items.length"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Quantité totale</dt>
                        <dd class="font-semibold text-slate-900" x-text="totalQuantity()"></dd>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-slate-100">
                        <dt class="text-slate-500">Valeur estimée</dt>
                        <dd class="font-bold text-green-600" x-text="formatPrice(totalValue())"></dd>
                    </div>
                </dl>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 p-6">
                <h4 class="font-semibold text-blue-900 mb-2">💡 Conseils</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Vérifiez les quantités avant validation</li>
                    <li>• Le coût unitaire met à jour le prix d'achat</li>
                    <li>• Le stock sera mis à jour automatiquement</li>
                </ul>
            </div>
        </div>
    </div>
</form>

<script>
function receptionForm() {
    return {
        items: [{ product_id: '', quantity: 1, unit_cost: '' }],
        
        addItem() {
            this.items.push({ product_id: '', quantity: 1, unit_cost: '' });
        },
        
        removeItem(index) {
            this.items.splice(index, 1);
        },
        
        updateVariants(index) {
            // Gérer les variantes si nécessaire
        },
        
        totalQuantity() {
            return this.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        },
        
        totalValue() {
            return this.items.reduce((sum, item) => {
                return sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0));
            }, 0);
        },
        
        formatPrice(value) {
            return new Intl.NumberFormat('fr-FR').format(value) + ' F CFA';
        }
    }
}
</script>
@endsection

