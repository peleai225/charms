@extends('layouts.admin')

@section('title', 'Nouveau code promo')
@section('page-title', 'Nouveau code promo')

@section('content')
<form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-6">
    @csrf

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Créer le code promo</button>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informations</h3>
                <div class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Code *</label>
                            <div class="flex gap-2">
                                <input type="text" name="code" value="{{ old('code') }}" class="flex-1 px-4 py-2 border border-slate-300 rounded-xl uppercase font-mono" required>
                                <button type="button" onclick="generateCode()" class="px-3 py-2 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300" title="Générer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                            @error('code')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                            @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Réduction</h3>
                <div class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Type *</label>
                            <select name="type" id="couponType" class="w-full px-4 py-2 border border-slate-300 rounded-xl" onchange="toggleValueField()">
                                <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Montant fixe (F CFA)</option>
                                <option value="free_shipping" {{ old('type') === 'free_shipping' ? 'selected' : '' }}>Livraison gratuite</option>
                            </select>
                        </div>
                        <div id="valueField">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Valeur *</label>
                            <input type="number" name="value" value="{{ old('value', 0) }}" step="0.01" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Montant min. de commande</label>
                            <input type="number" name="min_order_amount" value="{{ old('min_order_amount') }}" step="100" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: 10000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Réduction max.</label>
                            <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" step="100" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: 5000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Limites d'utilisation</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Limite totale</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" min="1" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Illimité">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Limite par client</label>
                        <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user') }}" min="1" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Illimité">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Validité</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date de début</label>
                        <input type="date" name="starts_at" value="{{ old('starts_at') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date de fin</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Options</h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-slate-700">Actif</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="first_order_only" value="1" {{ old('first_order_only') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-slate-700">Première commande uniquement</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function generateCode() {
    fetch('{{ route("admin.coupons.generate-code") }}')
        .then(r => r.json())
        .then(data => {
            document.querySelector('input[name="code"]').value = data.code;
        });
}

function toggleValueField() {
    const type = document.getElementById('couponType').value;
    document.getElementById('valueField').style.display = type === 'free_shipping' ? 'none' : 'block';
}
toggleValueField();
</script>
@endsection

