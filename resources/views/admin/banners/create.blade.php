@extends('layouts.admin')

@section('title', 'Nouvelle bannière')
@section('page-title', 'Nouvelle bannière')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.banners.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
          x-data="{ 
              bannerType: '{{ old('type', 'hero') }}', 
              bannerPosition: '{{ old('position', 'home_hero') }}',
              preview: null 
          }">
        @csrf

        <!-- Aide pour barre d'annonce -->
        <div x-show="bannerPosition === 'announcement_bar'" 
             x-transition
             class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="font-medium text-amber-800">Barre d'annonce</h4>
                <p class="text-sm text-amber-700">Pour une barre d'annonce (ex: "🎉 Livraison gratuite dès 50 000 F CFA"), remplissez uniquement le <strong>Titre</strong>. L'image n'est pas obligatoire pour ce type de bannière.</p>
            </div>
        </div>

        <!-- Aide pour popup -->
        <div x-show="bannerPosition === 'popup_center'" 
             x-transition
             class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-indigo-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <div>
                <h4 class="font-medium text-indigo-800">Popup centre écran</h4>
                <p class="text-sm text-indigo-700">S'affiche au centre de l'écran après un court délai. L'image est optionnelle — le titre et le sous-titre suffisent pour une annonce textuelle élégante.</p>
            </div>
        </div>

        <!-- Image (masquée pour barre d'annonce uniquement) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6"
             x-show="bannerPosition !== 'announcement_bar'">
            <h3 class="text-lg font-semibold text-slate-900 mb-6">Image de la bannière</h3>
            
            <div>
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center"
                     :class="{ 'border-blue-500': preview }">
                    {{-- x-show au lieu de x-if pour garder le champ fichier dans le DOM lors de la prévisualisation --}}
                    <div x-show="!preview" x-cloak class="space-y-2">
                        <svg class="w-12 h-12 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-slate-600">Glissez-déposez une image ou</p>
                        <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                            <span>Choisir un fichier</span>
                            <input type="file" name="image" accept="image/*" class="hidden" x-ref="fileInput"
                                   :required="bannerPosition !== 'announcement_bar' && bannerPosition !== 'popup_center'"
                                   @change="preview = URL.createObjectURL($event.target.files[0])">
                        </label>
                        <p class="text-xs text-slate-500">PNG, JPG, WEBP jusqu'à 5MB. Taille recommandée: 1920x600px</p>
                    </div>
                    <div x-show="preview" x-cloak class="relative">
                        <img :src="preview" class="max-h-64 mx-auto rounded-lg">
                        <button type="button" @click="preview = null; $refs.fileInput && ($refs.fileInput.value = '')" class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Contenu -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-6">Contenu</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-2">Titre (optionnel)</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="subtitle" class="block text-sm font-medium text-slate-700 mb-2">Sous-titre (optionnel)</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="link" class="block text-sm font-medium text-slate-700 mb-2">Lien (optionnel)</label>
                    <input type="text" name="link" id="link" value="{{ old('link') }}" placeholder="https://..."
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="button_text" class="block text-sm font-medium text-slate-700 mb-2">Texte du bouton</label>
                    <input type="text" name="button_text" id="button_text" value="{{ old('button_text', 'Découvrir') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Paramètres -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-6">Paramètres</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="position" class="block text-sm font-medium text-slate-700 mb-2">Position *</label>
                    <select name="position" id="position" required x-model="bannerPosition"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @foreach($positions as $key => $label)
                            <option value="{{ $key }}" {{ old('position') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">
                        <span x-show="bannerPosition === 'announcement_bar'" class="text-amber-600 font-medium">
                            ⚡ Affichée tout en haut du site, idéal pour les promos
                        </span>
                    </p>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">Type *</label>
                    <select name="type" id="type" required x-model="bannerType"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="order" class="block text-sm font-medium text-slate-700 mb-2">Ordre d'affichage</label>
                    <input type="number" name="order" id="order" value="{{ old('order', 0) }}" min="0"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Statut</label>
                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-slate-700">Bannière active</label>
                    </div>
                </div>

                <div>
                    <label for="starts_at" class="block text-sm font-medium text-slate-700 mb-2">Date de début (optionnel)</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="ends_at" class="block text-sm font-medium text-slate-700 mb-2">Date de fin (optionnel)</label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.banners.index') }}" class="px-6 py-3 text-slate-600 hover:text-slate-900 font-medium">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Créer la bannière
            </button>
        </div>
    </form>
</div>
@endsection

