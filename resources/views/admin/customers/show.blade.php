@extends('layouts.admin')

@section('title', 'Client — ' . $customer->full_name)

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.customers.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">{{ $customer->full_name }}</h1>
                    @php
                        $headerStatusColors = [
                            'active'   => 'bg-green-50 text-green-700 border-green-200',
                            'inactive' => 'bg-gray-50 text-gray-700 border-gray-200',
                            'blocked'  => 'bg-red-50 text-red-700 border-red-200',
                        ];
                        $headerStatusLabels = ['active' => 'Actif', 'inactive' => 'Inactif', 'blocked' => 'Bloqué'];
                        $hsc = $headerStatusColors[$customer->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                        $hsl = $headerStatusLabels[$customer->status] ?? ucfirst($customer->status);
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $hsc }}">
                        {{ $hsl }}
                    </span>
                </div>
                <p class="text-[13px] text-gray-500 mt-0.5">Client depuis {{ $customer->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.customers.edit', $customer) }}"
           class="h-9 px-4 flex items-center gap-2 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier
        </a>
    </div>

    {{-- 2-column grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Left: Orders + Addresses (lg:col-span-2) ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Dernières commandes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Dernières commandes</h2>
                    <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}"
                       class="text-[12px] text-orange-500 hover:text-orange-600 font-semibold transition">
                        Voir tout →
                    </a>
                </div>

                @if($customer->orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-700 whitespace-nowrap">N° Commande</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-700 whitespace-nowrap">Date</th>
                                <th class="px-4 py-2.5 text-right font-semibold text-gray-700 whitespace-nowrap">Montant</th>
                                <th class="px-4 py-2.5 text-center font-semibold text-gray-700 whitespace-nowrap">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customer->orders as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="font-semibold text-gray-900 hover:text-orange-600 transition">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                    {{ format_price($order->total) }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    @php
                                        $orderStatusColors = [
                                            'pending'    => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                            'confirmed'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'shipped'    => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'delivered'  => 'bg-green-50 text-green-700 border-green-200',
                                            'cancelled'  => 'bg-red-50 text-red-700 border-red-200',
                                            'refunded'   => 'bg-orange-50 text-orange-700 border-orange-200',
                                        ];
                                        $osc = $orderStatusColors[$order->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $osc }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-4 py-10 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-[13px] text-gray-500">Aucune commande</p>
                </div>
                @endif
            </div>

            {{-- Adresses --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Adresses</h2>
                </div>

                @if($customer->addresses->count() > 0)
                <div class="grid sm:grid-cols-2 gap-4 p-4">
                    @foreach($customer->addresses as $address)
                    <div class="p-4 border border-gray-200 rounded-lg text-[13px]">
                        @if($address->is_default_shipping)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border bg-green-50 text-green-700 border-green-200 mb-2">
                            Par défaut
                        </span>
                        @endif
                        <p class="font-medium text-gray-900">{{ $address->first_name }} {{ $address->last_name }}</p>
                        @if($address->company)
                            <p class="text-gray-500 mt-0.5">{{ $address->company }}</p>
                        @endif
                        <p class="text-gray-600 mt-1">{{ $address->address }}</p>
                        <p class="text-gray-600">{{ $address->postal_code }} {{ $address->city }}</p>
                        <p class="text-gray-600">{{ $address->country }}</p>
                        @if($address->phone)
                            <p class="text-gray-500 mt-1.5">{{ $address->phone }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-4 py-10 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-[13px] text-gray-500">Aucune adresse enregistrée</p>
                </div>
                @endif
            </div>

        </div>

        {{-- ── Right sidebar ── --}}
        <div class="space-y-5">

            {{-- Infos client --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Informations</h2>
                </div>
                <div class="p-4 space-y-3 text-[13px]">
                    <div>
                        <p class="text-xs font-medium text-gray-700 mb-0.5">Email</p>
                        <p class="text-gray-900 break-all">{{ $customer->email }}</p>
                    </div>
                    @if($customer->phone)
                    <div>
                        <p class="text-xs font-medium text-gray-700 mb-0.5">Téléphone</p>
                        <p class="text-gray-900">{{ $customer->phone }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs font-medium text-gray-700 mb-0.5">Date d'inscription</p>
                        <p class="text-gray-900">{{ $customer->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-700 mb-0.5">Statut</p>
                        @php
                            $infoStatusColors = [
                                'active'   => 'bg-green-50 text-green-700 border-green-200',
                                'inactive' => 'bg-gray-50 text-gray-700 border-gray-200',
                                'blocked'  => 'bg-red-50 text-red-700 border-red-200',
                            ];
                            $infoStatusLabels = ['active' => 'Actif', 'inactive' => 'Inactif', 'blocked' => 'Bloqué'];
                            $isc = $infoStatusColors[$customer->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            $isl = $infoStatusLabels[$customer->status] ?? ucfirst($customer->status);
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $isc }}">
                            {{ $isl }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Statistiques</h2>
                </div>
                <div class="divide-y divide-gray-100 text-[13px]">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <span class="text-gray-600">Total commandes</span>
                        <span class="font-semibold text-gray-900 tabular-nums">{{ $customer->orders->count() }}</span>
                    </div>
                    <div class="px-4 py-3 flex items-center justify-between">
                        <span class="text-gray-600">CA total</span>
                        <span class="font-semibold text-gray-900 tabular-nums">
                            {{ format_price($customer->orders->where('payment_status', 'paid')->sum('total')) }}
                        </span>
                    </div>
                    @if(isset($customer->loyalty_points) && $customer->loyalty_points > 0)
                    <div class="px-4 py-3 flex items-center justify-between">
                        <span class="text-gray-600">Points fidélité</span>
                        <span class="font-semibold text-orange-600 tabular-nums">{{ number_format($customer->loyalty_points) }} pts</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Changer statut --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Changer le statut</h2>
                </div>
                <div class="p-4">
                    <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status_quick" class="text-xs font-medium text-gray-700 block mb-1.5">Statut du compte</label>
                            <select
                                id="status_quick"
                                name="status"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            >
                                <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="blocked" {{ $customer->status === 'blocked' ? 'selected' : '' }}>Bloqué</option>
                            </select>
                            <p class="mt-1.5 text-[11px] text-gray-400">Un client bloqué ne peut plus passer commande.</p>
                        </div>
                        <button
                            type="submit"
                            class="w-full h-9 flex items-center justify-center bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition"
                        >
                            Mettre à jour
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
