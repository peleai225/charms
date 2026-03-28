    {{-- ===== CART DRAWER (slide-over) ===== --}}
    <div x-data
         x-show="$store.cartDrawer.isOpen"
         x-cloak
         class="relative z-[300]">

        {{-- Backdrop --}}
        <div x-show="$store.cartDrawer.isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$store.cartDrawer.close()"
             class="fixed inset-0 bg-black/50"></div>

        {{-- Drawer panel --}}
        <div x-show="$store.cartDrawer.isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 w-full max-w-md flex flex-col bg-white shadow-2xl transform">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">
                    Mon panier
                    <span x-show="$store.cart.count > 0"
                          class="ml-2 inline-flex items-center justify-center w-6 h-6 bg-primary-600 text-white text-xs rounded-full"
                          x-text="$store.cart.count"></span>
                </h2>
                <button @click="$store.cartDrawer.close()"
                        class="p-2 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="$store.cartDrawer.loading" class="flex-1 flex items-center justify-center">
                <svg class="w-8 h-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            {{-- Empty state --}}
            <div x-show="!$store.cartDrawer.loading && $store.cartDrawer.items.length === 0"
                 class="flex-1 flex flex-col items-center justify-center text-center px-6">
                <svg class="w-16 h-16 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="text-slate-500 font-medium mb-1">Votre panier est vide</p>
                <p class="text-sm text-slate-400 mb-6">Découvrez nos produits et ajoutez-en au panier.</p>
                <a href="{{ route('shop.index') }}"
                   @click="$store.cartDrawer.close()"
                   class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                    Voir la boutique
                </a>
            </div>

            {{-- Items list --}}
            <div x-show="!$store.cartDrawer.loading && $store.cartDrawer.items.length > 0"
                 class="flex-1 overflow-y-auto divide-y divide-slate-100 px-5 py-2">
                <template x-for="item in $store.cartDrawer.items" :key="item.id">
                    <div class="py-4 flex gap-3">
                        {{-- Image --}}
                        <a :href="'/produit/' + item.slug" @click="$store.cartDrawer.close()">
                            <img :src="item.image || '/images/placeholder.png'"
                                 :alt="item.name"
                                 class="w-16 h-16 rounded-lg object-cover border border-slate-100 flex-shrink-0">
                        </a>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <a :href="'/produit/' + item.slug"
                               @click="$store.cartDrawer.close()"
                               class="text-sm font-medium text-slate-900 hover:text-primary-600 line-clamp-2"
                               x-text="item.name"></a>
                            <p x-show="item.variant" x-text="item.variant"
                               class="text-xs text-slate-400 mt-0.5"></p>
                            <p class="text-sm font-bold text-primary-600 mt-1" x-text="item.price_fmt"></p>
                        </div>
                        {{-- Qty + remove --}}
                        <div class="flex flex-col items-end justify-between gap-2">
                            <button @click="$store.cartDrawer.remove(item.id)"
                                    class="text-slate-300 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <div class="flex items-center gap-1 border border-slate-200 rounded-lg overflow-hidden">
                                <button @click="$store.cartDrawer.updateQty(item.id, item.quantity - 1)"
                                        class="px-2 py-1 text-slate-500 hover:bg-slate-100 transition-colors text-sm">−</button>
                                <span class="px-2 text-sm font-medium text-slate-800" x-text="item.quantity"></span>
                                <button @click="$store.cartDrawer.updateQty(item.id, item.quantity + 1)"
                                        class="px-2 py-1 text-slate-500 hover:bg-slate-100 transition-colors text-sm">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer totals + CTA --}}
            <div x-show="!$store.cartDrawer.loading && $store.cartDrawer.items.length > 0"
                 class="border-t border-slate-200 px-5 py-4 space-y-3 bg-slate-50">
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Sous-total</span>
                        <span x-text="$store.cartDrawer.subtotal_fmt"></span>
                    </div>
                    <div x-show="$store.cartDrawer.discount_fmt" class="flex justify-between text-green-600">
                        <span>Réduction (<span x-text="$store.cartDrawer.coupon_code"></span>)</span>
                        <span>− <span x-text="$store.cartDrawer.discount_fmt"></span></span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 text-base pt-1 border-t border-slate-200">
                        <span>Total</span>
                        <span x-text="$store.cartDrawer.total_fmt"></span>
                    </div>
                </div>
                <a :href="$store.cartDrawer.checkout_url"
                   class="block w-full text-center py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    Passer la commande →
                </a>
                <a href="{{ route('cart.index') }}"
                   @click="$store.cartDrawer.close()"
                   class="block w-full text-center py-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
                    Voir le panier complet
                </a>
            </div>
        </div>
    </div>
