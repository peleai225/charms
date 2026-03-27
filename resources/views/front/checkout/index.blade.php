@extends('layouts.front')

@section('title', 'Commander')

@section('content')
<!-- Hero-style header -->
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-10 mb-8 relative overflow-hidden">
    <div class="absolute -top-16 -right-16 w-64 h-64 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-4 relative">
        <nav class="text-sm text-slate-400 mb-4 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-white transition-colors">Panier</a>
            <span class="text-slate-600">/</span>
            <span class="text-white font-medium">Commander</span>
        </nav>
        <h1 class="text-3xl font-extrabold">Finaliser ma commande</h1>
    </div>
</div>

<div class="container mx-auto px-4 pb-8">
    <!-- Indicateur de progression -->
    <div class="mb-8 overflow-x-auto">
        <div class="flex items-center min-w-max max-w-2xl mx-auto">
            <!-- Panier (completed) -->
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-500 text-white ring-4 ring-green-100 text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:inline">Panier</span>
            </div>
            <div class="flex-1 h-0.5 mx-3 bg-green-500"></div>
            <!-- Contact (active) -->
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white ring-4 ring-primary-100 animate-pulse text-sm font-semibold">1</div>
                <span class="ml-2 text-sm font-semibold text-gray-900 hidden sm:inline">Contact</span>
            </div>
            <div class="flex-1 h-0.5 mx-3 bg-slate-200"></div>
            <!-- Livraison (pending) -->
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-200 text-slate-400 text-sm font-semibold">2</div>
                <span class="ml-2 text-sm font-medium text-slate-400 hidden sm:inline">Livraison</span>
            </div>
            <div class="flex-1 h-0.5 mx-3 bg-slate-200"></div>
            <!-- Facturation (pending) -->
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-200 text-slate-400 text-sm font-semibold">3</div>
                <span class="ml-2 text-sm font-medium text-slate-400 hidden sm:inline">Facturation</span>
            </div>
            <div class="flex-1 h-0.5 mx-3 bg-slate-200"></div>
            <!-- Paiement (pending) -->
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-200 text-slate-400 text-sm font-semibold">4</div>
                <span class="ml-2 text-sm font-medium text-slate-400 hidden sm:inline">Paiement</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('checkout.store') }}" x-data="checkoutForm()" x-init="init()" @submit="isSubmitting = true" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <!-- Formulaire -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations de contact -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 text-white rounded-xl shadow-lg shadow-primary-500/25 flex items-center justify-center text-sm font-bold">1</span>
                    Informations de contact
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" id="email" required
                            value="{{ old('email', $customer?->email ?? auth()->user()?->email) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                        <input type="tel" name="phone" id="phone" required
                            value="{{ old('phone', $customer?->phone) }}"
                            placeholder="+225 XX XX XX XX XX"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="newsletter" value="1" {{ old('newsletter') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-600">Recevoir les offres par email</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Adresse de livraison -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 text-white rounded-xl shadow-lg shadow-primary-500/25 flex items-center justify-center text-sm font-bold">2</span>
                    Adresse de livraison
                </h2>

                @if($addresses->where('type', 'shipping')->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresses enregistrées</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($addresses->where('type', 'shipping') as $addr)
                        <label class="relative border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-primary-500 transition-colors">
                            <input type="radio" name="saved_shipping" value="{{ $addr->id }}" class="sr-only peer"
                                @click="fillShippingAddress({{ json_encode($addr) }})">
                            <div class="peer-checked:border-primary-500 absolute inset-0 rounded-xl border-2 border-transparent"></div>
                            <p class="font-medium">{{ $addr->first_name }} {{ $addr->last_name }}</p>
                            <p class="text-sm text-gray-600">{{ $addr->address }}</p>
                            <p class="text-sm text-gray-600">{{ $addr->postal_code }} {{ $addr->city }}</p>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="shipping_first_name" class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                        <input type="text" name="shipping_first_name" id="shipping_first_name" required
                            x-model="shipping.first_name"
                            value="{{ old('shipping_first_name', $customer?->first_name) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="shipping_last_name" class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" name="shipping_last_name" id="shipping_last_name" required
                            x-model="shipping.last_name"
                            value="{{ old('shipping_last_name', $customer?->last_name) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Adresse *</label>
                        <input type="text" name="shipping_address" id="shipping_address" required
                            x-model="shipping.address"
                            placeholder="Numéro et nom de rue"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="shipping_address_2" class="block text-sm font-medium text-gray-700 mb-1">Complément d'adresse</label>
                        <input type="text" name="shipping_address_2" id="shipping_address_2"
                            x-model="shipping.address_2"
                            placeholder="Appartement, étage, bâtiment..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                        <input type="text" name="shipping_city" id="shipping_city" required
                            x-model="shipping.city" @input.debounce.500ms="calculateEstimatedShipping()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700 mb-1">Code postal *</label>
                        <input type="text" name="shipping_postal_code" id="shipping_postal_code" required
                            x-model="shipping.postal_code"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700 mb-1">Pays *</label>
                        <select name="shipping_country" id="shipping_country" required x-model="shipping.country" @change="calculateEstimatedShipping()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <option value="">Sélectionner un pays</option>
                            <option value="CI" selected>🇨🇮 Côte d'Ivoire</option>
                            <option value="SN">🇸🇳 Sénégal</option>
                            <option value="ML">🇲🇱 Mali</option>
                            <option value="BF">🇧🇫 Burkina Faso</option>
                            <option value="TG">🇹🇬 Togo</option>
                            <option value="BJ">🇧🇯 Bénin</option>
                            <option value="GN">🇬🇳 Guinée</option>
                            <option value="CM">🇨🇲 Cameroun</option>
                            <option value="GA">🇬🇦 Gabon</option>
                            <option value="CG">🇨🇬 Congo</option>
                            <option value="FR">🇫🇷 France</option>
                            <option value="BE">🇧🇪 Belgique</option>
                            <option value="CH">🇨🇭 Suisse</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Adresse de facturation -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 text-white rounded-xl shadow-lg shadow-primary-500/25 flex items-center justify-center text-sm font-bold">3</span>
                    Adresse de facturation
                </h2>

                <label class="flex items-center gap-2 mb-4 cursor-pointer">
                    <input type="checkbox" name="same_billing" value="1" x-model="sameBilling" checked
                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Identique à l'adresse de livraison</span>
                </label>

                <div x-show="!sameBilling" x-collapse class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="billing_first_name" class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                        <input type="text" name="billing_first_name" id="billing_first_name"
                            :required="!sameBilling"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="billing_last_name" class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" name="billing_last_name" id="billing_last_name"
                            :required="!sameBilling"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Adresse *</label>
                        <input type="text" name="billing_address" id="billing_address"
                            :required="!sameBilling"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="billing_address_2" class="block text-sm font-medium text-gray-700 mb-1">Complément</label>
                        <input type="text" name="billing_address_2" id="billing_address_2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="billing_city" class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                        <input type="text" name="billing_city" id="billing_city"
                            :required="!sameBilling"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label for="billing_postal_code" class="block text-sm font-medium text-gray-700 mb-1">Code postal *</label>
                        <input type="text" name="billing_postal_code" id="billing_postal_code"
                            :required="!sameBilling"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="billing_country" class="block text-sm font-medium text-gray-700 mb-1">Pays *</label>
                        <select name="billing_country" id="billing_country" :required="!sameBilling"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <option value="">Sélectionner un pays</option>
                            <option value="CI">🇨🇮 Côte d'Ivoire</option>
                            <option value="SN">🇸🇳 Sénégal</option>
                            <option value="FR">🇫🇷 France</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Mode de paiement -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 text-white rounded-xl shadow-lg shadow-primary-500/25 flex items-center justify-center text-sm font-bold">4</span>
                    Mode de paiement
                </h2>

                <div class="space-y-3">
                    <!-- CinetPay -->
                    @if(($settings['payment_cinetpay_enabled'] ?? '0') === '1')
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                        :class="paymentMethod === 'cinetpay' ? 'border-primary-500 bg-primary-50 scale-[1.02] shadow-md' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_method" value="cinetpay" x-model="paymentMethod" class="sr-only" {{ (($settings['payment_cinetpay_enabled'] ?? '0') === '1' && ($settings['payment_lygos_enabled'] ?? '0') !== '1' && ($settings['payment_cod_enabled'] ?? '1') !== '1') ? 'checked' : '' }}>
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                :class="paymentMethod === 'cinetpay' ? 'border-primary-500' : 'border-gray-300'">
                                <div class="w-2.5 h-2.5 rounded-full transition-colors"
                                    :class="paymentMethod === 'cinetpay' ? 'bg-primary-500' : 'bg-transparent'"></div>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Mobile Money / Carte bancaire</p>
                                <p class="text-sm text-gray-500">Orange Money, MTN, Wave, Moov, Visa, Mastercard</p>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <img src="https://cinetpay.com/assets/images/logo-orange-money.png" alt="Orange Money" class="h-6 object-contain">
                            <img src="https://cinetpay.com/assets/images/logo-mtn.png" alt="MTN" class="h-6 object-contain">
                        </div>
                    </label>
                    @endif

                    <!-- Lygos Pay -->
                    @if(($settings['payment_lygos_enabled'] ?? '0') === '1')
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                        :class="paymentMethod === 'lygos' ? 'border-primary-500 bg-primary-50 scale-[1.02] shadow-md' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_method" value="lygos" x-model="paymentMethod" class="sr-only" {{ (($settings['payment_cinetpay_enabled'] ?? '0') !== '1' && ($settings['payment_lygos_enabled'] ?? '0') === '1' && ($settings['payment_cod_enabled'] ?? '1') !== '1') ? 'checked' : '' }}>
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                :class="paymentMethod === 'lygos' ? 'border-primary-500' : 'border-gray-300'">
                                <div class="w-2.5 h-2.5 rounded-full transition-colors"
                                    :class="paymentMethod === 'lygos' ? 'bg-primary-500' : 'bg-transparent'"></div>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <img src="https://lygosapp.com/favicon.ico" alt="Lygos Pay" class="w-8 h-8">
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Lygos Pay</p>
                                <p class="text-sm text-gray-500">Mobile Money et paiements internationaux</p>
                            </div>
                        </div>
                    </label>
                    @endif

                    <!-- Paiement à la livraison -->
                    @if(($settings['payment_cod_enabled'] ?? '1') === '1')
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                        :class="paymentMethod === 'cod' ? 'border-primary-500 bg-primary-50 scale-[1.02] shadow-md' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="sr-only" {{ (($settings['payment_cinetpay_enabled'] ?? '0') !== '1' && ($settings['payment_lygos_enabled'] ?? '0') !== '1' && ($settings['payment_cod_enabled'] ?? '1') === '1') ? 'checked' : '' }}>
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                :class="paymentMethod === 'cod' ? 'border-primary-500' : 'border-gray-300'">
                                <div class="w-2.5 h-2.5 rounded-full transition-colors"
                                    :class="paymentMethod === 'cod' ? 'bg-primary-500' : 'bg-transparent'"></div>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Paiement à la livraison</p>
                                <p class="text-sm text-gray-500">Payez en espèces à la réception</p>
                            </div>
                        </div>
                    </label>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes de commande (optionnel)</h2>
                <textarea name="notes" rows="3" placeholder="Instructions spéciales pour la livraison..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">{{ old('notes') }}</textarea>
            </div>

            @if(auth()->check())
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="save_address" value="1" checked
                    class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-600">Sauvegarder cette adresse pour mes prochaines commandes</span>
            </label>
            @endif
        </div>

        <!-- Récapitulatif -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Récapitulatif</h2>

                <!-- Articles -->
                <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
                    @foreach($cart->items as $item)
                    <div class="flex gap-3">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->variant?->image)
                                <img src="{{ asset('storage/' . $item->variant->image) }}" alt="" class="w-full h-full object-cover">
                            @elseif($item->product->images->where('is_primary', true)->first())
                                <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product->name }}</p>
                            @if($item->variant)
                                <p class="text-xs text-gray-500">{{ $item->variant->name }}</p>
                            @endif
                            <p class="text-sm text-gray-600">{{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', ' ') }} F</p>
                        </div>
                        <p class="font-medium text-sm">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                    </div>
                    @endforeach
                </div>

                {{-- Code promo --}}
                <div class="mb-4" x-data="couponInput()">
                    @if($cart->coupon_code)
                    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 py-2">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span class="font-semibold text-green-700">{{ $cart->coupon_code }}</span>
                            <span class="text-green-600 text-xs">appliqué ✓</span>
                        </div>
                        <form method="POST" action="{{ route('cart.coupon.remove') }}" class="no-ajax">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors ml-2 text-xs underline">Retirer</button>
                        </form>
                    </div>
                    @else
                    <div class="flex gap-2">
                        <input type="text" x-model="couponCode" placeholder="Code promo"
                            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 uppercase"
                            @keydown.enter.prevent="applyCoupon()">
                        <button type="button" @click="applyCoupon()" :disabled="applying"
                            class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-medium transition-colors disabled:opacity-50 whitespace-nowrap">
                            <span x-show="!applying">Appliquer</span>
                            <span x-show="applying" class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </span>
                        </button>
                    </div>
                    <p x-show="couponError" x-text="couponError" class="mt-1 text-xs text-red-500"></p>
                    @endif
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sous-total</span>
                        <span class="font-medium">{{ number_format($cart->subtotal, 0, ',', ' ') }} F</span>
                    </div>

                    @if($cart->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Réduction <span class="text-xs opacity-75">({{ $cart->coupon_code }})</span></span>
                        <span>-{{ number_format($cart->discount_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-gray-600">Livraison</span>
                        <span class="font-medium flex items-center gap-1">
                            <span x-show="isCalculatingShipping" class="animate-spin inline-block">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            <span x-text="isCalculatingShipping ? 'Calcul...' : shippingText">Selon destination</span>
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-200 mt-4 pt-4">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total estimé</span>
                        <span x-text="formatPrice(estimatedTotal)">{{ number_format($cart->total, 0, ',', ' ') }} F CFA</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">* Frais de livraison inclus (selon destination)</p>
                </div>

                <!-- Bouton commander -->
                <button type="submit" :disabled="isSubmitting" class="mt-6 w-full py-4 px-6 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 disabled:opacity-70 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-xl shadow-primary-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                    <span x-show="!isSubmitting">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <span x-show="isSubmitting" class="animate-spin">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                    <span x-text="isSubmitting ? 'Traitement en cours...' : (paymentMethod === 'cod' ? 'Confirmer la commande' : 'Payer maintenant')">Payer maintenant</span>
                </button>

                <!-- Sécurité -->
                <div class="mt-4 text-center text-xs text-gray-500">
                    <p class="flex items-center justify-center gap-1">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Paiement 100% sécurisé
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
</div>

@push('scripts')
<script>
function checkoutForm() {
    return {
        isSubmitting: false,
        isCalculatingShipping: false,
        sameBilling: true,
        paymentMethod: '{{ (($settings['payment_cinetpay_enabled'] ?? '0') === '1') ? 'cinetpay' : ((($settings['payment_lygos_enabled'] ?? '0') === '1') ? 'lygos' : 'cod') }}',
        shippingText: 'Selon destination',
        estimatedShipping: 0,
        estimatedTotal: {{ $cart->total }},
        shipping: {
            first_name: '{{ old('shipping_first_name', $customer?->first_name ?? '') }}',
            last_name: '{{ old('shipping_last_name', $customer?->last_name ?? '') }}',
            address: '',
            address_2: '',
            city: '',
            postal_code: '',
            country: '{{ old('shipping_country', 'CI') }}',
        },
        
        formatPrice(amount) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(amount)) + ' F CFA';
        },
        
        async calculateEstimatedShipping() {
            const country = this.shipping.country || 'CI';
            const city = this.shipping.city || '';
            const subtotal = {{ $cart->subtotal }};
            
            if (!country) {
                this.estimatedShipping = 0;
                this.estimatedTotal = subtotal - {{ $cart->discount_amount }};
                this.shippingText = 'Sélectionner un pays';
                return;
            }
            
            this.isCalculatingShipping = true;
            try {
                const response = await fetch('{{ route("api.shipping-cost") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        country: country,
                        city: city,
                        cart_subtotal: subtotal
                    })
                });
                
                const data = await response.json();
                this.estimatedShipping = data.shipping_cost || 0;
                this.estimatedTotal = subtotal - {{ $cart->discount_amount }} + this.estimatedShipping;
                this.shippingText = this.estimatedShipping === 0 ? 'Gratuite' : data.formatted || this.formatPrice(this.estimatedShipping);
            } catch (error) {
                console.error('Erreur calcul livraison:', error);
                this.estimatedShipping = 5000;
                this.estimatedTotal = subtotal - {{ $cart->discount_amount }} + this.estimatedShipping;
                this.shippingText = this.formatPrice(this.estimatedShipping);
            } finally {
                this.isCalculatingShipping = false;
            }
        },
        
        fillShippingAddress(address) {
            this.shipping.first_name = address.first_name;
            this.shipping.last_name = address.last_name;
            this.shipping.address = address.address;
            this.shipping.address_2 = address.address_2 || '';
            this.shipping.city = address.city;
            this.shipping.postal_code = address.postal_code;
            this.shipping.country = address.country;
            this.calculateEstimatedShipping();
        },
        
        init() {
            // Calculer les frais de livraison au chargement si un pays est sélectionné
            if (this.shipping.country) {
                this.calculateEstimatedShipping();
            }
            
            // Watcher pour recalculer quand le pays ou la ville change
            this.$watch('shipping.country', () => this.calculateEstimatedShipping());
            this.$watch('shipping.city', () => this.calculateEstimatedShipping());
        }
    }
}


function couponInput() {
    return {
        couponCode: '',
        applying: false,
        couponError: '',
        async applyCoupon() {
            if (!this.couponCode.trim()) return;
            this.applying = true;
            this.couponError = '';
            try {
                const res = await fetch('{{ route("cart.coupon.apply") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: this.couponCode.trim().toUpperCase() })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    this.couponError = data.message || 'Code invalide.';
                }
            } catch(e) {
                this.couponError = 'Erreur réseau. Réessayez.';
            } finally {
                this.applying = false;
            }
        }
    }
}
</script>
@endpush
@endsection
