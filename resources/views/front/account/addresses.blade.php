@extends('layouts.front')

@section('title', 'Mes adresses')

@section('content')

<div class="bg-slate-50 border-b border-slate-200 py-8">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-2 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span>/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-slate-700 transition-colors">Mon compte</a>
            <span>/</span>
            <span class="text-slate-700">Mes adresses</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Mes adresses</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8" x-data="{ showModal: false, editId: null }">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1 min-w-0">

                @if (session('success'))
                <div class="mb-5 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if (session('error'))
                <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                    {{ session('error') }}
                </div>
                @endif

                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-semibold text-slate-900">Adresses de livraison</h2>
                    <button type="button" @click="showModal = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter
                    </button>
                </div>

                @if($addresses->count() > 0)
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($addresses as $address)
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 relative hover:border-blue-200 transition-colors">
                        @if($address->is_default)
                        <span class="absolute top-4 right-4 inline-flex px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                            Par défaut
                        </span>
                        @endif

                        <div class="mb-3 pr-16">
                            <p class="font-semibold text-slate-900">{{ $address->first_name }} {{ $address->last_name }}</p>
                        </div>
                        <div class="text-sm text-slate-600 space-y-0.5">
                            <p>{{ $address->address_line1 }}</p>
                            @if($address->address_line2)
                                <p>{{ $address->address_line2 }}</p>
                            @endif
                            <p>{{ $address->postal_code }} {{ $address->city }}</p>
                            <p>{{ $address->country }}</p>
                            @if($address->phone)
                                <p class="mt-1 text-slate-500">{{ $address->phone }}</p>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-3">
                            <button type="button" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Modifier
                            </button>
                            @if(!$address->is_default)
                            <form action="{{ route('account.addresses.store') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PATCH">
                                <input type="hidden" name="address_id" value="{{ $address->id }}">
                                <input type="hidden" name="set_default" value="1">
                                <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 font-medium">
                                    Par défaut
                                </button>
                            </form>
                            <form action="{{ route('account.addresses.store') }}" method="POST" class="inline ml-auto"
                                  onsubmit="return confirm('Supprimer cette adresse ?')">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="address_id" value="{{ $address->id }}">
                                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                                    Supprimer
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    {{-- Card ajouter --}}
                    <button type="button" @click="showModal = true"
                        class="bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 p-5 flex flex-col items-center justify-center gap-2 hover:border-blue-400 hover:bg-blue-50 transition-colors text-slate-400 hover:text-blue-600 min-h-[140px]">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-sm font-medium">Nouvelle adresse</span>
                    </button>
                </div>
                @else
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Aucune adresse enregistrée</h3>
                    <p class="text-slate-500 text-sm mb-5">Ajoutez une adresse pour faciliter vos prochaines commandes.</p>
                    <button type="button" @click="showModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter une adresse
                    </button>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Modal ajout adresse --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Nouvelle adresse</h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="addAddressForm" action="{{ route('account.addresses.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Adresse <span class="text-red-500">*</span></label>
                    <input type="text" name="address" required
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        placeholder="Rue, quartier, numéro...">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Code postal <span class="text-red-500">*</span></label>
                        <input type="text" name="postal_code" required value="00225"
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ville <span class="text-red-500">*</span></label>
                        <input type="text" name="city" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            placeholder="Abidjan">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Pays <span class="text-red-500">*</span></label>
                    <select name="country" required
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white">
                        <option value="CI" selected>Côte d'Ivoire</option>
                        <option value="SN">Sénégal</option>
                        <option value="ML">Mali</option>
                        <option value="BF">Burkina Faso</option>
                        <option value="GN">Guinée</option>
                        <option value="FR">France</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                    <input type="tel" name="phone"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        placeholder="+225 07 00 00 00 00">
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1"
                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-600">Définir comme adresse par défaut</span>
                </label>
            </form>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showModal = false"
                    class="px-4 py-2 text-sm text-slate-600 font-medium rounded-xl hover:bg-slate-100 transition-colors">
                    Annuler
                </button>
                <button type="submit" form="addAddressForm"
                    class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
