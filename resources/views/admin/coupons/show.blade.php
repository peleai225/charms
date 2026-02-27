@extends('layouts.admin')

@section('title', 'Code promo ' . $coupon->code)
@section('page-title', 'Détails du code promo')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl">
            Modifier
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Informations</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Code</dt>
                        <dd class="mt-1 font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded inline-block">{{ $coupon->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Nom</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $coupon->name }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-700">{{ $coupon->description ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Réduction</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Type</dt>
                        <dd class="mt-1 font-medium text-green-600">{{ $coupon->type_label }}</dd>
                    </div>
                    @if($coupon->type !== 'free_shipping')
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Valeur</dt>
                        <dd class="mt-1 font-medium">{{ $coupon->type === 'percentage' ? $coupon->value . '%' : number_format($coupon->value, 0, ',', ' ') . ' F' }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Montant min.</dt>
                        <dd class="mt-1">{{ $coupon->min_order_amount ? number_format($coupon->min_order_amount, 0, ',', ' ') . ' F' : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Réduction max.</dt>
                        <dd class="mt-1">{{ $coupon->max_discount_amount ? number_format($coupon->max_discount_amount, 0, ',', ' ') . ' F' : '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Utilisations</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Client</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Commande</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($coupon->usages as $usage)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $usage->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 text-sm">{{ $usage->customer?->full_name ?? 'Invité' }}</td>
                                <td class="px-4 py-2">
                                    @if($usage->order)
                                        <a href="{{ route('admin.orders.show', $usage->order) }}" class="text-blue-600 hover:underline font-mono">{{ $usage->order->order_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-right font-medium">{{ $usage->discount_amount ? number_format($usage->discount_amount, 0, ',', ' ') . ' F' : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune utilisation</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Statut</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Statut</span>
                        @php
                            $statusLabels = ['active' => 'Actif', 'inactive' => 'Inactif', 'expired' => 'Expiré', 'scheduled' => 'Programmé', 'exhausted' => 'Épuisé'];
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($coupon->status === 'active') bg-green-100 text-green-700
                            @elseif($coupon->status === 'expired') bg-red-100 text-red-700
                            @elseif($coupon->status === 'inactive') bg-slate-100 text-slate-700
                            @else bg-amber-100 text-amber-700 @endif">
                            {{ $statusLabels[$coupon->status] ?? $coupon->status }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Utilisations</span>
                        <span class="font-medium">{{ $coupon->usages->count() }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Validité</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Début</span>
                        <span>{{ $coupon->starts_at?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Fin</span>
                        <span>{{ $coupon->expires_at?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">1ère commande uniquement</span>
                        <span>{{ $coupon->first_order_only ? 'Oui' : 'Non' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
