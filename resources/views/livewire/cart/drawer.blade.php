<div
    x-data="{ open: @entangle('open').live }"
    @open-cart-drawer.window="open = true"
    @keydown.escape.window="if(open){ open = false }"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="close"
        class="fixed inset-0 bg-slate-900/50 z-40"
        x-cloak
    ></div>

    {{-- Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-xl z-50 flex flex-col"
        x-cloak
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <h2 class="font-semibold text-slate-900 text-sm">Votre panier</h2>
                @if($cart->items_count > 0)
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">{{ $cart->items_count }}</span>
                @endif
            </div>
            <button wire:click="close" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Items list --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            @forelse($cart->items as $item)
            @php
                $imgPath = $item->variant?->image
                    ?? $item->product->images->where('is_primary', true)->first()?->path
                    ?? $item->product->images->first()?->path;
            @endphp
            <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100" wire:key="item-{{ $item->id }}">

                {{-- Thumbnail --}}
                <a href="{{ route('shop.product', $item->product->slug) }}" wire:click="close"
                   class="shrink-0 w-16 h-16 rounded-lg bg-white border border-slate-200 overflow-hidden">
                    @if($imgPath)
                        <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-100">
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </a>

                {{-- Details --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 leading-snug line-clamp-2">{{ $item->product->name }}</p>
                    @if($item->variant)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $item->variant->label }}</p>
                    @endif
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ format_price($item->unit_price) }}</p>

                    {{-- Quantity stepper --}}
                    <div class="flex items-center gap-1.5 mt-2">
                        <button wire:click="updateQty({{ $item->id }}, {{ $item->quantity - 1 }})"
                                wire:loading.attr="disabled"
                                class="w-7 h-7 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 flex items-center justify-center font-medium transition-colors duration-100 text-base leading-none">
                            &minus;
                        </button>
                        <span class="w-7 text-center text-sm font-semibold text-slate-900 tabular-nums">{{ $item->quantity }}</span>
                        <button wire:click="updateQty({{ $item->id }}, {{ $item->quantity + 1 }})"
                                wire:loading.attr="disabled"
                                class="w-7 h-7 rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 flex items-center justify-center font-medium transition-colors duration-100 text-base leading-none">
                            +
                        </button>
                    </div>
                </div>

                {{-- Remove --}}
                <button wire:click="removeItem({{ $item->id }})" wire:loading.attr="disabled"
                        class="self-start mt-0.5 p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors duration-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-900">Panier vide</p>
                <p class="text-xs text-slate-500 mt-1">Parcourez nos produits</p>
                <a href="{{ route('shop.index') }}" wire:click="close"
                   class="mt-5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors duration-150">
                    Voir la boutique
                </a>
            </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($cart->items_count > 0)
        <div class="shrink-0 border-t border-slate-100 px-5 py-4 space-y-3 bg-white">

            {{-- Totals --}}
            <div class="space-y-1">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Sous-total</span>
                    <span class="font-medium text-slate-900">{{ format_price($cart->subtotal) }}</span>
                </div>
                @if($cart->coupon && $cart->discount_amount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Code {{ $cart->coupon->code }}</span>
                    <span class="font-medium">&minus; {{ format_price($cart->discount_amount) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-base font-bold text-slate-900 pt-2 mt-1 border-t border-slate-100">
                    <span>Total</span>
                    <span>{{ format_price($cart->total) }}</span>
                </div>
            </div>

            {{-- Géolocalisation --}}
            <div
                x-data="{
                    status: '{{ $locationAddress ? 'found' : 'idle' }}',
                    address: @js($locationAddress),
                    locating: false,
                    error: '',
                    locate() {
                        if (!navigator.geolocation) {
                            this.error = 'Géolocalisation non disponible sur ce navigateur.';
                            return;
                        }
                        this.locating = true;
                        this.error = '';
                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                const lat = pos.coords.latitude;
                                const lng = pos.coords.longitude;
                                try {
                                    const res = await fetch(
                                        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=jsonv2&accept-language=fr`,
                                        { headers: { 'Accept': 'application/json' } }
                                    );
                                    const data = await res.json();
                                    const addr = data.display_name ?? `${lat}, ${lng}`;
                                    this.address = addr;
                                    this.status = 'found';
                                    $wire.setLocation(addr, lat, lng);
                                } catch(e) {
                                    const addr = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                                    this.address = addr;
                                    this.status = 'found';
                                    $wire.setLocation(addr, lat, lng);
                                } finally {
                                    this.locating = false;
                                }
                            },
                            (err) => {
                                this.locating = false;
                                this.error = err.code === 1 ? 'Accès à la position refusé.' : 'Impossible de récupérer la position.';
                            },
                            { timeout: 10000, maximumAge: 60000 }
                        );
                    },
                    clear() {
                        this.address = '';
                        this.status = 'idle';
                        $wire.setLocation('', 0, 0);
                    }
                }"
                class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-600">Adresse de livraison</span>
                    <div class="flex items-center gap-1">
                        <button
                            @click="locate()"
                            :disabled="locating"
                            class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-colors duration-100"
                            :class="status === 'found' ? 'text-blue-600 bg-blue-50 hover:bg-blue-100' : 'text-white bg-blue-600 hover:bg-blue-700'"
                        >
                            <svg x-show="!locating" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg x-show="locating" class="w-3.5 h-3.5 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h-4z"/>
                            </svg>
                            <span x-text="locating ? 'Localisation…' : (status === 'found' ? 'Actualiser' : 'Ma position GPS')"></span>
                        </button>
                        <button x-show="status === 'found'" @click="clear()" class="p-1 text-slate-400 hover:text-red-500 rounded transition-colors duration-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <p x-show="status === 'found'" x-text="address" class="mt-1.5 text-xs text-slate-700 leading-snug line-clamp-2"></p>
                <p x-show="error" x-text="error" class="mt-1 text-xs text-red-500"></p>
                <p x-show="status === 'idle' && !error" class="mt-1 text-xs text-slate-400">Optionnel — inclus automatiquement dans le message WhatsApp</p>
            </div>

            {{-- WhatsApp — option principale pour la CI --}}
            @if($hasWhatsapp)
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               class="flex items-center justify-center gap-2.5 w-full py-3.5 bg-[#25D366] hover:bg-[#1ebe5d] text-white font-bold text-sm rounded-lg transition-colors duration-150 shadow-sm">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Commander via WhatsApp
            </a>
            @endif

            {{-- Checkout classique --}}
            <a href="{{ route('checkout.index') }}" wire:click="close"
               class="flex items-center justify-center gap-2 w-full py-3 border border-slate-200 hover:border-blue-500 text-slate-700 hover:text-blue-600 font-semibold text-sm rounded-lg transition-colors duration-150">
                Paiement en ligne
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="{{ route('cart.index') }}" wire:click="close"
               class="block text-center text-xs text-slate-400 hover:text-slate-600 transition-colors duration-150">
                Modifier le panier
            </a>
        </div>
        @endif

        {{-- Wire loading overlay --}}
        <div wire:loading.flex
             class="absolute inset-0 bg-white/60 flex items-center justify-center z-10 pointer-events-none">
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-4 py-2 shadow-sm">
                <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h-4z"/>
                </svg>
                <span class="text-xs font-medium text-slate-600">Mise à jour…</span>
            </div>
        </div>
    </div>
</div>
