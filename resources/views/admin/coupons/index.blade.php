@extends('layouts.admin')

@section('title', 'Codes promo')

@section('content')
<div class="p-4 sm:p-6 space-y-5" x-data>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Codes promo</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">{{ $coupons->total() }} code(s)</p>
        </div>
        <div class="flex gap-2">
            <button type="button"
                    @click="$dispatch('open-modal', 'coupon-create')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau code promo
            </button>
            <a href="{{ route('admin.coupons.create') }}"
               class="h-9 px-3 inline-flex items-center text-[13px] font-medium border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors"
               title="Formulaire avancé">
                Avancé
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher un code..."
                       class="pl-9 pr-4 h-9 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-52">
            </div>
            <select name="status" class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les statuts</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Actifs</option>
                <option value="expired"  {{ request('status') === 'expired'  ? 'selected' : '' }}>Expirés</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
            <button type="submit" class="h-9 px-4 bg-gray-800 text-white font-medium text-[13px] rounded-lg hover:bg-gray-700 transition-colors">Filtrer</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.coupons.index') }}" class="h-9 px-3 inline-flex items-center text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table Desktop --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Code</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Nom</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Réduction</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Utilisations</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Validité</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($coupons as $coupon)
                    <tr class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg text-[12px]">{{ $coupon->code }}</span>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-medium text-gray-900">{{ $coupon->name }}</td>
                        <td class="px-5 py-4">
                            <span class="text-[13px] font-semibold text-green-600">{{ $coupon->type_label }}</span>
                            @if($coupon->min_order_amount)
                                <p class="text-[11px] text-gray-400">Min: {{ format_price($coupon->min_order_amount) }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="text-[13px] font-semibold text-gray-800">{{ $coupon->usages_count }}</span>
                            @if($coupon->usage_limit)
                                <span class="text-[12px] text-gray-400">/ {{ $coupon->usage_limit }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[12px] text-gray-500">
                            @if($coupon->starts_at && $coupon->expires_at)
                                {{ $coupon->starts_at->format('d/m/Y') }} - {{ $coupon->expires_at->format('d/m/Y') }}
                            @elseif($coupon->expires_at)
                                Jusqu'au {{ $coupon->expires_at->format('d/m/Y') }}
                            @else
                                <span class="text-green-600 font-medium">Illimité</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full
                                @switch($coupon->status)
                                    @case('active') bg-green-50 text-green-700 @break
                                    @case('inactive') bg-gray-100 text-gray-500 @break
                                    @case('expired') bg-red-50 text-red-700 @break
                                    @case('scheduled') bg-blue-50 text-blue-700 @break
                                    @case('exhausted') bg-amber-50 text-amber-700 @break
                                    @default bg-gray-100 text-gray-500
                                @endswitch">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @switch($coupon->status)
                                        @case('active') bg-green-500 @break
                                        @case('inactive') bg-gray-400 @break
                                        @case('expired') bg-red-500 @break
                                        @case('scheduled') bg-blue-500 @break
                                        @case('exhausted') bg-amber-500 @break
                                        @default bg-gray-400
                                    @endswitch"></span>
                                @switch($coupon->status)
                                    @case('active') Actif @break
                                    @case('inactive') Inactif @break
                                    @case('expired') Expiré @break
                                    @case('scheduled') Programmé @break
                                    @case('exhausted') Épuisé @break
                                    @default {{ $coupon->status }}
                                @endswitch
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.coupons.show', $coupon) }}"
                                   class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                   title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" @click="$dispatch('open-modal', 'coupon-edit-{{ $coupon->id }}')"
                                        class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                        title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Supprimer ce code promo ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-all" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <p class="text-[13px] text-gray-400 mb-1">Aucun code promo</p>
                            <p class="text-[12px] text-gray-300">Créez des codes promo pour stimuler vos ventes</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $coupons->links() }}</div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($coupons as $coupon)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg text-[12px]">{{ $coupon->code }}</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                    @switch($coupon->status)
                        @case('active') bg-green-50 text-green-700 @break
                        @case('inactive') bg-gray-100 text-gray-500 @break
                        @case('expired') bg-red-50 text-red-700 @break
                        @default bg-gray-100 text-gray-500
                    @endswitch">
                    @switch($coupon->status)
                        @case('active') Actif @break
                        @case('inactive') Inactif @break
                        @case('expired') Expiré @break
                        @case('scheduled') Programmé @break
                        @case('exhausted') Épuisé @break
                        @default {{ $coupon->status }}
                    @endswitch
                </span>
            </div>
            <p class="text-[13px] font-medium text-gray-900">{{ $coupon->name }}</p>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-[13px] text-green-600 font-semibold">{{ $coupon->type_label }}</span>
                <span class="text-[12px] text-gray-500">{{ $coupon->usages_count }}@if($coupon->usage_limit)/{{ $coupon->usage_limit }}@endif utilisé(s)</span>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <p class="text-[13px] text-gray-400">Aucun code promo</p>
        </div>
        @endforelse
    </div>

    {{-- Modals édition --}}
    @foreach($coupons as $coupon)
    <x-admin.modal id="coupon-edit-{{ $coupon->id }}" title="Modifier {{ $coupon->code }}" maxWidth="max-w-xl" :open="request('open_modal') === 'edit' && request('coupon_id') == $coupon->id">
        @include('admin.coupons.partials.edit-form', ['coupon' => $coupon])
    </x-admin.modal>
    @endforeach

    {{-- Modal création rapide --}}
    <x-admin.modal id="coupon-create" title="Nouveau code promo" maxWidth="max-w-xl" :open="request('open_modal') === 'create' || ($errors->any() && request('open_modal') !== 'edit')">
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-4" x-data="couponModalForm()">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal_code" class="block text-[13px] font-medium text-gray-700 mb-1">Code *</label>
                    <div class="flex gap-2">
                        <input type="text" name="code" id="modal_code" value="{{ old('code') }}" required
                            class="flex-1 h-9 px-3 border border-gray-200 rounded-lg uppercase font-mono text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" @click="generateCode()" class="h-9 px-2.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors" title="Générer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                    @error('code')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="modal_name" class="block text-[13px] font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="name" id="modal_name" value="{{ old('name') }}" required
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal_type" class="block text-[13px] font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" id="modal_type"
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        x-model="type" @change="toggleValueField()">
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                        <option value="fixed"      {{ old('type') === 'fixed'      ? 'selected' : '' }}>Montant fixe (F)</option>
                        <option value="free_shipping" {{ old('type') === 'free_shipping' ? 'selected' : '' }}>Livraison gratuite</option>
                    </select>
                </div>
                <div x-show="type !== 'free_shipping'">
                    <label for="modal_value" class="block text-[13px] font-medium text-gray-700 mb-1">Valeur *</label>
                    <input type="number" name="value" id="modal_value" value="{{ old('value', 0) }}" step="0.01" min="0"
                        :required="type !== 'free_shipping'"
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('value')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="modal_expires_at" class="block text-[13px] font-medium text-gray-700 mb-1">Date d'expiration</label>
                <input type="date" name="expires_at" id="modal_expires_at" value="{{ old('expires_at') }}"
                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('expires_at')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-[13px] text-gray-700">Actif</span>
            </label>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="h-9 px-5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors">
                    Créer le code promo
                </button>
                <button type="button"
                        @click="$dispatch('close-modal', 'coupon-create')"
                        class="h-9 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-[13px] rounded-lg transition-colors">
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
        init() { this.toggleValueField(); },
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
