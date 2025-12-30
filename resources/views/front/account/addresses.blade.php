@extends('layouts.front')

@section('title', 'Mes adresses')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <a href="{{ route('account.dashboard') }}" class="hover:text-primary-600">Mon compte</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Mes adresses</span>
    </nav>

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            @include('front.account.partials.sidebar')

            <!-- Main Content -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-slate-900">Mes adresses</h1>
                    <button type="button" onclick="document.getElementById('addAddressModal').classList.remove('hidden')" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter une adresse
                    </button>
                </div>
                
                @php
                    $customer = auth()->user()->customer;
                    $addresses = $customer ? $customer->addresses : collect();
                @endphp

                @if($customer && $addresses->count() > 0)
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($addresses as $address)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative">
                            @if($address->is_default_shipping)
                                <span class="absolute top-4 right-4 inline-flex px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                    Adresse par défaut
                                </span>
                            @endif
                            
                            <div class="mb-4">
                                <p class="font-semibold text-slate-900">{{ $address->first_name }} {{ $address->last_name }}</p>
                                @if($address->company)
                                    <p class="text-slate-600">{{ $address->company }}</p>
                                @endif
                            </div>
                            
                            <div class="text-slate-600 text-sm space-y-1">
                                <p>{{ $address->address }}</p>
                                @if($address->address2)
                                    <p>{{ $address->address2 }}</p>
                                @endif
                                <p>{{ $address->postal_code }} {{ $address->city }}</p>
                                <p>{{ $address->country }}</p>
                                @if($address->phone)
                                    <p class="mt-2">📞 {{ $address->phone }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-4">
                                <button type="button" class="text-sm text-primary-600 hover:underline">Modifier</button>
                                @if(!$address->is_default_shipping)
                                    <button type="button" class="text-sm text-slate-600 hover:underline">Définir par défaut</button>
                                @endif
                                <button type="button" class="text-sm text-red-600 hover:underline">Supprimer</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl p-12 shadow-sm border border-slate-200 text-center">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900 mb-2">Aucune adresse enregistrée</h3>
                        <p class="text-slate-600 mb-6">Ajoutez une adresse pour faciliter vos prochaines commandes.</p>
                        <button type="button" onclick="document.getElementById('addAddressModal').classList.remove('hidden')"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter une adresse
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter une adresse -->
<div id="addAddressModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity" onclick="document.getElementById('addAddressModal').classList.add('hidden')"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900" id="modal-title">Ajouter une adresse</h3>
            </div>
            
            <form action="#" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                        <input type="text" name="first_name" required class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" name="last_name" required class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                    <input type="text" name="address" required class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Code postal</label>
                        <input type="text" name="postal_code" required class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ville</label>
                        <input type="text" name="city" required class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pays</label>
                    <input type="text" name="country" value="Côte d'Ivoire" required class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                    <input type="tel" name="phone" class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_default" id="is_default" class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                    <label for="is_default" class="ml-2 text-sm text-slate-600">Définir comme adresse par défaut</label>
                </div>
            </form>
            
            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addAddressModal').classList.add('hidden')"
                        class="px-4 py-2 text-slate-600 font-medium rounded-xl hover:bg-slate-100 transition-colors">
                    Annuler
                </button>
                <button type="submit" form="addAddressForm"
                        class="px-6 py-2 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

