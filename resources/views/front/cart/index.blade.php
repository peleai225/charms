@extends('layouts.front')

@section('title', 'Panier')

@section('content')
<!-- Hero-style header -->
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-10 mb-8 relative overflow-hidden">
    <div class="absolute -top-16 -right-16 w-64 h-64 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-4 relative">
        <nav class="text-sm text-slate-400 mb-4 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white font-medium">Panier</span>
        </nav>
        <h1 class="text-3xl font-extrabold">Mon panier</h1>
    </div>
</div>

<div class="container mx-auto px-4 pb-8 md:pb-10">
    @if($cart->items->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Articles -->
        <div class="lg:col-span-2 space-y-4">
            @foreach($cart->items as $item)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100/80 p-5 flex gap-5 transition-all duration-300 hover:shadow-md hover:border-slate-200"
                 x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})"
                 :class="{ 'opacity-50': isUpdating, 'translate-x-full opacity-0': isRemoving }">
                <!-- Image -->
                <a href="{{ route('shop.product', $item->product->slug) }}" class="flex-shrink-0">
                    <div class="w-28 h-28 rounded-xl overflow-hidden bg-slate-50 ring-1 ring-slate-100">
                        @if($item->variant?->image)
                            <img src="{{ asset('storage/' . $item->variant->image) }}" alt="" class="w-full h-full object-cover">
                        @elseif($item->product->images->where('is_primary', true)->first())
                            <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </a>

                <!-- Infos -->
                <div class="flex-1 min-w-0">
                    <a href="{{ route('shop.product', $item->product->slug) }}" class="font-medium text-gray-900 hover:text-primary-600">
                        {{ $item->product->name }}
                    </a>

                    <!-- Variante -->
                    @if($item->variant)
                        <div class="flex items-center gap-2 mt-1">
                            @foreach($item->variant->attributeValues as $attrValue)
                                @if($attrValue->color_code)
                                    <span class="w-4 h-4 rounded-full border border-gray-200" style="background-color: {{ $attrValue->color_code }}"></span>
                                @endif
                                <span class="text-sm text-gray-500">{{ $attrValue->value }}</span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Prix unitaire -->
                    <p class="text-sm text-gray-500 mt-1">{{ format_price($item->unit_price) }} / unité</p>
                </div>

                <!-- Quantité avec mise à jour AJAX -->
                <div class="flex items-center">
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button type="button" 
                                @click="decrementQuantity()" 
                                :disabled="isUpdating || quantity <= 1"
                                class="px-3 py-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <input type="number" 
                               x-model.number="quantity" 
                               @change="updateQuantity()"
                               min="1" 
                               max="99" 
                               :disabled="isUpdating"
                               class="w-12 text-center border-0 focus:ring-0 text-sm disabled:bg-gray-50">
                        <button type="button" 
                                @click="incrementQuantity()" 
                                :disabled="isUpdating || quantity >= 99"
                                class="px-3 py-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Total ligne -->
                <div class="text-right">
                    <p class="font-semibold text-gray-900" x-text="formatPrice(lineTotal)"></p>
                    
                    <!-- Supprimer avec animation -->
                    <button type="button" 
                            @click="removeItem()"
                            :disabled="isRemoving"
                            class="mt-2 text-sm text-red-600 hover:text-red-700 disabled:opacity-50">
                        <span x-show="!isRemoving">Supprimer</span>
                        <span x-show="isRemoving" class="flex items-center gap-1">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Suppression...
                        </span>
                    </button>
                </div>
            </div>
            @endforeach

            <!-- Vider le panier -->
            <div class="flex justify-between items-center pt-4">
                <a href="{{ route('shop.index') }}" class="text-primary-600 hover:text-primary-700 font-medium inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Continuer mes achats
                </a>

                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-500 hover:text-red-600 text-sm">
                        Vider le panier
                    </button>
                </form>
            </div>
        </div>

        <!-- Récapitulatif -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-500 to-primary-600"></div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Récapitulatif</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sous-total (<span id="cart-summary-count">{{ $cart->items_count }} articles</span>)</span>
                        <span class="font-medium" id="cart-summary-subtotal">{{ format_price($cart->subtotal) }}</span>
                    </div>

                    @if($cart->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Réduction</span>
                        <span>-{{ format_price($cart->discount_amount) }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-gray-600">Livraison</span>
                        <span class="font-medium">Calculée à l'étape suivante</span>
                    </div>
                </div>

                <!-- Code promo -->
                <div class="mt-4 pt-4 border-t border-gray-200" 
                     x-data="couponForm()">
                    @if($cart->coupon_code)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <span class="text-sm font-medium text-green-700">{{ $cart->coupon_code }}</span>
                                <span class="text-xs text-green-600 block">Code promo appliqué</span>
                            </div>
                            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-green-600 hover:text-green-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.coupon.apply') }}" 
                              @submit.prevent="applyCoupon($event)"
                              class="flex gap-2">
                            @csrf
                            <input type="text" 
                                   x-model="couponCode"
                                   name="coupon_code" 
                                   placeholder="Code promo"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <button type="submit" 
                                    :disabled="isApplyingCoupon"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 font-medium rounded-lg text-sm transition-colors flex items-center gap-2 min-w-[100px] justify-center">
                                <span x-show="isApplyingCoupon" class="animate-spin">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                                <span x-text="isApplyingCoupon ? 'Application...' : 'Appliquer'">Appliquer</span>
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Total -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span id="cart-summary-total">{{ format_price($cart->total) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Taxes incluses</p>
                </div>

                <!-- Bouton commander -->
                <a href="{{ route('checkout.index') }}" class="mt-6 w-full block py-3 px-6 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl text-center shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 transition-all">
                    Passer commande
                </a>

                <!-- Réassurance -->
                <div class="mt-6 space-y-2 text-xs text-gray-500">
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Paiement 100% sécurisé
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Livraison gratuite dès 50 000 F CFA
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Retours gratuits sous 30 jours
                    </p>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Panier vide -->
    <div class="text-center py-20">
        <div class="w-32 h-32 mx-auto mb-8 rounded-full bg-slate-100 flex items-center justify-center">
            <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Votre panier est vide</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">Découvrez nos produits et ajoutez-les à votre panier</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            Découvrir la boutique
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function couponForm() {
    return {
        couponCode: '',
        isApplyingCoupon: false,
        async applyCoupon(event) {
            event.preventDefault();
            
            if (this.isApplyingCoupon || !this.couponCode.trim()) {
                if (!this.couponCode.trim()) {
                    alert('Veuillez saisir un code promo');
                }
                return;
            }
            
            this.isApplyingCoupon = true;
            
            try {
                const formData = new FormData();
                formData.append('coupon_code', this.couponCode.trim());
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route('cart.coupon.apply') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Recharger la page pour afficher le coupon et mettre à jour les totaux
                    window.location.reload();
                } else {
                    const errorMessage = data.error || data.message || 'Erreur lors de l\'application du code promo';
                    alert(errorMessage);
                    this.isApplyingCoupon = false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'application du code promo. Veuillez réessayer.');
                this.isApplyingCoupon = false;
            }
        }
    };
}

function cartItem(itemId, initialQuantity, unitPrice) {
    return {
        itemId: itemId,
        quantity: initialQuantity,
        unitPrice: unitPrice,
        isUpdating: false,
        isRemoving: false,
        updateTimeout: null,

        get lineTotal() {
            return this.quantity * this.unitPrice;
        },

        formatPrice(amount) {
            return new Intl.NumberFormat('fr-FR', { 
                style: 'decimal',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount) + ' F CFA';
        },

        incrementQuantity() {
            if (this.quantity < 99) {
                this.quantity++;
                this.debouncedUpdate();
            }
        },

        decrementQuantity() {
            if (this.quantity > 1) {
                this.quantity--;
                this.debouncedUpdate();
            }
        },

        debouncedUpdate() {
            clearTimeout(this.updateTimeout);
            this.updateTimeout = setTimeout(() => this.updateQuantity(), 500);
        },

        async updateQuantity() {
            if (this.isUpdating) return;
            this.isUpdating = true;

            try {
                const response = await fetch(`/api/cart/items/${this.itemId}`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ quantity: this.quantity })
                });

                if (response.ok) {
                    const data = await response.json();
                    // Update global cart store
                    if (Alpine.store('cart')) {
                        Alpine.store('cart').count = data.items_count;
                        Alpine.store('cart').sync();
                    }
                    // Update summary section
                    this.updateSummary();
                }
            } catch (error) {
                console.error('Update error:', error);
            } finally {
                this.isUpdating = false;
            }
        },

        async removeItem() {
            if (this.isRemoving) return;
            this.isRemoving = true;

            try {
                const response = await fetch(`/api/cart/items/${this.itemId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    // Update global cart store
                    if (Alpine.store('cart')) {
                        Alpine.store('cart').count = data.items_count;
                    }
                    
                    // Animate out and remove element
                    await new Promise(resolve => setTimeout(resolve, 300));
                    this.$el.remove();
                    
                    // Refresh page if cart is empty
                    if (data.items_count === 0) {
                        window.location.reload();
                    } else {
                        this.updateSummary();
                    }
                }
            } catch (error) {
                console.error('Remove error:', error);
                this.isRemoving = false;
            }
        },

        async updateSummary() {
            // Fetch updated cart data and update summary
            if (Alpine.store('cart')) {
                await Alpine.store('cart').sync();
                
                // Update summary display
                const summarySubtotal = document.getElementById('cart-summary-subtotal');
                const summaryTotal = document.getElementById('cart-summary-total');
                const summaryCount = document.getElementById('cart-summary-count');
                
                if (summarySubtotal && Alpine.store('cart').subtotal !== undefined) {
                    summarySubtotal.textContent = this.formatPrice(Alpine.store('cart').subtotal);
                }
                if (summaryTotal && Alpine.store('cart').total !== undefined) {
                    summaryTotal.textContent = this.formatPrice(Alpine.store('cart').total);
                }
                if (summaryCount && Alpine.store('cart').count !== undefined) {
                    summaryCount.textContent = `${Alpine.store('cart').count} articles`;
                }
            } else {
                // Fallback: fetch from API
                try {
                    const response = await fetch('/api/cart');
                    if (response.ok) {
                        const data = await response.json();
                        const summarySubtotal = document.getElementById('cart-summary-subtotal');
                        const summaryTotal = document.getElementById('cart-summary-total');
                        const summaryCount = document.getElementById('cart-summary-count');
                        
                        if (summarySubtotal) {
                            summarySubtotal.textContent = this.formatPrice(data.subtotal);
                        }
                        if (summaryTotal) {
                            summaryTotal.textContent = this.formatPrice(data.total);
                        }
                        if (summaryCount) {
                            summaryCount.textContent = `${data.items_count} articles`;
                        }
                    }
                } catch (error) {
                    console.error('Error updating summary:', error);
                }
            }
        }
    };
}
</script>
@endpush
@endsection

