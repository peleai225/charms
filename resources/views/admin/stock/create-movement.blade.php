@extends('layouts.admin')

@section('title', 'Nouveau mouvement')
@section('page-title', 'Nouveau mouvement de stock')

@section('content')
<form method="POST" action="{{ route('admin.stock.store-movement') }}" class="max-w-2xl">
    @csrf

    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.stock.movements') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Produit *</label>
            <select name="product_id" id="productSelect" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                <option value="">Sélectionner un produit</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-variants='@json($product->variants)'>
                        {{ $product->name }} (Stock: {{ $product->stock_quantity }})
                    </option>
                @endforeach
            </select>
        </div>

        <div id="variantField" class="hidden">
            <label class="block text-sm font-medium text-slate-700 mb-1">Variante</label>
            <select name="variant_id" id="variantSelect" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                <option value="">Produit principal</option>
            </select>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Type de mouvement *</label>
                <select name="type" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                    <option value="in">Entrée (réception)</option>
                    <option value="out">Sortie</option>
                    <option value="adjustment">Ajustement (inventaire)</option>
                    <option value="return">Retour client</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Quantité *</label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Raison *</label>
            <input type="text" name="reason" value="{{ old('reason') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: Réception commande #123" required>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Référence</label>
                <input type="text" name="reference" value="{{ old('reference') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="N° BL, facture...">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fournisseur</label>
                <select name="supplier_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    <option value="">Aucun</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Coût unitaire (F CFA)</label>
            <input type="number" name="unit_cost" value="{{ old('unit_cost') }}" step="1" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
                Enregistrer le mouvement
            </button>
        </div>
    </div>
</form>

<script>
document.getElementById('productSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const variants = JSON.parse(option.dataset.variants || '[]');
    const variantField = document.getElementById('variantField');
    const variantSelect = document.getElementById('variantSelect');
    
    variantSelect.innerHTML = '<option value="">Produit principal</option>';
    
    if (variants.length > 0) {
        variants.forEach(v => {
            variantSelect.innerHTML += `<option value="${v.id}">${v.sku} (Stock: ${v.stock_quantity})</option>`;
        });
        variantField.classList.remove('hidden');
    } else {
        variantField.classList.add('hidden');
    }
});
</script>
@endsection

