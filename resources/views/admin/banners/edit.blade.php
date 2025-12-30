@extends('layouts.admin')

@section('title', 'Modifier la bannière')
@section('page-title', 'Modifier la bannière')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.banners.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>

        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette bannière ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
        </form>
    </div>

    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Image -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-6">Image de la bannière</h3>
            
            <div x-data="{ preview: '{{ $banner->image ? asset('storage/' . $banner->image) : '' }}' }">
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center"
                     :class="{ 'border-blue-500': preview }">
                    <template x-if="!preview">
                        <div class="space-y-2">
                            <svg class="w-12 h-12 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-slate-600">Glissez-déposez une image ou</p>
                            <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                                <span>Choisir un fichier</span>
                                <input type="file" name="image" accept="image/*" class="hidden"
                                       @change="preview = URL.createObjectURL($event.target.files[0])">
                            </label>
                        </div>
                    </template>
                    <template x-if="preview">
                        <div class="relative">
                            <img :src="preview" class="max-h-64 mx-auto rounded-lg">
                            <button type="button" @click="preview = ''" class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-slate-500 mt-2">Laissez vide pour conserver l'image actuelle</p>
            </div>
        </div>

        <!-- Contenu -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-6">Contenu</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-2">Titre (optionnel)</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="subtitle" class="block text-sm font-medium text-slate-700 mb-2">Sous-titre (optionnel)</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $banner->subtitle) }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="link" class="block text-sm font-medium text-slate-700 mb-2">Lien (optionnel)</label>
                    <input type="text" name="link" id="link" value="{{ old('link', $banner->link) }}" placeholder="https://..."
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="button_text" class="block text-sm font-medium text-slate-700 mb-2">Texte du bouton</label>
                    <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $banner->button_text) }}"
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
                    <select name="position" id="position" required
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @foreach($positions as $key => $label)
                            <option value="{{ $key }}" {{ old('position', $banner->position) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">Type *</label>
                    <select name="type" id="type" required
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $banner->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="order" class="block text-sm font-medium text-slate-700 mb-2">Ordre d'affichage</label>
                    <input type="number" name="order" id="order" value="{{ old('order', $banner->order) }}" min="0"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Statut</label>
                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-slate-700">Bannière active</label>
                    </div>
                </div>

                <div>
                    <label for="starts_at" class="block text-sm font-medium text-slate-700 mb-2">Date de début (optionnel)</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label for="ends_at" class="block text-sm font-medium text-slate-700 mb-2">Date de fin (optionnel)</label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i')) }}"
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
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection

