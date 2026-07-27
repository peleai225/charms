@extends('layouts.admin')

@section('title', 'Nouveau client')
@section('page-title', 'Nouveau client')

@section('content')

@if ($errors->any())
<div class="mb-5 flex gap-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
    <div>
        <p class="text-[13px] font-semibold text-orange-800">Erreurs de validation</p>
        <ul class="mt-1 space-y-0.5">
            @foreach ($errors->all() as $error)
            <li class="text-[12px] text-orange-700">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('admin.customers.store') }}">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Informations personnelles --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations personnelles</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-medium text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('first_name') border-red-300 @enderror">
                        @error('first_name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('last_name') border-red-300 @enderror">
                        @error('last_name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('email') border-red-300 @enderror">
                        @error('email') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-medium text-gray-700 mb-1">Téléphone</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('phone') border-red-300 @enderror">
                        @error('phone') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="birth_date" class="block text-xs font-medium text-gray-700 mb-1">Date de naissance</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('birth_date') border-red-300 @enderror">
                        @error('birth_date') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="gender" class="block text-xs font-medium text-gray-700 mb-1">Genre</label>
                        <select id="gender" name="gender"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">— Non renseigné —</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Homme</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Femme</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Adresse principale --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Adresse principale</h2>
                <p class="text-[12px] text-gray-400 mb-4">Optionnel — peut être ajoutée ultérieurement.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="address_line1" class="block text-xs font-medium text-gray-700 mb-1">Rue / Adresse</label>
                        <input type="text" id="address_line1" name="address_line1" value="{{ old('address_line1') }}"
                            placeholder="12 rue de la Paix"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="address_city" class="block text-xs font-medium text-gray-700 mb-1">Ville</label>
                        <input type="text" id="address_city" name="address_city" value="{{ old('address_city') }}"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="address_postal_code" class="block text-xs font-medium text-gray-700 mb-1">Code postal</label>
                        <input type="text" id="address_postal_code" name="address_postal_code" value="{{ old('address_postal_code') }}"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="address_country" class="block text-xs font-medium text-gray-700 mb-1">Pays</label>
                        <select id="address_country" name="address_country"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="FR" {{ old('address_country', 'FR') === 'FR' ? 'selected' : '' }}>France</option>
                            <option value="BE" {{ old('address_country') === 'BE' ? 'selected' : '' }}>Belgique</option>
                            <option value="CH" {{ old('address_country') === 'CH' ? 'selected' : '' }}>Suisse</option>
                            <option value="LU" {{ old('address_country') === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                            <option value="DE" {{ old('address_country') === 'DE' ? 'selected' : '' }}>Allemagne</option>
                            <option value="ES" {{ old('address_country') === 'ES' ? 'selected' : '' }}>Espagne</option>
                            <option value="GB" {{ old('address_country') === 'GB' ? 'selected' : '' }}>Royaume-Uni</option>
                        </select>
                    </div>
                    <div>
                        <label for="address_phone" class="block text-xs font-medium text-gray-700 mb-1">Téléphone de livraison</label>
                        <input type="tel" id="address_phone" name="address_phone" value="{{ old('address_phone') }}"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
            </div>

            {{-- Notes internes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Notes internes</h2>
                <textarea id="notes" name="notes" rows="4"
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                    placeholder="Notes visibles uniquement par l'équipe admin...">{{ old('notes') }}</textarea>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Statut --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Statut du compte</h2>
                <select id="status" name="status" required
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
                <p class="mt-2 text-[11px] text-gray-400">Un client inactif ne pourra pas passer de commande.</p>

                <div class="mt-5 pt-4 border-t border-gray-100 space-y-2">
                    <button type="submit"
                        class="w-full h-9 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors">
                        Créer le client
                    </button>
                    <a href="{{ route('admin.customers.index') }}"
                        class="w-full h-9 inline-flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </a>
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                <p class="text-[12px] font-semibold text-orange-800 mb-1">Compte en ligne</p>
                <p class="text-[12px] text-orange-700">
                    Pour lier ce client à un compte en ligne, créez d'abord l'entrée client puis associez-le depuis la fiche client.
                </p>
            </div>

        </div>
    </div>
</form>

@endsection
