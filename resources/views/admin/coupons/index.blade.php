@extends('layouts.admin')

@section('title', 'Codes promo')
@section('page-title', 'Codes promo')

@section('content')
<div class="space-y-6">
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
        <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau code promo
        </a>
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
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
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
</div>
@endsection

