@extends('layouts.admin')

@section('title', 'Caisse POS')
@section('page-title', 'Caisse POS')

@push('styles')
<style>
    /* ── Layout ──────────────────────────────────────────── */
    .pos-layout {
        height: calc(100vh - 112px);
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 20px;
    }
    @media (max-width: 1024px) {
        .pos-layout { grid-template-columns: 1fr; height: auto; }
    }

    /* ── Scan input ──────────────────────────────────────── */
    .scan-input {
        font-size: 1.2rem;
        letter-spacing: 0.04em;
        transition: box-shadow .15s, border-color .15s;
    }
    .scan-input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249,115,22,.18);
    }
    .scan-wrap.flash-ok  { animation: flash-ok  .45s ease; }
    .scan-wrap.flash-err { animation: flash-err .45s ease; }
    @keyframes flash-ok  { 0%,100%{ background:#fff; } 40%{ background:#dcfce7; } }
    @keyframes flash-err { 0%,100%{ background:#fff; } 40%{ background:#fee2e2; } }

    /* ── Cart items ───────────────────────────────────────── */
    .cart-item { transition: background .12s; }
    .cart-item:hover { background: #fafafa; }
    .qty-btn {
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 7px;
        background: #f1f5f9;
        cursor: pointer;
        transition: background .12s;
        border: none;
    }
    .qty-btn:hover { background: #e2e8f0; }

    /* ── Payment toggle ───────────────────────────────────── */
    .pay-btn {
        flex: 1; padding: .55rem .4rem;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color .12s, background .12s;
        display: flex; flex-direction: column; align-items: center; gap: 3px;
        font-size: .72rem; font-weight: 600; color: #64748b;
    }
    .pay-btn.active { border-color: #f97316; background: #fff7ed; color: #c2410c; }
    .pay-btn:hover:not(.active) { border-color: #fdba74; }

    /* ── Checkout btn ─────────────────────────────────────── */
    .checkout-btn {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        transition: opacity .15s, transform .1s;
    }
    .checkout-btn:hover:not(:disabled) { opacity: .92; transform: translateY(-1px); }
    .checkout-btn:disabled { opacity: .4; cursor: not-allowed; transform: none; }

    /* ── Search results ───────────────────────────────────── */
    .search-result-item:hover { background: #fff7ed; }

    /* ── Modal ────────────────────────────────────────────── */
    .modal-bg { background: rgba(15,23,42,.55); backdrop-filter: blur(3px); }

    /* ── Scrollbar ────────────────────────────────────────── */
    .thin-scroll::-webkit-scrollbar { width: 3px; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
</style>
@endpush

@section('content')
<div class="pos-layout"
     x-data="posScanner()"
     x-init="init()">

    {{-- ══════════════════════════════════════════════════════
         GAUCHE — Scanner + Recherche + Feedback
    ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 min-h-0 overflow-y-auto thin-scroll pr-0.5">

        {{-- Bloc scan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-[12px] text-gray-500 mb-3">Champ actif en permanence — scanneur HID ou saisie manuelle</p>

            {{-- Input --}}
            <div class="scan-wrap rounded-xl overflow-hidden"
                 :class="{ 'flash-ok': scanFlashOk, 'flash-err': scanFlashErr }">
                <div class="relative">
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <input type="text"
                           x-ref="scanInput"
                           @keydown.enter.prevent="handleScanEnter($event)"
                           placeholder="Scanner un code-barres ou taper un SKU..."
                           autocomplete="off" spellcheck="false"
                           class="scan-input w-full pl-12 pr-28 py-4 border-2 border-slate-200 rounded-xl font-mono text-slate-900 bg-white">
                    <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 rounded text-xs text-slate-400 font-mono hidden sm:block">↵</kbd>
                        <button @click="openCamera()"
                                title="Caméra"
                                class="p-1.5 bg-violet-100 hover:bg-violet-200 text-violet-600 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modes --}}
            <div class="flex gap-2 mt-3">
                <button @click="mode = 'cart'"
                        :class="mode === 'cart' ? 'bg-orange-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Panier
                </button>
                <button @click="mode = 'stock_in'"
                        :class="mode === 'stock_in' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Entrée stock
                </button>
                <button @click="mode = 'stock_out'"
                        :class="mode === 'stock_out' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Sortie stock
                </button>
            </div>
        </div>

        {{-- Dernier article scanné --}}
        <div x-show="lastScanned"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                    <template x-if="lastScanned?.image">
                        <img :src="lastScanned.image" class="w-full h-full object-cover" :alt="lastScanned?.name">
                    </template>
                    <template x-if="!lastScanned?.image">
                        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 truncate" x-text="lastScanned?.name"></p>
                    <p x-show="lastScanned?.variant_name" class="text-xs text-orange-600 font-medium" x-text="lastScanned?.variant_name"></p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        SKU: <span x-text="lastScanned?.sku || '—'"></span>
                        <span x-show="lastScanned?.stock !== undefined"> · Stock: <span x-text="lastScanned?.stock"></span></span>
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-lg font-bold text-slate-900" x-text="lastScanned?.price_formatted"></p>
                    <span x-show="mode === 'cart'"
                          class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Ajouté
                    </span>
                    <span x-show="mode === 'stock_in'" class="inline-block mt-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">+1 stock</span>
                    <span x-show="mode === 'stock_out'" class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">-1 stock</span>
                </div>
            </div>
        </div>

        {{-- Erreur --}}
        <div x-show="error"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl p-4">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-red-700 flex-1" x-text="error"></p>
            <button @click="error = null" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Recherche manuelle --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-[13px] font-semibold text-gray-700">Recherche manuelle</h3>
            </div>
            <div class="relative">
                <input type="text"
                       x-model="searchQuery"
                       @input.debounce.350ms="searchProducts()"
                       placeholder="Nom du produit, SKU, code-barres..."
                       class="w-full pl-4 pr-9 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                <div x-show="searchLoading" class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="animate-spin w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            <div x-show="searchResults.length > 0" class="mt-2 border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-50">
                <template x-for="(result, idx) in searchResults" :key="idx">
                    <button @click="addSearchResultToCart(result)"
                            class="search-result-item w-full flex items-center gap-3 px-3 py-2.5 text-left transition-colors">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden">
                            <template x-if="result.image">
                                <img :src="result.image" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!result.image">
                                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate" x-text="result.name"></p>
                            <p class="text-xs text-slate-400" x-text="'SKU: ' + (result.sku || '—')"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-orange-600" x-text="result.price_formatted"></p>
                        </div>
                        <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </template>
            </div>
            <p x-show="searchQuery.length >= 2 && !searchLoading && searchResults.length === 0"
               class="mt-2 text-xs text-center text-slate-400 py-2">Aucun produit trouvé</p>
        </div>

        {{-- Historique scans --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex-1">
            <h3 class="text-[13px] font-semibold text-gray-700 mb-3">Historique des scans</h3>
            <div class="space-y-1.5 max-h-52 overflow-y-auto thin-scroll">
                <template x-for="(s, i) in scanHistory" :key="i">
                    <div class="flex items-center gap-2 px-2.5 py-2 bg-slate-50 rounded-lg text-xs">
                        <span class="text-slate-400 font-mono w-11 flex-shrink-0" x-text="s.time"></span>
                        <span class="font-medium text-slate-700 flex-1 truncate" x-text="s.name"></span>
                        <span class="font-mono text-slate-400 hidden sm:block truncate max-w-[80px]" x-text="s.code"></span>
                        <span :class="{
                                'bg-orange-100 text-orange-700': s.action === 'cart',
                                'bg-emerald-100 text-emerald-700': s.action === 'stock_in',
                                'bg-red-100 text-red-700': s.action === 'stock_out'
                              }"
                              class="px-1.5 py-0.5 rounded font-medium flex-shrink-0"
                              x-text="s.actionLabel"></span>
                    </div>
                </template>
                <p x-show="scanHistory.length === 0" class="text-xs text-slate-400 text-center py-4">Aucun scan pour l'instant</p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         DROITE — Panier POS
    ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 min-h-0 overflow-hidden">

        {{-- Header panier --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h2 class="font-bold text-gray-900 text-base">Panier</h2>
                <span x-show="cart.count > 0"
                      class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 bg-orange-500 text-white text-xs font-bold rounded-full"
                      x-text="cart.count"></span>
            </div>
            <button @click="clearCart()"
                    x-show="cart.items && cart.items.length > 0"
                    class="flex items-center gap-1 text-xs text-slate-400 hover:text-red-500 transition-colors px-2 py-1 rounded-lg hover:bg-red-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Vider
            </button>
        </div>

        {{-- Articles --}}
        <div class="flex-1 overflow-y-auto thin-scroll px-4 py-3 space-y-2">

            {{-- Vide --}}
            <div x-show="!cart.items || cart.items.length === 0"
                 class="flex flex-col items-center justify-center py-20 text-slate-300">
                <svg class="w-14 h-14 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm font-medium">Panier vide</p>
                <p class="text-xs mt-1">Scannez ou recherchez un article</p>
            </div>

            {{-- Items --}}
            <template x-for="(item, key) in cart.items" :key="key">
                <div class="cart-item flex gap-3 p-3 rounded-xl border border-slate-100 bg-white">
                    <div class="w-11 h-11 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                        <template x-if="item.image">
                            <img :src="item.image" class="w-full h-full object-cover" :alt="item.name">
                        </template>
                        <template x-if="!item.image">
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate leading-tight" x-text="item.name"></p>
                        <p x-show="item.variant_name" class="text-xs text-orange-600 font-medium leading-tight mt-0.5" x-text="item.variant_name"></p>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="formatPrice(item.price) + ' / u'"></p>
                    </div>
                    <div class="flex flex-col items-end justify-between gap-1 flex-shrink-0">
                        <div class="flex items-center gap-1.5">
                            <button class="qty-btn"
                                    @click="updateQuantity(item.product_id + '-' + (item.variant_id || 0), item.quantity - 1)">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                                </svg>
                            </button>
                            <span class="w-7 text-center text-sm font-bold text-slate-900" x-text="item.quantity"></span>
                            <button class="qty-btn"
                                    @click="updateQuantity(item.product_id + '-' + (item.variant_id || 0), item.quantity + 1)">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm font-bold text-slate-900" x-text="formatPrice(item.price * item.quantity)"></p>
                    </div>
                    <button @click="removeItem(item.product_id + '-' + (item.variant_id || 0))"
                            class="self-start p-1 text-slate-300 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Footer sticky --}}
        <div class="border-t border-gray-100 px-5 py-4 space-y-3 flex-shrink-0">

            {{-- Remise --}}
            <div x-show="cart.items && cart.items.length > 0" class="flex items-center gap-2">
                <label class="text-xs text-slate-500 whitespace-nowrap flex-shrink-0">Remise (F)</label>
                <input type="number" x-model.number="discount" min="0" :max="cart.total || 0"
                       placeholder="0"
                       class="flex-1 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-orange-400 text-right min-w-0">
                <button x-show="discount > 0" @click="discount = 0"
                        class="text-xs text-slate-400 hover:text-red-500 flex-shrink-0">✕</button>
            </div>

            {{-- Totaux --}}
            <div x-show="cart.items && cart.items.length > 0" class="space-y-1">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Sous-total</span>
                    <span x-text="cart.total_formatted"></span>
                </div>
                <div x-show="discount > 0" class="flex justify-between text-sm text-emerald-600 font-medium">
                    <span>Remise</span>
                    <span x-text="'– ' + formatPrice(discount)"></span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-100">
                    <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">Total</span>
                    <span class="text-2xl font-black text-slate-900"
                          x-text="formatPrice(Math.max(0, (cart.total || 0) - discount))"></span>
                </div>
            </div>

            {{-- Mode paiement --}}
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Paiement</p>
                <div class="flex gap-2">
                    <button @click="paymentMethod = 'cash'" :class="paymentMethod === 'cash' ? 'active' : ''" class="pay-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Espèces
                    </button>
                    <button @click="paymentMethod = 'card'" :class="paymentMethod === 'card' ? 'active' : ''" class="pay-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Carte
                    </button>
                    <button @click="paymentMethod = 'mobile_money'" :class="paymentMethod === 'mobile_money' ? 'active' : ''" class="pay-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Mobile
                    </button>
                </div>
            </div>

            {{-- Montant reçu (espèces) --}}
            <div x-show="paymentMethod === 'cash'"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-2">
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Montant reçu</label>
                <div class="relative">
                    <input type="number" x-model.number="amountReceived" min="0" placeholder="0"
                           class="w-full pl-4 pr-16 py-2.5 border border-slate-200 rounded-xl text-right text-base font-bold focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                    <span class="absolute inset-y-0 right-3 flex items-center text-xs text-slate-400 pointer-events-none">F CFA</span>
                </div>

                {{-- Rendu monnaie --}}
                <div x-show="amountReceived > 0"
                     :class="amountReceived >= Math.max(0,(cart.total||0)-discount)
                             ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                             : 'bg-red-50 border-red-200 text-red-700'"
                     class="flex items-center justify-between px-3 py-2 border rounded-xl text-sm font-bold">
                    <span x-text="amountReceived >= Math.max(0,(cart.total||0)-discount) ? 'Monnaie rendue' : 'Montant insuffisant'"></span>
                    <span x-text="formatPrice(Math.max(0, amountReceived - Math.max(0,(cart.total||0)-discount)))"></span>
                </div>

                {{-- Raccourcis montants --}}
                <div class="flex gap-1.5 flex-wrap" x-show="(cart.total||0) > 0">
                    <template x-for="amt in quickAmounts" :key="amt">
                        <button @click="amountReceived = amt"
                                :class="amountReceived === amt ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors"
                                x-text="formatPrice(amt)"></button>
                    </template>
                </div>
            </div>

            {{-- CTA Valider --}}
            <button @click="processCheckout()"
                    :disabled="!cart.items || cart.items.length === 0 || isProcessing"
                    class="checkout-btn w-full py-4 text-white font-bold text-base rounded-xl shadow-lg shadow-orange-200/60 disabled:shadow-none">
                <template x-if="!isProcessing">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Valider la vente
                    </span>
                </template>
                <template x-if="isProcessing">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Traitement en cours...
                    </span>
                </template>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         MODAL — Vente validée
    ══════════════════════════════════════════════════════ --}}
    <div x-show="showSuccess"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="modal-bg fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center">

            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h3 class="text-2xl font-black text-slate-900 mb-1">Vente validée !</h3>
            <p class="text-slate-500 text-sm mb-5">
                Commande <span class="font-mono font-bold text-slate-700" x-text="lastOrder?.order_number"></span>
            </p>

            <div class="bg-slate-50 rounded-2xl px-6 py-4 mb-5 text-left space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Total encaissé</span>
                    <span class="text-2xl font-black text-orange-600" x-text="lastOrder?.total_formatted"></span>
                </div>
                <div x-show="lastOrder?.change > 0" class="flex justify-between items-center border-t border-slate-200 pt-2">
                    <span class="text-sm text-slate-500">Monnaie rendue</span>
                    <span class="text-lg font-bold text-emerald-600" x-text="lastOrder?.change_formatted"></span>
                </div>
            </div>

            <p x-show="lastOrder?.receipt_url && receiptAutoPrint"
               class="text-xs text-slate-400 mb-4">
                Reçu ouvert — appuyez sur <kbd class="px-1.5 py-0.5 bg-slate-200 rounded font-mono">Entrée</kbd> pour imprimer.
            </p>

            <div class="flex gap-3">
                <button x-show="lastOrder?.receipt_url"
                        @click="openReceipt()"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer
                </button>
                <button @click="newSale()"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle vente
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         MODAL — Caméra
    ══════════════════════════════════════════════════════ --}}
    <div x-show="showCamera"
         x-transition
         class="modal-bg fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="closeCamera()">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-900">Scanner avec la caméra</h3>
                <button @click="closeCamera()" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="rounded-xl overflow-hidden bg-black">
                <video id="camera-preview" autoplay playsinline class="w-full"></video>
            </div>
            <p class="text-xs text-center text-slate-500 mt-3">Placez le code-barres devant la caméra</p>
        </div>
    </div>

</div>

<script src="https://unpkg.com/@zxing/library@0.19.1/umd/index.min.js"></script>
<script>
function posScanner() {
    return {
        // ── État ──────────────────────────────────────────────────
        mode: 'cart',
        receiptAutoPrint: {{ ($receiptAutoPrint ?? false) ? 'true' : 'false' }},
        cart: { items: [], count: 0, total: 0, total_formatted: '0 F CFA' },
        discount: 0,
        paymentMethod: 'cash',
        amountReceived: 0,
        quickAmounts: [],

        // ── Scan ─────────────────────────────────────────────────
        lastScanned: null,
        scanHistory: [],
        scanFlashOk: false,
        scanFlashErr: false,
        error: null,

        // ── Recherche ─────────────────────────────────────────────
        searchQuery: '',
        searchResults: [],
        searchLoading: false,

        // ── UI ───────────────────────────────────────────────────
        showSuccess: false,
        showCamera: false,
        isProcessing: false,
        lastOrder: null,
        codeReader: null,

        // ─────────────────────────────────────────────────────────
        init() {
            this.loadCart();
            this.$nextTick(() => this.$refs.scanInput?.focus());
        },

        async loadCart() {
            try {
                const r = await fetch('{{ route("admin.scanner.cart") }}');
                this.cart = await r.json();
                this.recalcQuickAmounts();
            } catch (e) { console.error(e); }
        },

        // ── Scan ─────────────────────────────────────────────────
        handleScanEnter(event) {
            const val = (event.target.value || '').trim();
            event.target.value = '';
            if (val) this.scanCode(val);
        },

        async scanCode(code) {
            if (!code.trim()) return;
            this.error = null;
            try {
                const r = await fetch('{{ route("admin.scanner.scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ code: code.trim() })
                });
                const data = await r.json();
                if (!data.found) {
                    this.error = data.message || 'Produit non trouvé';
                    this.flash('err');
                    return;
                }
                this.lastScanned = data.data;
                this.flash('ok');
                const now = new Date();
                this.scanHistory.unshift({
                    time: now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                    name: data.data.name,
                    code,
                    action: this.mode,
                    actionLabel: this.mode === 'cart' ? 'Panier' : this.mode === 'stock_in' ? 'Entrée' : 'Sortie'
                });
                if (this.scanHistory.length > 30) this.scanHistory.pop();

                if (this.mode === 'cart') await this.addToCart(data.data);
                else await this.processStockMovement(data.data);

            } catch (e) { console.error(e); this.error = 'Erreur de connexion'; }
        },

        flash(type) {
            const key = type === 'ok' ? 'scanFlashOk' : 'scanFlashErr';
            this[key] = true;
            setTimeout(() => this[key] = false, 450);
        },

        // ── Recherche manuelle ────────────────────────────────────
        async searchProducts() {
            const q = (this.searchQuery || '').trim();
            if (q.length < 2) { this.searchResults = []; return; }
            this.searchLoading = true;
            try {
                const r = await fetch(`/api/admin/search?q=${encodeURIComponent(q)}`);
                const data = await r.json();
                this.searchResults = (data.results || [])
                    .filter(i => i.type === 'product')
                    .slice(0, 8)
                    .map(i => ({
                        name:           i.label,
                        sku:            (i.sublabel?.match(/SKU:\s*([^\s·]+)/) || [])[1] || '—',
                        price_formatted:(i.sublabel?.match(/([0-9\s]+\s*F)/) || [])[1] || '—',
                        price:          parseInt(((i.sublabel?.match(/([0-9\s]+)\s*F/) || [])[1] || '0').replace(/\s/g, '')),
                        image:          null,
                        id:             (i.url?.split('/').filter(Boolean).pop()) || null,
                    }));
            } catch (e) { console.error(e); }
            finally { this.searchLoading = false; }
        },

        async addSearchResultToCart(result) {
            if (!result.id) return;
            this.error = null;
            try {
                const r = await fetch('{{ route("admin.scanner.cart.add") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ product_id: result.id, quantity: 1 })
                });
                const data = await r.json();
                if (data.success) {
                    this.cart = data.cart;
                    this.recalcQuickAmounts();
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.flash('ok');
                } else {
                    this.error = data.message || 'Erreur ajout';
                }
            } catch (e) { this.error = 'Erreur de connexion'; }
        },

        // ── Panier ────────────────────────────────────────────────
        async addToCart(product) {
            try {
                const r = await fetch('{{ route("admin.scanner.cart.add") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        product_id: product.product_id || product.id,
                        variant_id: product.variant_id || null,
                        quantity: 1
                    })
                });
                const data = await r.json();
                if (data.success) { this.cart = data.cart; this.recalcQuickAmounts(); }
            } catch (e) { console.error(e); }
        },

        async updateQuantity(key, quantity) {
            if (quantity <= 0) { await this.removeItem(key); return; }
            try {
                const r = await fetch(`/admin/scanner/cart/${key}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ quantity })
                });
                const data = await r.json();
                if (data.success) { this.cart = data.cart; this.recalcQuickAmounts(); }
            } catch (e) { console.error(e); }
        },

        async removeItem(key) {
            try {
                const r = await fetch(`/admin/scanner/cart/${key}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await r.json();
                if (data.success) { this.cart = data.cart; this.recalcQuickAmounts(); }
            } catch (e) { console.error(e); }
        },

        async clearCart() {
            try {
                const r = await fetch('{{ route("admin.scanner.cart.clear") }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await r.json();
                if (data.success) { this.cart = data.cart; this.discount = 0; this.amountReceived = 0; this.quickAmounts = []; }
            } catch (e) { console.error(e); }
        },

        // ── Checkout ──────────────────────────────────────────────
        async processCheckout() {
            if (!this.cart.items || this.cart.items.length === 0 || this.isProcessing) return;
            this.isProcessing = true;
            this.error = null;
            const net = Math.max(0, (this.cart.total || 0) - this.discount);
            try {
                const r = await fetch('{{ route("admin.scanner.checkout") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        payment_method: this.paymentMethod,
                        amount_received: this.paymentMethod === 'cash' ? this.amountReceived : net,
                        discount_amount: this.discount,
                    })
                });
                const data = await r.json();
                if (data.success) {
                    this.lastOrder = { ...data.order, change: data.change, change_formatted: data.change_formatted, receipt_url: data.receipt_url };
                    this.cart = { items: [], count: 0, total: 0, total_formatted: '0 F CFA' };
                    this.amountReceived = 0;
                    this.discount = 0;
                    this.quickAmounts = [];
                    this.showSuccess = true;
                    if (this.receiptAutoPrint && data.receipt_url) {
                        window.open(data.receipt_url, 'pos_receipt', 'width=440,height=700,toolbar=0,scrollbars=1');
                    }
                } else {
                    this.error = data.message || 'Erreur lors de la validation';
                }
            } catch (e) { console.error(e); this.error = 'Erreur de connexion'; }
            finally { this.isProcessing = false; }
        },

        openReceipt() {
            if (this.lastOrder?.receipt_url) {
                window.open(this.lastOrder.receipt_url, 'pos_receipt', 'width=440,height=700,toolbar=0,scrollbars=1');
            }
        },

        newSale() {
            this.showSuccess = false;
            this.lastOrder = null;
            this.$nextTick(() => this.$refs.scanInput?.focus());
        },

        // ── Stock ─────────────────────────────────────────────────
        async processStockMovement(product) {
            try {
                const r = await fetch('{{ route("admin.scanner.stock-movement") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        product_id: product.product_id || product.id,
                        variant_id: product.variant_id || null,
                        type: this.mode === 'stock_in' ? 'in' : 'out',
                        quantity: 1
                    })
                });
                const data = await r.json();
                if (data.success && this.lastScanned) this.lastScanned.stock = data.new_stock;
            } catch (e) { console.error(e); }
        },

        // ── Caméra ────────────────────────────────────────────────
        async openCamera() {
            this.showCamera = true;
            await this.$nextTick();
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                const video = document.getElementById('camera-preview');
                video.srcObject = stream;
                this.codeReader = new ZXing.BrowserMultiFormatReader();
                this.codeReader.decodeFromVideoDevice(null, 'camera-preview', (result) => {
                    if (result) { this.scanCode(result.getText()); this.closeCamera(); }
                });
            } catch (e) { this.error = "Impossible d'accéder à la caméra"; this.showCamera = false; }
        },

        closeCamera() {
            this.showCamera = false;
            const video = document.getElementById('camera-preview');
            if (video?.srcObject) { video.srcObject.getTracks().forEach(t => t.stop()); video.srcObject = null; }
            if (this.codeReader) { this.codeReader.reset(); this.codeReader = null; }
            this.$refs.scanInput?.focus();
        },

        // ── Utilitaires ───────────────────────────────────────────
        formatPrice(amount) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(amount || 0)) + ' F CFA';
        },

        recalcQuickAmounts() {
            const total = Math.max(0, (this.cart?.total || 0) - this.discount);
            if (total <= 0) { this.quickAmounts = []; return; }
            const base = Math.ceil(total / 500) * 500;
            this.quickAmounts = [base, base + 500, base + 1000, base + 2000];
        },
    };
}
</script>
@endsection
