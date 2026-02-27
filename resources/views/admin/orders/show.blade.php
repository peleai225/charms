@extends('layouts.admin')

@section('title', 'Commande ' . $order->order_number)
@section('page-title', 'Détails de la commande')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">{{ $order->order_number }}</h2>
            <p class="text-slate-600">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.invoice.view', $order) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Voir facture
            </a>
            <a href="{{ route('admin.orders.invoice', $order) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Télécharger PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Articles -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900">Articles commandés</h3>
                </div>
                <div class="divide-y divide-slate-200">
                    @foreach($order->items as $item)
                    <div class="p-4 flex gap-4">
                        <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->product?->images->where('is_primary', true)->first())
                                <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                            @if($item->variant_name)
                                <p class="text-sm text-slate-500">{{ $item->variant_name }}</p>
                            @endif
                            <p class="text-sm text-slate-500">SKU: {{ $item->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">{{ number_format($item->unit_price, 0, ',', ' ') }} F × {{ $item->quantity }}</p>
                            <p class="text-lg font-bold text-slate-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Sous-total</span>
                        <span>{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Réduction @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
                        <span>-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Livraison</span>
                        <span>{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite' }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                        <span>Total</span>
                        <span>{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                    </div>
                </div>
            </div>

            <!-- Adresses -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900 mb-3">Adresse de livraison</h3>
                    <div class="text-sm text-slate-600 space-y-1">
                        <p class="font-medium text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                        <p>{{ $order->shipping_address }}</p>
                        @if($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
                        <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                        <p>{{ $order->shipping_country }}</p>
                        @if($order->shipping_phone)<p class="pt-2">📞 {{ $order->shipping_phone }}</p>@endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900 mb-3">Adresse de facturation</h3>
                    <div class="text-sm text-slate-600 space-y-1">
                        <p class="font-medium text-slate-900">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                        <p>{{ $order->billing_address }}</p>
                        @if($order->billing_address_2)<p>{{ $order->billing_address_2 }}</p>@endif
                        <p>{{ $order->billing_postal_code }} {{ $order->billing_city }}</p>
                        <p>{{ $order->billing_country }}</p>
                        <p class="pt-2">✉️ {{ $order->billing_email }}</p>
                        @if($order->billing_phone)<p>📞 {{ $order->billing_phone }}</p>@endif
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes || $order->admin_notes)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-3">Notes</h3>
                @if($order->notes)
                <div class="mb-4">
                    <p class="text-xs text-slate-500 mb-1">Note du client</p>
                    <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg">{{ $order->notes }}</p>
                </div>
                @endif
                @if($order->admin_notes)
                <div>
                    <p class="text-xs text-slate-500 mb-1">Notes internes</p>
                    <p class="text-sm text-slate-600 bg-amber-50 p-3 rounded-lg whitespace-pre-line">{{ $order->admin_notes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Colonne latérale -->
        <div class="space-y-6">
            <!-- Statut -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Statut de la commande</h3>
                
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>En préparation</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Livrée</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Transporteur</label>
                        <select name="shipping_carrier" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Sélectionner...</option>
                            <option value="chronopost" {{ $order->shipping_carrier === 'chronopost' ? 'selected' : '' }}>Chronopost</option>
                            <option value="dhl" {{ $order->shipping_carrier === 'dhl' ? 'selected' : '' }}>DHL</option>
                            <option value="fedex" {{ $order->shipping_carrier === 'fedex' ? 'selected' : '' }}>FedEx</option>
                            <option value="colissimo" {{ $order->shipping_carrier === 'colissimo' ? 'selected' : '' }}>Colissimo</option>
                            <option value="livraison_locale" {{ $order->shipping_carrier === 'livraison_locale' ? 'selected' : '' }}>Livraison locale</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">N° de suivi</label>
                        <input type="text" name="tracking_number" value="{{ $order->tracking_number }}"
                            placeholder="ABC123456789"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                        Mettre à jour
                    </button>
                </form>

                @if($order->tracking_number)
                <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                    <p class="text-xs text-purple-600 font-medium">Numéro de suivi</p>
                    <p class="font-mono text-purple-900">{{ $order->tracking_number }}</p>
                    @if($order->shipping_carrier)
                        <p class="text-xs text-purple-600 mt-1">via {{ ucfirst($order->shipping_carrier) }}</p>
                    @endif
                </div>
                @endif
            </div>

            <!-- Paiement -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Paiement</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Méthode</span>
                        <span class="font-medium">
                            @if($order->payment_method === 'cinetpay')
                                CinetPay
                            @elseif($order->payment_method === 'cod')
                                À la livraison
                            @else
                                {{ $order->payment_method ?? 'N/A' }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Statut</span>
                        <span>
                            @if($order->payment_status === 'refunded')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Remboursée</span>
                            @elseif($order->payment_status === 'paid')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Payée</span>
                            @elseif($order->payment_status === 'cod')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">À la livraison</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">En attente</span>
                            @endif
                        </span>
                    </div>
                    @if($order->paid_at)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Payée le</span>
                        <span class="font-medium">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @php $totalRefunded = $order->refunds()->sum('amount'); @endphp
                    @if($totalRefunded > 0)
                    <div class="flex justify-between text-amber-600">
                        <span>Remboursé</span>
                        <span class="font-medium">-{{ number_format($totalRefunded, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Remboursement -->
            @if($order->is_refundable)
            @php $maxRefundable = max(0, (float)$order->total - $order->refunds()->sum('amount')); @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Créer un remboursement</h3>
                <form method="POST" action="{{ route('admin.refunds.store', $order) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Montant (max {{ number_format($maxRefundable, 0, ',', ' ') }} F)</label>
                        <input type="number" name="amount" step="1" min="1" max="{{ (int)$maxRefundable }}" value="{{ (int)$maxRefundable }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Motif</label>
                        <select name="reason" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="customer_request">Demande client</option>
                            <option value="product_defective">Produit défectueux</option>
                            <option value="wrong_item">Mauvais article</option>
                            <option value="not_delivered">Non livré</option>
                            <option value="duplicate">Doublon</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optionnel)</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Détails du remboursement..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-xl transition-colors">
                        Créer le remboursement
                    </button>
                </form>
            </div>
            @endif

            @if($order->refunds->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Remboursements</h3>
                <ul class="space-y-2">
                    @foreach($order->refunds as $r)
                    <li class="flex justify-between items-center text-sm">
                        <span class="font-mono">{{ $r->refund_number }}</span>
                        <span class="font-medium">{{ number_format($r->amount, 0, ',', ' ') }} F</span>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.refunds.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:text-blue-700">Voir tous les remboursements</a>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Actions</h3>
                
                <div class="space-y-2">
                    <form method="POST" action="{{ route('admin.orders.resend', $order) }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors text-sm">
                            📧 Renvoyer email de confirmation
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.note', $order) }}" class="mt-4">
                        @csrf
                        <textarea name="note" rows="2" placeholder="Ajouter une note interne..."
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                        <button type="submit" class="mt-2 w-full py-2 bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium rounded-lg text-sm transition-colors">
                            Ajouter la note
                        </button>
                    </form>
                </div>
            </div>

            <!-- Dates -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Historique</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Créée</span>
                        <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($order->paid_at)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Payée</span>
                        <span>{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($order->shipped_at)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Expédiée</span>
                        <span>{{ $order->shipped_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($order->delivered_at)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Livrée</span>
                        <span>{{ $order->delivered_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

