@extends('layouts.front')

@section('title', 'Commander')

@section('content')

<div class="bg-white border-b border-slate-200">
    <div class="container mx-auto px-4 py-4">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-slate-500 flex items-center gap-1.5 mb-4">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('cart.index') }}" class="hover:text-slate-700 transition-colors">Panier</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Commander</span>
        </nav>

        {{-- Stepper visuel --}}
        <div class="flex items-center gap-0 max-w-sm">
            <div class="flex items-center gap-1.5">
                <div class="w-6 h-6 rounded-full bg-primary-600 text-white text-xs flex items-center justify-center font-bold flex-shrink-0">1</div>
                <span class="text-sm font-semibold text-primary-600 hidden sm:inline">Livraison</span>
            </div>
            <div class="flex-1 h-px bg-slate-200 mx-2"></div>
            <div class="flex items-center gap-1.5">
                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 text-xs flex items-center justify-center font-bold flex-shrink-0">2</div>
                <span class="text-sm text-slate-400 hidden sm:inline">Paiement</span>
            </div>
            <div class="flex-1 h-px bg-slate-200 mx-2"></div>
            <div class="flex items-center gap-1.5">
                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 text-xs flex items-center justify-center font-bold flex-shrink-0">3</div>
                <span class="text-sm text-slate-400 hidden sm:inline">Confirmation</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-8">
<div class="container mx-auto px-4 pb-8">

    @if(session('error'))
    <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 max-w-3xl">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}"
          x-data="checkoutForm()"
          x-init="init()"
          @submit="isSubmitting = true"
          class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        @csrf

        {{-- ===== Colonne formulaire (60%) ===== --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- Bloc 1 : Informations de livraison --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Informations de livraison
                </h2>

                {{-- Adresses sauvegardées --}}
                @if($addresses->count() > 0)
                <div class="mb-5" x-data="{ showNew: false }">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Mes adresses enregistrées</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        @foreach($addresses as $addr)
                        <label class="relative border-2 rounded-lg p-4 cursor-pointer transition-colors"
                            :class="selectedAddress === {{ $addr->id }} ? 'border-primary-600 bg-primary-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="saved_address_id" value="{{ $addr->id }}" class="sr-only"
                                @click="selectAddress({{ json_encode($addr) }}); selectedAddress = {{ $addr->id }}; showNew = false">
                            <p class="font-medium text-sm text-slate-900">{{ $addr->full_name ?? ($addr->first_name . ' ' . $addr->last_name) }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $addr->address_line1 }}</p>
                            <p class="text-xs text-slate-500">{{ $addr->city }}@if($addr->district), {{ $addr->district }}@endif</p>
                            @if($addr->is_default)
                            <span class="absolute top-2 right-2 text-[10px] bg-primary-100 text-primary-600 px-1.5 py-0.5 rounded font-medium">Par défaut</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    <button type="button" @click="showNew = !showNew; selectedAddress = null"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="showNew ? 'Annuler' : 'Nouvelle adresse'"></span>
                    </button>

                    {{-- Nouvelle adresse — les inputs UI n'ont pas de name, ils alimentent les hidden via x-model --}}
                    <div x-show="showNew" x-transition class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Prénom *</label>
                            <input type="text" x-model="shipping.first_name"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                            <input type="text" x-model="shipping.last_name"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse *</label>
                            <input type="text" x-model="shipping.address" placeholder="Quartier, rue..."
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ville *</label>
                            <input type="text" x-model="shipping.city" @input.debounce.400ms="calcShipping()"
                                placeholder="Ex: Abidjan" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pays *</label>
                            <select x-model="shipping.country" @change="calcShipping()"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                                <option value="CI">Côte d'Ivoire</option>
                                <option value="SN">Sénégal</option>
                                <option value="ML">Mali</option>
                                <option value="BF">Burkina Faso</option>
                                <option value="TG">Togo</option>
                                <option value="BJ">Bénin</option>
                                <option value="FR">France</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Hidden inputs — seule source de vérité qui soumet les données --}}
                <input type="hidden" name="shipping_first_name" x-model="shipping.first_name">
                <input type="hidden" name="shipping_last_name" x-model="shipping.last_name">
                <input type="hidden" name="shipping_address" x-model="shipping.address">
                <input type="hidden" name="shipping_address_2" x-model="shipping.address_2">
                <input type="hidden" name="shipping_city" x-model="shipping.city">
                <input type="hidden" name="shipping_country" x-model="shipping.country">
                <input type="hidden" name="shipping_postal_code" x-model="shipping.postal_code">

                @else
                {{-- Champs d'adresse (toujours affichés si pas d'adresses sauvegardées) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="shipping_first_name" class="block text-sm font-medium text-slate-700 mb-1">Prénom *</label>
                        <input type="text" name="shipping_first_name" id="shipping_first_name" required
                            value="{{ old('shipping_first_name', $customer?->first_name) }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        @error('shipping_first_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="shipping_last_name" class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                        <input type="text" name="shipping_last_name" id="shipping_last_name" required
                            value="{{ old('shipping_last_name', $customer?->last_name) }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        @error('shipping_last_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">
                            Téléphone * <span class="text-xs text-slate-400 font-normal">format +225</span>
                        </label>
                        <input type="tel" name="phone" id="phone" required
                            value="{{ old('phone', $customer?->phone) }}"
                            placeholder="+225 07 XX XX XX XX"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-xs text-slate-400 font-normal">(optionnel)</span></label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', $customer?->email ?? auth()->user()?->email) }}"
                            placeholder="Pour recevoir la confirmation"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="shipping_address" class="block text-sm font-medium text-slate-700 mb-1">Adresse / Quartier *</label>
                        <div class="flex gap-2">
                            <input type="text" name="shipping_address" id="shipping_address" required
                                x-model="shipping.address"
                                placeholder="Ex: Cocody Riviera 3, près de la pharmacie"
                                class="flex-1 px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                            <button type="button" @click="detectLocation()" :disabled="isGpsLoading"
                                title="Détecter ma position GPS"
                                class="flex-shrink-0 px-3 py-2.5 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-50 transition-colors">
                                <svg x-show="!isGpsLoading" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="isGpsLoading" class="w-4 h-4 text-slate-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </button>
                        </div>
                        @error('shipping_address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="shipping_address_2" class="block text-sm font-medium text-slate-700 mb-1">Complément <span class="text-xs text-slate-400 font-normal">(optionnel)</span></label>
                        <input type="text" name="shipping_address_2" id="shipping_address_2"
                            x-model="shipping.address_2"
                            placeholder="Bâtiment, point de repère..."
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                    </div>
                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-slate-700 mb-1">Ville / Commune *</label>
                        <input type="text" name="shipping_city" id="shipping_city" required
                            x-model="shipping.city" @input.debounce.400ms="calcShipping()"
                            placeholder="Ex: Abidjan"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        @error('shipping_city')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-slate-700 mb-1">Pays *</label>
                        <select name="shipping_country" id="shipping_country" required
                            x-model="shipping.country" @change="calcShipping()"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                            <option value="CI">Côte d'Ivoire</option>
                            <option value="SN">Sénégal</option>
                            <option value="ML">Mali</option>
                            <option value="BF">Burkina Faso</option>
                            <option value="TG">Togo</option>
                            <option value="BJ">Bénin</option>
                            <option value="GN">Guinée</option>
                            <option value="CM">Cameroun</option>
                            <option value="FR">France</option>
                            <option value="BE">Belgique</option>
                            <option value="CH">Suisse</option>
                        </select>
                    </div>
                </div>
                @endif

                <input type="hidden" name="same_billing" value="1">
                @if(auth()->check())
                <input type="hidden" name="save_address" value="1">
                @endif
            </div>

            {{-- Téléphone + email si adresses sauvegardées --}}
            @if($addresses->count() > 0)
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Contact
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Téléphone *</label>
                        <input type="tel" name="phone" id="phone" required
                            value="{{ old('phone', $customer?->phone) }}"
                            placeholder="+225 07 XX XX XX XX"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                        @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-xs text-slate-400 font-normal">(optionnel)</span></label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', $customer?->email ?? auth()->user()?->email) }}"
                            placeholder="confirmation@email.com"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors bg-white text-sm">
                    </div>
                </div>
            </div>
            @endif

            {{-- Bloc 2 : Mode de paiement --}}
            {{-- uiMethod = affichage Alpine ; payment_method hidden = valeur soumise --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6" x-data="{ uiMethod: paymentMethod }">
                <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Mode de paiement
                </h2>

                {{-- Hidden input soumis au contrôleur — whatsapp mappe sur cod --}}
                <input type="hidden" name="payment_method"
                    :value="uiMethod === 'whatsapp' ? 'cod' : uiMethod">

                <div class="space-y-3">

                    {{-- WhatsApp — option principale CI --}}
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-colors select-none"
                        :class="uiMethod === 'whatsapp' ? 'border-[#25D366] bg-green-50' : 'border-slate-200 hover:border-slate-300'"
                        @click="uiMethod = 'whatsapp'; paymentMethod = 'whatsapp'">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                :class="uiMethod === 'whatsapp' ? 'border-[#25D366]' : 'border-slate-300'">
                                <div class="w-2.5 h-2.5 rounded-full transition-colors" :class="uiMethod === 'whatsapp' ? 'bg-[#25D366]' : ''"></div>
                            </div>
                            <div class="w-11 h-11 bg-[#25D366] rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900 text-sm">Commander via WhatsApp</p>
                                <p class="text-xs text-slate-500">Finalisez la commande sur WhatsApp — réponse rapide</p>
                            </div>
                            <span class="ml-auto text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold flex-shrink-0">Recommandé</span>
                        </div>
                    </label>

                    {{-- Mobile Money --}}
                    @if(($settings['payment_moneyfusion_enabled'] ?? '0') === '1')
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-colors select-none"
                        :class="uiMethod === 'moneyfusion' ? 'border-primary-600 bg-primary-50' : 'border-slate-200 hover:border-slate-300'"
                        @click="uiMethod = 'moneyfusion'; paymentMethod = 'moneyfusion'">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                :class="uiMethod === 'moneyfusion' ? 'border-primary-600' : 'border-slate-300'">
                                <div class="w-2.5 h-2.5 rounded-full" :class="uiMethod === 'moneyfusion' ? 'bg-primary-600' : ''"></div>
                            </div>
                            <div class="w-11 h-11 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0 border border-slate-200">
                                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900 text-sm">Mobile Money</p>
                                <p class="text-xs text-slate-500">Wave, Orange Money, MTN, Moov</p>
                            </div>
                        </div>
                    </label>
                    @endif

                    {{-- Paiement à la livraison --}}
                    @if(($settings['payment_cod_enabled'] ?? '1') === '1')
                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-colors select-none"
                        :class="uiMethod === 'cod' ? 'border-primary-600 bg-primary-50' : 'border-slate-200 hover:border-slate-300'"
                        @click="uiMethod = 'cod'; paymentMethod = 'cod'">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                :class="uiMethod === 'cod' ? 'border-primary-600' : 'border-slate-300'">
                                <div class="w-2.5 h-2.5 rounded-full" :class="uiMethod === 'cod' ? 'bg-primary-600' : ''"></div>
                            </div>
                            <div class="w-11 h-11 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 border border-amber-100">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900 text-sm">Paiement à la livraison</p>
                                <p class="text-xs text-slate-500">Payez en espèces à la réception</p>
                            </div>
                        </div>
                    </label>
                    @endif

                </div>
                @error('payment_method')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Bloc 3 : Notes --}}
            <details class="group bg-white rounded-xl border border-slate-200">
                <summary class="px-6 py-4 text-sm text-slate-600 cursor-pointer hover:text-slate-900 transition-colors list-none flex items-center gap-2 select-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    Ajouter une note pour la livraison
                    <svg class="w-4 h-4 text-slate-400 ml-auto group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-6 pb-5">
                    <textarea name="notes" rows="3" placeholder="Instructions spéciales, point de repère..."
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-colors resize-none bg-white">{{ old('notes') }}</textarea>
                </div>
            </details>

        </div>

        {{-- ===== Récapitulatif commande (40%) ===== --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 p-5 sticky top-24">
                <h2 class="text-base font-semibold text-slate-900 mb-4">
                    Ma commande ({{ $cart->items_count }})
                </h2>

                <div class="space-y-3 max-h-60 overflow-y-auto mb-4 pr-1">
                    @foreach($cart->items as $item)
                    <div class="flex gap-3 items-start">
                        <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0 border border-slate-100">
                            @if($item->variant?->image)
                                <img src="{{ asset('storage/' . $item->variant->image) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @elseif($item->product->images->where('is_primary', true)->first())
                                <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 truncate leading-tight">{{ $item->product->name }}</p>
                            @if($item->variant)
                                <p class="text-xs text-slate-500">{{ $item->variant->label ?? $item->variant->name }}</p>
                            @endif
                            <p class="text-xs text-slate-500">{{ $item->quantity }} × {{ format_price($item->unit_price) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-slate-900 flex-shrink-0">{{ format_price($item->unit_price * $item->quantity) }}</p>
                    </div>
                    @endforeach
                </div>

                @if($cart->coupon_code)
                <div class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg mb-3 text-sm">
                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span class="font-semibold text-green-700">{{ $cart->coupon_code }}</span>
                    <span class="text-green-600 text-xs">appliqué</span>
                </div>
                @endif

                <div class="border-t border-slate-100 pt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Sous-total</span>
                        <span class="font-medium text-slate-900">{{ format_price($cart->subtotal) }}</span>
                    </div>
                    @if($cart->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Réduction</span>
                        <span>-{{ format_price($cart->discount_amount) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-600">Livraison</span>
                        <span class="text-slate-500 text-xs" x-text="shippingText">Calculée selon destination</span>
                    </div>
                    <div class="flex justify-between items-baseline border-t border-slate-100 pt-2">
                        <span class="font-bold text-slate-900">Total</span>
                        <span class="font-bold text-lg text-slate-900" x-text="fmtPrice(estimatedTotal)">{{ format_price($cart->total) }}</span>
                    </div>
                </div>

                <button type="submit" :disabled="isSubmitting"
                    class="mt-5 w-full py-4 px-6 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2 text-sm">
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="isSubmitting ? 'Traitement...' : (paymentMethod === 'cod' ? 'Confirmer la commande' : (paymentMethod === 'whatsapp' ? 'Commander via WhatsApp' : 'Payer maintenant'))">Confirmer la commande</span>
                </button>

                <div class="mt-3 flex items-center justify-center gap-1.5 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Paiement 100% sécurisé
                </div>
            </div>
        </div>

    </form>
</div>
</div>

@push('scripts')
<script>
// Pixels — InitiateCheckout
document.addEventListener('DOMContentLoaded', function() {
    var _t = {{ $cart->total }}, _n = {{ $cart->items_count }};
    if (window.trackPixel) window.trackPixel.initiateCheckout(_t, _n);
    if (window.trackGA4) window.trackGA4.beginCheckout(_t);
    if (window.ttq) window.ttq.track('InitiateCheckout', { value: _t, currency: 'XOF' });
});

function checkoutForm() {
    return {
        isSubmitting: false, isGpsLoading: false, isCalcShipping: false,
        paymentMethod: '{{ (($settings['payment_moneyfusion_enabled'] ?? '0') === '1') ? 'moneyfusion' : 'cod' }}',
        shippingText: 'Calculée selon destination',
        estimatedTotal: {{ $cart->total }},
        selectedAddress: null,
        shipping: {
            first_name: '{{ addslashes(old('shipping_first_name', $customer?->first_name ?? '')) }}',
            last_name:  '{{ addslashes(old('shipping_last_name', $customer?->last_name ?? '')) }}',
            address: '', address_2: '',
            city: '{{ addslashes(old('shipping_city', '')) }}',
            country: '{{ old('shipping_country', 'CI') }}',
            postal_code: '',
        },

        fmtPrice(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' F CFA'; },

        async calcShipping() {
            const subtotal = {{ $cart->subtotal }};
            const discount = {{ $cart->discount_amount }};
            if (!this.shipping.country) return;
            this.isCalcShipping = true;
            try {
                const res = await fetch('{{ route("api.shipping-cost") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ country: this.shipping.country, city: this.shipping.city, cart_subtotal: subtotal })
                });
                const data = await res.json();
                const cost = data.shipping_cost || 0;
                this.estimatedTotal = subtotal - discount + cost;
                this.shippingText = cost === 0 ? 'Gratuite' : (data.formatted || this.fmtPrice(cost));
            } catch { this.shippingText = 'Selon destination'; }
            finally { this.isCalcShipping = false; }
        },

        selectAddress(addr) {
            this.shipping.first_name = addr.first_name || '';
            this.shipping.last_name  = addr.last_name || '';
            this.shipping.address    = addr.address_line1 || addr.address || '';
            this.shipping.address_2  = addr.address_line2 || '';
            this.shipping.city       = addr.city || '';
            this.shipping.country    = addr.country || 'CI';
            this.shipping.postal_code = addr.postal_code || '';
            this.calcShipping();
        },

        async detectLocation() {
            if (!navigator.geolocation) { alert('Géolocalisation non supportée.'); return; }
            this.isGpsLoading = true;
            navigator.geolocation.getCurrentPosition(async (pos) => {
                try {
                    const { latitude: lat, longitude: lng } = pos.coords;
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                        headers: { 'Accept-Language': 'fr' }
                    });
                    const data = await res.json();
                    const a = data.address || {};
                    const addr = [a.road, a.suburb, a.neighbourhood].filter(Boolean).join(', ');
                    if (addr) {
                        this.shipping.address = addr;
                        const addrEl = document.getElementById('shipping_address');
                        if (addrEl) addrEl.value = addr;
                    }
                    const city = a.city || a.town || a.village || a.county || '';
                    if (city) {
                        this.shipping.city = city;
                        const cityEl = document.getElementById('shipping_city');
                        if (cityEl) cityEl.value = city;
                    }
                    this.calcShipping();
                } catch { alert('Impossible de détecter la position.'); }
                finally { this.isGpsLoading = false; }
            }, () => { alert('Accès à la position refusé.'); this.isGpsLoading = false; });
        },

        init() {
            if (this.shipping.country) this.calcShipping();
            this.$watch('shipping.country', () => this.calcShipping());
        }
    };
}
</script>
@endpush
@endsection
