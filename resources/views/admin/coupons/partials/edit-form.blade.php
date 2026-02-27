<form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-4 coupon-edit-form" x-data="couponEditForm()">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Code *</label>
            <div class="flex gap-2">
                <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-xl uppercase font-mono text-sm">
                <button type="button" @click="generateCode()" class="px-3 py-2 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300" title="Générer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
            @error('code')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
            <input type="text" name="name" value="{{ old('name', $coupon->name) }}" required
                class="w-full px-4 py-2 border border-slate-300 rounded-xl">
            @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Type *</label>
            <select name="type" class="w-full px-4 py-2 border border-slate-300 rounded-xl" x-model="type">
                <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Montant fixe (F)</option>
                <option value="free_shipping" {{ old('type', $coupon->type) === 'free_shipping' ? 'selected' : '' }}>Livraison gratuite</option>
            </select>
        </div>
        <div x-show="type !== 'free_shipping'">
            <label class="block text-sm font-medium text-slate-700 mb-1">Valeur *</label>
            <input type="number" name="value" value="{{ old('value', $coupon->value) }}" step="0.01" min="0"
                :required="type !== 'free_shipping'"
                class="w-full px-4 py-2 border border-slate-300 rounded-xl">
            @error('value')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Date d'expiration</label>
        <input type="date" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}"
            class="w-full px-4 py-2 border border-slate-300 rounded-xl">
        @error('expires_at')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
        <span class="text-sm text-slate-700">Actif</span>
    </label>

    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-sm text-slate-500 hover:text-slate-700">Formulaire complet →</a>
        <div class="flex gap-3">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Enregistrer
            </button>
            <button type="button"
                    @click="$dispatch('close-modal', 'coupon-edit-{{ $coupon->id }}')"
                    class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                Annuler
            </button>
        </div>
    </div>
</form>
