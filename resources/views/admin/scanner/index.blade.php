@extends('layouts.admin')

@section('title', 'Scanner / Caisse POS')
@section('page-title', 'Scanner / Mode Caisse')

@push('styles')
<style>
    .scanner-input:focus { 
        outline: none; 
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
    }
    .cart-item {
        transition: all 0.2s ease;
    }
    .cart-item:hover {
        background-color: #f8fafc;
    }
    .product-found {
        animation: pulse-green 0.5s ease;
    }
    @keyframes pulse-green {
        0%, 100% { background-color: transparent; }
        50% { background-color: #dcfce7; }
    }
    #camera-container video {
        width: 100%;
        max-width: 400px;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="h-[calc(100vh-120px)] flex gap-6" x-data="posScanner({{ ($receiptAutoPrint ?? false) ? 'true' : 'false' }})">
    
    <!-- Partie gauche: Scanner et produits -->
    <div class="flex-1 flex flex-col gap-4">
        
        <!-- Zone de scan -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Scanner un code-barres ou QR code
                    </label>
                    <input type="text" 
                           x-ref="scanInput"
                           @keypress.enter="scanCode($event.target.value); $event.target.value = ''"
                           placeholder="Scannez ou saisissez le code..."
                           class="scanner-input w-full px-4 py-4 text-xl font-mono border-2 border-slate-300 rounded-xl focus:border-blue-500"
                           autofocus>
                </div>
                <button @click="openCamera()" 
                        class="p-4 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors"
                        title="Utiliser la caméra">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
            
            <!-- Mode sélection -->
            <div class="flex gap-2">
                <button @click="mode = 'cart'" 
                        :class="mode === 'cart' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Ajout panier
                </button>
                <button @click="mode = 'stock_in'" 
                        :class="mode === 'stock_in' ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-700'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Entrée stock
                </button>
                <button @click="mode = 'stock_out'" 
                        :class="mode === 'stock_out' ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-700'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Sortie stock
                </button>
            </div>
        </div>

        <!-- Dernier produit scanné -->
        <div x-show="lastScanned" 
             x-transition
             class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center gap-4">
                <template x-if="lastScanned?.image">
                    <img :src="lastScanned.image" class="w-16 h-16 rounded-lg object-cover">
                </template>
                <template x-if="!lastScanned?.image">
                    <div class="w-16 h-16 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </template>
                <div class="flex-1">
                    <h3 class="font-semibold text-slate-900" x-text="lastScanned?.name"></h3>
                    <p class="text-sm text-slate-500">
                        SKU: <span x-text="lastScanned?.sku || 'N/A'"></span> | 
                        Stock: <span x-text="lastScanned?.stock"></span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-bold text-slate-900" x-text="lastScanned?.price_formatted"></p>
                    <span x-show="mode === 'cart'" 
                          class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                        Ajouté au panier
                    </span>
                    <span x-show="mode === 'stock_in'" 
                          class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                        +1 en stock
                    </span>
                    <span x-show="mode === 'stock_out'" 
                          class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                        -1 en stock
                    </span>
                </div>
            </div>
        </div>

        <!-- Message d'erreur -->
        <div x-show="error" 
             x-transition
             class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-red-700" x-text="error"></p>
            <button @click="error = null" class="ml-auto text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Historique des scans -->
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-200 p-4 overflow-hidden">
            <h3 class="font-semibold text-slate-900 mb-3">Historique des scans</h3>
            <div class="space-y-2 max-h-[300px] overflow-y-auto">
                <template x-for="(scan, index) in scanHistory" :key="index">
                    <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg text-sm">
                        <span class="text-slate-400 font-mono" x-text="scan.time"></span>
                        <span class="font-medium text-slate-700" x-text="scan.name"></span>
                        <span class="text-slate-500" x-text="scan.code"></span>
                        <span :class="{
                            'bg-green-100 text-green-700': scan.action === 'cart',
                            'bg-blue-100 text-blue-700': scan.action === 'stock_in',
                            'bg-red-100 text-red-700': scan.action === 'stock_out'
                        }" class="ml-auto px-2 py-0.5 rounded text-xs font-medium" x-text="scan.actionLabel"></span>
                    </div>
                </template>
                <p x-show="scanHistory.length === 0" class="text-slate-400 text-center py-4">
                    Aucun scan effectué
                </p>
            </div>
        </div>
    </div>

    <!-- Partie droite: Panier POS -->
    <div class="w-96 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200">
        
        <!-- En-tête panier -->
        <div class="p-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Panier</h2>
                <button @click="clearCart()" 
                        x-show="cart.items.length > 0"
                        class="text-sm text-red-600 hover:text-red-700">
                    Vider
                </button>
            </div>
            <p class="text-sm text-slate-500"><span x-text="cart.count"></span> article(s)</p>
        </div>

        <!-- Liste des articles -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-for="(item, key) in cart.items" :key="key">
                <div class="cart-item flex gap-3 p-3 bg-slate-50 rounded-xl">
                    <template x-if="item.image">
                        <img :src="item.image" class="w-12 h-12 rounded-lg object-cover">
                    </template>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-900 truncate" x-text="item.name"></p>
                        <p x-show="item.variant_name" class="text-xs text-slate-500" x-text="item.variant_name"></p>
                        <p class="text-sm text-slate-600" x-text="formatPrice(item.price)"></p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-1">
                            <button @click="updateQuantity(item.product_id + '-' + (item.variant_id || 0), item.quantity - 1)"
                                    class="w-6 h-6 flex items-center justify-center bg-slate-200 rounded hover:bg-slate-300">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <span class="w-8 text-center font-medium" x-text="item.quantity"></span>
                            <button @click="updateQuantity(item.product_id + '-' + (item.variant_id || 0), item.quantity + 1)"
                                    class="w-6 h-6 flex items-center justify-center bg-slate-200 rounded hover:bg-slate-300">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                        <p class="font-semibold text-slate-900" x-text="formatPrice(item.price * item.quantity)"></p>
                    </div>
                </div>
            </template>

            <div x-show="cart.items.length === 0" class="text-center py-12 text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>Scannez un produit pour l'ajouter</p>
            </div>
        </div>

        <!-- Totaux et paiement -->
        <div class="p-4 border-t border-slate-200 space-y-4">
            <div class="flex justify-between text-2xl font-bold">
                <span>Total</span>
                <span x-text="cart.total_formatted"></span>
            </div>

            <!-- Montant reçu (pour espèces) -->
            <div x-show="paymentMethod === 'cash'">
                <label class="block text-sm font-medium text-slate-700 mb-1">Montant reçu</label>
                <input type="number" 
                       x-model.number="amountReceived"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                       placeholder="0">
                <p x-show="amountReceived > 0" class="mt-1 text-lg font-semibold" 
                   :class="amountReceived >= cart.total ? 'text-green-600' : 'text-red-600'">
                    Rendu: <span x-text="formatPrice(Math.max(0, amountReceived - cart.total))"></span>
                </p>
            </div>

            <!-- Méthodes de paiement -->
            <div class="grid grid-cols-3 gap-2">
                <button @click="paymentMethod = 'cash'" 
                        :class="paymentMethod === 'cash' ? 'ring-2 ring-blue-500' : ''"
                        class="p-3 bg-slate-100 rounded-lg text-center hover:bg-slate-200">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-xs font-medium">Espèces</span>
                </button>
                <button @click="paymentMethod = 'card'" 
                        :class="paymentMethod === 'card' ? 'ring-2 ring-blue-500' : ''"
                        class="p-3 bg-slate-100 rounded-lg text-center hover:bg-slate-200">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span class="text-xs font-medium">Carte</span>
                </button>
                <button @click="paymentMethod = 'mobile_money'" 
                        :class="paymentMethod === 'mobile_money' ? 'ring-2 ring-blue-500' : ''"
                        class="p-3 bg-slate-100 rounded-lg text-center hover:bg-slate-200">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs font-medium">Mobile</span>
                </button>
            </div>

            <button @click="processCheckout()"
                    :disabled="cart.items.length === 0 || isProcessing"
                    class="w-full py-4 bg-green-600 text-white font-bold text-lg rounded-xl hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <span x-show="!isProcessing">Valider la vente</span>
                <span x-show="isProcessing" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Traitement...
                </span>
            </button>
        </div>
    </div>

    <!-- Modal Caméra -->
    <div x-show="showCamera" 
         x-transition
         class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center"
         @click.self="closeCamera()">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Scanner avec la caméra</h3>
                <button @click="closeCamera()" class="p-2 hover:bg-slate-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="camera-container" class="flex justify-center">
                <video id="camera-preview" autoplay playsinline></video>
            </div>
            <p class="text-center text-sm text-slate-500 mt-4">
                Placez le code-barres devant la caméra
            </p>
        </div>
    </div>

    <!-- Modal Succès -->
    <div x-show="showSuccess" 
         x-transition
         class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2">Vente validée !</h3>
            <p class="text-slate-600 mb-2">Commande <span class="font-mono font-semibold" x-text="lastOrder?.order_number"></span></p>
            <p class="text-3xl font-bold text-green-600 mb-4" x-text="lastOrder?.total_formatted"></p>
            <p x-show="lastOrder?.change > 0" class="text-lg text-slate-700 mb-4">
                Rendu monnaie: <span class="font-bold" x-text="lastOrder?.change_formatted"></span>
            </p>
            <p x-show="lastOrder?.receipt_url && receiptAutoPrint" class="text-sm text-slate-500 mb-6">
                Le reçu s'est ouvert. Appuyez sur <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-xs">Entrée</kbd> pour imprimer.
            </p>
            <div class="flex gap-3 justify-center flex-wrap">
                <button @click="showSuccess = false; $refs.scanInput.focus()" 
                        class="px-8 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700">
                    Nouvelle vente
                </button>
                <button x-show="lastOrder?.receipt_url"
                        @click="window.open(lastOrder.receipt_url, 'receipt', 'width=400,height=600')"
                        class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer le reçu
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/@zxing/library@0.19.1/umd/index.min.js"></script>
<script>
function posScanner(receiptAutoPrint = false) {
    return {
        mode: 'cart', // cart, stock_in, stock_out
        receiptAutoPrint: receiptAutoPrint,
        cart: { items: [], count: 0, total: 0, total_formatted: '0 F CFA' },
        lastScanned: null,
        scanHistory: [],
        error: null,
        showCamera: false,
        showSuccess: false,
        lastOrder: null,
        paymentMethod: 'cash',
        amountReceived: 0,
        isProcessing: false,
        codeReader: null,

        init() {
            this.loadCart();
            // Focus sur le champ de scan
            this.$nextTick(() => {
                this.$refs.scanInput?.focus();
            });
        },

        async loadCart() {
            try {
                const response = await fetch('{{ route("admin.scanner.cart") }}');
                this.cart = await response.json();
            } catch (e) {
                console.error(e);
            }
        },

        async scanCode(code) {
            if (!code.trim()) return;
            this.error = null;

            try {
                const response = await fetch('{{ route("admin.scanner.scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ code: code.trim() })
                });

                const data = await response.json();

                if (!data.found) {
                    this.error = data.message || 'Produit non trouvé';
                    this.playSound('error');
                    return;
                }

                this.lastScanned = data.data;
                this.playSound('success');

                // Ajouter à l'historique
                const now = new Date();
                this.scanHistory.unshift({
                    time: now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                    name: data.data.name,
                    code: code,
                    action: this.mode,
                    actionLabel: this.mode === 'cart' ? 'Panier' : this.mode === 'stock_in' ? 'Entrée' : 'Sortie'
                });
                if (this.scanHistory.length > 20) this.scanHistory.pop();

                // Action selon le mode
                if (this.mode === 'cart') {
                    await this.addToCart(data.data);
                } else {
                    await this.processStockMovement(data.data);
                }

            } catch (e) {
                console.error(e);
                this.error = 'Erreur de connexion';
            }
        },

        async addToCart(product) {
            try {
                const response = await fetch('{{ route("admin.scanner.cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: product.product_id || product.id,
                        variant_id: product.variant_id || null,
                        quantity: 1
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.cart = data.cart;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async processStockMovement(product) {
            try {
                const response = await fetch('{{ route("admin.scanner.stock-movement") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: product.product_id || product.id,
                        variant_id: product.variant_id || null,
                        type: this.mode === 'stock_in' ? 'in' : 'out',
                        quantity: 1
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.lastScanned.stock = data.new_stock;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async updateQuantity(key, quantity) {
            if (quantity <= 0) {
                await this.removeItem(key);
                return;
            }

            try {
                const response = await fetch(`/admin/scanner/cart/${key}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity })
                });

                const data = await response.json();
                if (data.success) {
                    this.cart = data.cart;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async removeItem(key) {
            try {
                const response = await fetch(`/admin/scanner/cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.cart = data.cart;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async clearCart() {
            try {
                const response = await fetch('{{ route("admin.scanner.cart.clear") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.cart = data.cart;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async processCheckout() {
            if (this.cart.items.length === 0 || this.isProcessing) return;

            this.isProcessing = true;

            try {
                const response = await fetch('{{ route("admin.scanner.checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        payment_method: this.paymentMethod,
                        amount_received: this.amountReceived
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.lastOrder = data.order;
                    this.lastOrder.change = data.change;
                    this.lastOrder.change_formatted = data.change_formatted;
                    this.lastOrder.receipt_url = data.receipt_url;
                    this.cart = { items: [], count: 0, total: 0, total_formatted: '0 F CFA' };
                    this.amountReceived = 0;
                    this.showSuccess = true;
                    this.playSound('success');
                    // Impression automatique du reçu si configuré
                    if (this.receiptAutoPrint && data.receipt_url) {
                        const receiptWin = window.open(data.receipt_url, 'receipt', 'width=400,height=600');
                        if (receiptWin) receiptWin.focus();
                    }
                } else {
                    this.error = data.message;
                }
            } catch (e) {
                console.error(e);
                this.error = 'Erreur lors de la validation';
            } finally {
                this.isProcessing = false;
            }
        },

        formatPrice(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' F CFA';
        },

        playSound(type) {
            // Sons de feedback (optionnel)
            try {
                const audio = new Audio();
                if (type === 'success') {
                    audio.src = 'data:audio/wav;base64,UklGRl9vT19teleAQWRaYXRh';
                } else {
                    audio.src = 'data:audio/wav;base64,UklGRl9vT19teleAQWRaYXRh';
                }
                audio.volume = 0.3;
                audio.play().catch(() => {});
            } catch (e) {}
        },

        async openCamera() {
            this.showCamera = true;
            
            await this.$nextTick();
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                
                const video = document.getElementById('camera-preview');
                video.srcObject = stream;
                
                // Initialiser le lecteur de codes-barres
                this.codeReader = new ZXing.BrowserMultiFormatReader();
                this.codeReader.decodeFromVideoDevice(null, 'camera-preview', (result, error) => {
                    if (result) {
                        this.scanCode(result.getText());
                        this.closeCamera();
                    }
                });
            } catch (e) {
                console.error('Camera error:', e);
                this.error = 'Impossible d\'accéder à la caméra';
                this.showCamera = false;
            }
        },

        closeCamera() {
            this.showCamera = false;
            
            const video = document.getElementById('camera-preview');
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
            
            if (this.codeReader) {
                this.codeReader.reset();
                this.codeReader = null;
            }

            this.$refs.scanInput?.focus();
        }
    }
}
</script>
@endsection

