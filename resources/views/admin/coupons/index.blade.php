@extends('layouts.admin')

@section('title', 'Codes promo')
@section('page-title', 'Codes promo')

@section('content')
<div class="space-y-6" x-data>
    <div class="flex items-center justify-between">
        <form method="GET" class="flex gap-3">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="px-4 py-2 border border-slate-300 rounded-xl">
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-xl">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirés</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-300">Filtrer</button>
        </form>
        <div class="flex gap-2">
            <button type="button"
                    @click="$dispatch('open-modal', 'coupon-create')"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau code promo
            </button>
            <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 inline-flex items-center gap-2" title="Formulaire complet">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Options avancées
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Code</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Réduction</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Utilisations</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Validité</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $coupon->code }}</span>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $coupon->name }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-green-600">{{ $coupon->type_label }}</span>
                            @if($coupon->min_order_amount)
                                <br><span class="text-xs text-slate-500">Min: {{ format_price($coupon->min_order_amount) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-medium">{{ $coupon->usages_count }}</span>
                            @if($coupon->usage_limit)
                                <span class="text-slate-500">/ {{ $coupon->usage_limit }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($coupon->starts_at && $coupon->expires_at)
                                {{ $coupon->starts_at->format('d/m/Y') }} - {{ $coupon->expires_at->format('d/m/Y') }}
                            @elseif($coupon->expires_at)
                                Jusqu'au {{ $coupon->expires_at->format('d/m/Y') }}
                            @else
                                Illimité
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColors = [
                                    'active' => 'green',
                                    'inactive' => 'slate',
                                    'expired' => 'red',
                                    'scheduled' => 'blue',
                                    'exhausted' => 'amber',
                                ];
                                $statusLabels = [
                                    'active' => 'Actif',
                                    'inactive' => 'Inactif',
                                    'expired' => 'Expiré',
                                    'scheduled' => 'Programmé',
                                    'exhausted' => 'Épuisé',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $statusColors[$coupon->status] }}-100 text-{{ $statusColors[$coupon->status] }}-700">
                                {{ $statusLabels[$coupon->status] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.coupons.show', $coupon) }}" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" @click="$dispatch('open-modal', 'coupon-edit-{{ $coupon->id }}')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Supprimer ce code promo ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">Aucun code promo</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">{{ $coupons->links() }}</div>
        @endif
    </div>

    <!-- Modals édition -->
    @foreach($coupons as $coupon)
    <x-admin.modal id="coupon-edit-{{ $coupon->id }}" title="Modifier {{ $coupon->code }}" maxWidth="max-w-xl" :open="request('open_modal') === 'edit' && request('coupon_id') == $coupon->id">
        @include('admin.coupons.partials.edit-form', ['coupon' => $coupon])
    </x-admin.modal>
    @endforeach

    <!-- Modal création rapide -->
    <x-admin.modal id="coupon-create" title="Nouveau code promo" maxWidth="max-w-xl" :open="request('open_modal') === 'create' || ($errors->any() && request('open_modal') !== 'edit')">
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-4" x-data="couponModalForm()">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal_code" class="block text-sm font-medium text-slate-700 mb-1">Code *</label>
                    <div class="flex gap-2">
                        <input type="text" name="code" id="modal_code" value="{{ old('code') }}" required
                            class="flex-1 px-4 py-2 border border-slate-300 rounded-xl uppercase font-mono text-sm">
                        <button type="button" @click="generateCode()" class="px-3 py-2 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300" title="Générer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                    @error('code')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="modal_name" class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                    <input type="text" name="name" id="modal_name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal_type" class="block text-sm font-medium text-slate-700 mb-1">Type *</label>
                    <select name="type" id="modal_type" class="w-full px-4 py-2 border border-slate-300 rounded-xl" x-model="type" @change="toggleValueField()">
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Montant fixe (F)</option>
                        <option value="free_shipping" {{ old('type') === 'free_shipping' ? 'selected' : '' }}>Livraison gratuite</option>
                    </select>
                </div>
                <div x-show="type !== 'free_shipping'">
                    <label for="modal_value" class="block text-sm font-medium text-slate-700 mb-1">Valeur *</label>
                    <input type="number" name="value" id="modal_value" value="{{ old('value', 0) }}" step="0.01" min="0"
                        :required="type !== 'free_shipping'"
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    @error('value')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="modal_expires_at" class="block text-sm font-medium text-slate-700 mb-1">Date d'expiration</label>
                <input type="date" name="expires_at" id="modal_expires_at" value="{{ old('expires_at') }}"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                @error('expires_at')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-slate-700">Actif</span>
            </label>

            <div class="flex gap-3 pt-4 border-t border-slate-200">
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                    Créer le code promo
                </button>
                <button type="button"
                        @click="$dispatch('close-modal', 'coupon-create')"
                        class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </x-admin.modal>
</div>

@push('scripts')
<script>
function couponEditForm() {
    return {
        type: 'percentage',
        init() {
            const typeSelect = this.$el.querySelector('select[name="type"]');
            if (typeSelect) this.type = typeSelect.value;
        },
        async generateCode() {
            try {
                const r = await fetch('{{ route("admin.coupons.generate-code") }}');
                const data = await r.json();
                const codeInput = this.$el.querySelector('input[name="code"]');
                if (codeInput) codeInput.value = data.code || '';
            } catch (e) { console.error(e); }
        }
    };
}

function couponModalForm() {
    return {
        type: '{{ old('type', 'percentage') }}',
        init() {
            this.toggleValueField();
        },
        toggleValueField() {
            const valInput = document.getElementById('modal_value');
            if (valInput) valInput.required = this.type !== 'free_shipping';
        },
        async generateCode() {
            try {
                const r = await fetch('{{ route("admin.coupons.generate-code") }}');
                const data = await r.json();
                document.getElementById('modal_code').value = data.code || '';
            } catch (e) { console.error(e); }
        }
    };
}
</script>
@endpush
@endsection

