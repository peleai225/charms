@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Commande {{ $order->order_number }}</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.invoice.view', $order) }}" target="_blank" class="h-9 px-4 flex items-center gap-2 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Voir facture
            </a>
            <a href="{{ route('admin.orders.invoice', $order) }}" class="h-9 px-4 flex items-center gap-2 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Télécharger PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left column: Articles + Totaux --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Articles --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Articles ({{ $order->items->count() }})</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                    <div class="p-4 flex items-start gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->product && $item->product->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $item->product->images->first()->path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                            @if($item->variant_label)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $item->variant_label }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">Quantité : {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ number_format($item->price, 0, ',', ' ') }} F / unité</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Totaux --}}
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 space-y-2">
                    <div class="flex justify-between text-[13px] text-gray-600">
                        <span>Sous-total</span>
                        <span class="font-medium">{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-[13px] text-green-600">
                        <span>Réduction @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
                        <span class="font-medium">-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($order->shipping_amount > 0)
                    <div class="flex justify-between text-[13px] text-gray-600">
                        <span>Livraison</span>
                        <span class="font-medium">{{ number_format($order->shipping_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($order->tax_amount > 0)
                    <div class="flex justify-between text-[13px] text-gray-600">
                        <span>TVA</span>
                        <span class="font-medium">{{ number_format($order->tax_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="text-sm font-semibold text-gray-900">Total</span>
                        <span class="text-lg font-bold text-gray-900 tabular-nums">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>

            {{-- Notes admin --}}
            @if($order->admin_notes)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Notes internes</h2>
                <div class="text-[13px] text-gray-600 whitespace-pre-wrap">{{ $order->admin_notes }}</div>
            </div>
            @endif

        </div>

        {{-- Right column: Infos client + Statut --}}
        <div class="space-y-5">

            {{-- Statut commande --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Statut de la commande</h2>

                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-1.5 block">Statut</label>
                        <select name="status" class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En cours</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Livrée</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>

                    @if($order->status === 'shipped' || $order->status === 'delivery_in_progress' || $order->status === 'delivered')
                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-1.5 block">N° de suivi</label>
                        <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Ex: 1Z999AA10123456784">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-1.5 block">Transporteur</label>
                        <input type="text" name="shipping_carrier" value="{{ $order->shipping_carrier }}" class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Ex: DHL, Fedex">
                    </div>
                    @endif

                    <button type="submit" class="w-full h-9 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition">
                        Mettre à jour
                    </button>
                </form>

                {{-- Dates importantes --}}
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Créée</span>
                        <span class="text-gray-900 font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($order->shipped_at)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Expédiée</span>
                        <span class="text-gray-900 font-medium">{{ $order->shipped_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($order->delivered_at)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Livrée</span>
                        <span class="text-gray-900 font-medium">{{ $order->delivered_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Paiement --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Paiement</h2>
                <div class="space-y-2">
                    <div class="flex justify-between text-[13px]">
                        <span class="text-gray-500">Statut</span>
                        @php
                            $paymentColors = [
                                'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'paid' => 'bg-green-50 text-green-700 border-green-200',
                                'failed' => 'bg-red-50 text-red-700 border-red-200',
                                'refunded' => 'bg-orange-50 text-orange-700 border-orange-200',
                            ];
                            $pColor = $paymentColors[$order->payment_status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $pColor }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </div>
                    <div class="flex justify-between text-[13px]">
                        <span class="text-gray-500">Méthode</span>
                        <span class="text-gray-900 font-medium">{{ $order->payment_method_label }}</span>
                    </div>
                    @if($order->paid_at)
                    <div class="flex justify-between text-[13px]">
                        <span class="text-gray-500">Payée le</span>
                        <span class="text-gray-900 font-medium">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Informations client --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Client</h2>
                <div class="space-y-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                        @if($order->billing_email)
                        <a href="mailto:{{ $order->billing_email }}" class="text-xs text-blue-600 hover:text-blue-700 transition">{{ $order->billing_email }}</a>
                        @endif
                        @if($order->billing_phone)
                        <p class="text-xs text-gray-600 mt-0.5">{{ $order->billing_phone }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Adresse facturation --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Adresse de facturation</h2>
                <div class="text-[13px] text-gray-600 space-y-0.5">
                    <p>{{ $order->billing_address }}</p>
                    @if($order->billing_address_2)
                        <p>{{ $order->billing_address_2 }}</p>
                    @endif
                    <p>{{ $order->billing_postal_code }} {{ $order->billing_city }}</p>
                    <p>{{ $order->billing_country }}</p>
                </div>
            </div>

            {{-- Adresse livraison --}}
            @if($order->shipping_address)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Adresse de livraison</h2>
                <div class="text-[13px] text-gray-600 space-y-0.5">
                    <p class="font-medium text-gray-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                    @if($order->shipping_phone)
                        <p>{{ $order->shipping_phone }}</p>
                    @endif
                    <p>{{ $order->shipping_address }}</p>
                    @if($order->shipping_address_2)
                        <p>{{ $order->shipping_address_2 }}</p>
                    @endif
                    <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                    <p>{{ $order->shipping_country }}</p>
                </div>
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
