@extends('layouts.admin')

@section('title', 'Canal WhatsApp Business')
@section('page-title', 'WhatsApp Business')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Statut du canal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Canal WhatsApp Business</h2>
                <p class="text-slate-500 text-sm mt-0.5">
                    @if($waNumber)
                        <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                            <span class="w-2 h-2 bg-green-500 rounded-full inline-block"></span>
                            Actif — {{ $waNumber }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-amber-600 font-medium">
                            <span class="w-2 h-2 bg-amber-500 rounded-full inline-block"></span>
                            Non configuré — ajoutez votre numéro dans les paramètres
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="grid sm:grid-cols-2 gap-4">
        {{-- Export catalogue --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </div>
            <h3 class="font-semibold text-slate-900 mb-1">Exporter le catalogue</h3>
            <p class="text-sm text-slate-500 mb-4">
                Téléchargez un fichier CSV de vos <strong>{{ $productsCount }} produits</strong> compatible avec Meta Commerce Manager et WhatsApp Catalog.
            </p>
            <a href="{{ route('admin.whatsapp.catalog') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Télécharger le catalogue CSV
            </a>
        </div>

        {{-- Lien WhatsApp boutique --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <h3 class="font-semibold text-slate-900 mb-1">Lien d'accueil WhatsApp</h3>
            <p class="text-sm text-slate-500 mb-4">
                Partagez ce lien sur vos réseaux sociaux pour rediriger vos clients vers votre boutique via WhatsApp.
            </p>
            @php
                $waClean = preg_replace('/\D/', '', $waNumber ?? '');
                $welcomeMsg = urlencode("Bonjour, je souhaite visiter votre boutique en ligne : " . route('shop.index'));
            @endphp
            @if($waClean)
                <div class="flex gap-2">
                    <input type="text" readonly
                           value="https://wa.me/{{ $waClean }}?text={{ $welcomeMsg }}"
                           class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-xs bg-slate-50 font-mono truncate"
                           onclick="this.select()">
                    <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='✓'"
                            class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors">
                        Copier
                    </button>
                </div>
            @else
                <a href="{{ route('admin.settings.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                    Configurer le numéro →
                </a>
            @endif
        </div>
    </div>

    {{-- Guide d'utilisation --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-900 mb-4">Guide d'intégration WhatsApp Catalog</h3>
        <ol class="space-y-3 text-sm text-slate-600">
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                <span>Téléchargez le fichier CSV de votre catalogue ci-dessus</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                <span>Ouvrez <strong>Meta Business Suite</strong> → Commerce → Catalogue → Sources de données</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">3</span>
                <span>Importez le fichier CSV — Meta vérifie et publie vos produits automatiquement</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">4</span>
                <span>Connectez le catalogue à votre compte WhatsApp Business dans l'application</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">5</span>
                <span>Vos clients peuvent désormais voir et commander vos produits directement via WhatsApp</span>
            </li>
        </ol>
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
            <strong>💡 Conseil :</strong> Mettez à jour le catalogue chaque semaine pour synchroniser les nouveaux produits et les prix.
        </div>
    </div>
</div>
@endsection
