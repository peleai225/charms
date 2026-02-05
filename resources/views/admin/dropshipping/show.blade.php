@extends('layouts.admin')

@section('title', 'Commande Fournisseur #' . $orderSupplier->id)
@section('page-title', 'Détails Commande Fournisseur')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Commande Fournisseur #{{ $orderSupplier->id }}</h2>
            <p class="text-slate-600">
                Commande client : 
                <a href="{{ route('admin.orders.show', $orderSupplier->order) }}" class="text-blue-600 hover:underline font-mono">
                    {{ $orderSupplier->order->order_number }}
                </a>
            </p>
            <p class="text-slate-600">Créée le {{ $orderSupplier->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.show', $orderSupplier->order) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Voir commande client
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations Fournisseur -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Fournisseur</h3>
                <div class="space-y-2">
                    <p class="font-medium text-slate-900">{{ $orderSupplier->supplier->name }}</p>
                    @if($orderSupplier->supplier->email)
                        <p class="text-sm text-slate-600">✉️ {{ $orderSupplier->supplier->email }}</p>
                    @endif
                    @if($orderSupplier->supplier->phone)
                        <p class="text-sm text-slate-600">📞 {{ $orderSupplier->supplier->phone }}</p>
                    @endif
                    @if($orderSupplier->supplier->address)
                        <p class="text-sm text-slate-600">{{ $orderSupplier->supplier->address }}</p>
                    @endif
                </div>
            </div>

            <!-- Articles de cette commande fournisseur -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900">Articles à expédier par ce fournisseur</h3>
                </div>
                <div class="divide-y divide-slate-200">
                    @php
                        // Filtrer les items de la commande qui sont en dropshipping et liés à ce fournisseur
                        $supplierItems = $orderSupplier->order->items->filter(function($item) use ($orderSupplier) {
                            $product = $item->product;
                            if (!$product || !$product->is_dropshipping) {
                                return false;
                            }
                            // Vérifier si le produit a ce fournisseur comme principal
                            $primarySupplier = $product->suppliers()->wherePivot('is_primary', true)->first();
                            return $primarySupplier && $primarySupplier->id === $orderSupplier->supplier_id;
                        });
                    @endphp
                    
                    @forelse($supplierItems as $item)
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
                    @empty
                        <div class="p-4 text-center text-slate-500">
                            Aucun article trouvé pour ce fournisseur
                        </div>
                    @endforelse
                </div>
                @if($orderSupplier->subtotal || $orderSupplier->total)
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 space-y-2">
                    @if($orderSupplier->subtotal)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Sous-total</span>
                        <span>{{ number_format($orderSupplier->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($orderSupplier->shipping_cost)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Frais de livraison</span>
                        <span>{{ number_format($orderSupplier->shipping_cost, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($orderSupplier->total)
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                        <span>Total</span>
                        <span>{{ number_format($orderSupplier->total, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Adresse de livraison client -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-3">Adresse de livraison client</h3>
                <div class="text-sm text-slate-600 space-y-1">
                    <p class="font-medium text-slate-900">{{ $orderSupplier->order->shipping_first_name }} {{ $orderSupplier->order->shipping_last_name }}</p>
                    <p>{{ $orderSupplier->order->shipping_address }}</p>
                    @if($orderSupplier->order->shipping_address_2)<p>{{ $orderSupplier->order->shipping_address_2 }}</p>@endif
                    <p>{{ $orderSupplier->order->shipping_postal_code }} {{ $orderSupplier->order->shipping_city }}</p>
                    <p>{{ $orderSupplier->order->shipping_country }}</p>
                    @if($orderSupplier->order->shipping_phone)<p class="pt-2">📞 {{ $orderSupplier->order->shipping_phone }}</p>@endif
                    @if($orderSupplier->order->shipping_email)<p>✉️ {{ $orderSupplier->order->shipping_email }}</p>@endif
                </div>
            </div>

            <!-- Notes -->
            @if($orderSupplier->notes || $orderSupplier->supplier_notes)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-3">Notes</h3>
                @if($orderSupplier->notes)
                <div class="mb-4">
                    <p class="text-xs text-slate-500 mb-1">Notes internes</p>
                    <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg whitespace-pre-line">{{ $orderSupplier->notes }}</p>
                </div>
                @endif
                @if($orderSupplier->supplier_notes)
                <div>
                    <p class="text-xs text-slate-500 mb-1">Notes fournisseur</p>
                    <p class="text-sm text-slate-600 bg-blue-50 p-3 rounded-lg whitespace-pre-line">{{ $orderSupplier->supplier_notes }}</p>
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
                
                <form method="POST" action="{{ route('admin.dropshipping.update-status', $orderSupplier) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="pending" {{ $orderSupplier->status === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ $orderSupplier->status === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                            <option value="processing" {{ $orderSupplier->status === 'processing' ? 'selected' : '' }}>En traitement</option>
                            <option value="shipped" {{ $orderSupplier->status === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                            <option value="delivered" {{ $orderSupplier->status === 'delivered' ? 'selected' : '' }}>Livrée</option>
                            <option value="cancelled" {{ $orderSupplier->status === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Transporteur</label>
                        <select name="shipping_carrier" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Sélectionner...</option>
                            <option value="chronopost" {{ $orderSupplier->shipping_carrier === 'chronopost' ? 'selected' : '' }}>Chronopost</option>
                            <option value="dhl" {{ $orderSupplier->shipping_carrier === 'dhl' ? 'selected' : '' }}>DHL</option>
                            <option value="fedex" {{ $orderSupplier->shipping_carrier === 'fedex' ? 'selected' : '' }}>FedEx</option>
                            <option value="colissimo" {{ $orderSupplier->shipping_carrier === 'colissimo' ? 'selected' : '' }}>Colissimo</option>
                            <option value="livraison_locale" {{ $orderSupplier->shipping_carrier === 'livraison_locale' ? 'selected' : '' }}>Livraison locale</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">N° de suivi</label>
                        <input type="text" name="tracking_number" value="{{ $orderSupplier->tracking_number }}"
                            placeholder="ABC123456789"
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">URL de suivi</label>
                        <input type="url" name="tracking_url" value="{{ $orderSupplier->tracking_url }}"
                            placeholder="https://..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" placeholder="Notes internes..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ $orderSupplier->notes }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                        Mettre à jour
                    </button>
                </form>

                @if($orderSupplier->tracking_number)
                <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                    <p class="text-xs text-purple-600 font-medium">Numéro de suivi</p>
                    <p class="font-mono text-purple-900">{{ $orderSupplier->tracking_number }}</p>
                    @if($orderSupplier->shipping_carrier)
                        <p class="text-xs text-purple-600 mt-1">via {{ ucfirst($orderSupplier->shipping_carrier) }}</p>
                    @endif
                    @if($orderSupplier->tracking_url)
                        <a href="{{ $orderSupplier->tracking_url }}" target="_blank" class="text-xs text-purple-600 hover:underline mt-1 block">
                            Suivre la livraison →
                        </a>
                    @endif
                </div>
                @endif
            </div>

            <!-- Historique -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Historique</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Créée</span>
                        <span>{{ $orderSupplier->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($orderSupplier->shipped_at)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Expédiée</span>
                        <span>{{ $orderSupplier->shipped_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($orderSupplier->delivered_at)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Livrée</span>
                        <span>{{ $orderSupplier->delivered_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations commande client -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Commande client</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Numéro</span>
                        <a href="{{ route('admin.orders.show', $orderSupplier->order) }}" class="font-mono text-blue-600 hover:underline">
                            {{ $orderSupplier->order->order_number }}
                        </a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Statut</span>
                        <span class="font-medium">{{ $orderSupplier->order->status_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Paiement</span>
                        <span class="font-medium">
                            @if($orderSupplier->order->payment_status === 'paid')
                                <span class="text-green-600">Payée</span>
                            @else
                                <span class="text-amber-600">{{ ucfirst($orderSupplier->order->payment_status) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total</span>
                        <span class="font-bold">{{ number_format($orderSupplier->order->total, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

