@extends('layouts.admin')

@section('title', 'Attributs produits')
@section('page-title', 'Attributs produits')

@section('breadcrumbs')
<nav class="flex items-center gap-2 text-sm text-slate-500">
    <span class="text-slate-900 font-medium">Attributs</span>
</nav>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ===== Colonne gauche : créer un attribut ===== --}}
    <div class="space-y-6">

        {{-- Créer un attribut --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-4">Nouvel attribut</h2>
            <form method="POST" action="{{ route('admin.attributes.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Nom *</label>
                    <input type="text" name="name" required placeholder="Ex: Taille, Couleur, Matière..."
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                        <option value="size">Taille / Texte</option>
                        <option value="color">Couleur (avec code hex)</option>
                        <option value="text">Texte libre</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors text-sm">
                    Créer l'attribut
                </button>
            </form>
        </div>

        {{-- Aide --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-sm text-blue-800 space-y-2">
            <p class="font-semibold">Comment ça marche ?</p>
            <ul class="list-disc list-inside space-y-1 text-blue-700">
                <li><strong>Taille</strong> → âges (4 ans, 6 ans...), lettres (S/M/L), chiffres (38, 40...)</li>
                <li><strong>Couleur</strong> → avec code HEX pour afficher un pastille colorée</li>
                <li><strong>Texte</strong> → matière, style, etc.</li>
            </ul>
            <p class="text-blue-600 mt-2">Ces valeurs s'affichent dans la grille d'ajout de variantes.</p>
        </div>
    </div>

    {{-- ===== Colonne droite : liste des attributs et leurs valeurs ===== --}}
    <div class="lg:col-span-2 space-y-6">

        @forelse($attributes as $attribute)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
             x-data="{ showBulk: false }">

            {{-- En-tête attribut --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-3">
                    @if($attribute->type === 'color')
                        <span class="w-7 h-7 rounded-lg bg-gradient-to-br from-pink-400 to-red-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
                        </span>
                    @elseif($attribute->type === 'size')
                        <span class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-400 flex items-center justify-center text-white text-xs font-bold">T</span>
                    @else
                        <span class="w-7 h-7 rounded-lg bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white text-xs font-bold">A</span>
                    @endif
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $attribute->name }}</h3>
                        <span class="text-xs text-slate-400">{{ $attribute->values->count() }} valeur(s) · slug: {{ $attribute->slug }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showBulk=!showBulk"
                            class="px-3 py-1.5 text-xs font-medium bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors">
                        + Ajout multiple
                    </button>
                    <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}"
                          class="inline" onsubmit="return confirm('Supprimer l\'attribut {{ addslashes($attribute->name) }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Ajout multiple (textarea) --}}
            <div x-show="showBulk" x-collapse class="border-b border-slate-100 bg-amber-50/50 px-6 py-4">
                <form method="POST" action="{{ route('admin.attributes.values.bulk', $attribute) }}">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">
                        Saisir plusieurs valeurs (séparées par virgule, point-virgule ou retour à la ligne)
                    </label>
                    <textarea name="values" rows="3" required
                              placeholder="4 ans, 6 ans, 8 ans, 10 ans, 12 ans, 14 ans"
                              class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none"></textarea>
                    <div class="flex gap-2 mt-2">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg text-sm transition-colors">
                            Ajouter tout
                        </button>
                        <button type="button" @click="showBulk=false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

            {{-- Valeurs existantes + formulaire ajout unitaire --}}
            <div class="p-6">
                {{-- Liste des valeurs --}}
                @if($attribute->values->count() > 0)
                <div class="flex flex-wrap gap-2 mb-5">
                    @foreach($attribute->values as $val)
                    <span class="inline-flex items-center gap-1.5 pl-2 pr-1 py-1 bg-slate-100 rounded-lg text-sm group">
                        @if($val->color_code)
                            <span class="w-4 h-4 rounded-full border border-slate-300 flex-shrink-0" style="background:{{ $val->color_code }}"></span>
                        @endif
                        <span class="font-medium text-slate-800">{{ $val->value }}</span>
                        <form method="POST" action="{{ route('admin.attributes.values.destroy', [$attribute, $val]) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-0.5 text-slate-400 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100 rounded">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </span>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 italic mb-4">Aucune valeur. Ajoutez-en ci-dessous.</p>
                @endif

                {{-- Formulaire ajout unitaire --}}
                <form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}"
                      class="flex gap-2 items-end flex-wrap">
                    @csrf
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Nouvelle valeur</label>
                        <input type="text" name="value" required
                               placeholder="{{ $attribute->type === 'color' ? 'Ex: Rouge' : ($attribute->slug === 'taille' ? 'Ex: 4 ans' : 'Valeur...') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    @if($attribute->type === 'color')
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Code couleur</label>
                        <input type="color" name="color_code" value="#000000"
                               class="h-9 w-14 px-1 py-1 border border-slate-300 rounded-xl cursor-pointer">
                    </div>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl text-sm transition-colors h-9">
                        + Ajouter
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <p class="font-medium">Aucun attribut.</p>
            <p class="text-sm mt-1">Créez votre premier attribut (Taille, Couleur...) à gauche.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection
