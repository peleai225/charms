@extends('layouts.admin')

@section('title', 'Import / Export')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Import / Export de données</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Importez et exportez vos données catalogue</p>
    </div>

    {{-- KPI strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 divide-x divide-gray-100">
            <div class="p-5 flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['products_count'] }}</p>
                    <p class="text-[12px] text-gray-500">Produits en base</p>
                </div>
            </div>
            <div class="p-5 flex items-center gap-4">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['categories_count'] }}</p>
                    <p class="text-[12px] text-gray-500">Catégories en base</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Export --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <h2 class="text-[14px] font-semibold text-gray-900">Exporter les données</h2>
            </div>
            <div class="p-5 space-y-3">
                <div class="p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[13px] font-medium text-gray-900">Exporter les produits</h3>
                            <p class="text-[12px] text-gray-500 mt-0.5">Télécharger tous les produits au format CSV</p>
                        </div>
                        <a href="{{ route('admin.import-export.export-products') }}"
                           class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Exporter
                        </a>
                    </div>
                </div>

                <div class="p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[13px] font-medium text-gray-900">Exporter les catégories</h3>
                            <p class="text-[12px] text-gray-500 mt-0.5">Télécharger toutes les catégories au format CSV</p>
                        </div>
                        <a href="{{ route('admin.import-export.export-categories') }}"
                           class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Exporter
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Import --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <h2 class="text-[14px] font-semibold text-gray-900">Importer des produits</h2>
            </div>
            <div class="p-5">
                <form action="{{ route('admin.import-export.import-products') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="p-5 bg-gray-50 rounded-xl border border-dashed border-gray-300 text-center">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <label for="file" class="cursor-pointer">
                            <span class="text-[13px] text-blue-600 hover:underline font-medium">Choisir un fichier CSV</span>
                            <input type="file" id="file" name="file" accept=".csv,.txt" class="hidden" required>
                        </label>
                        <p class="text-[12px] text-gray-400 mt-1">ou glisser-déposer ici</p>
                        <p id="file-name" class="text-[13px] font-medium text-gray-900 mt-2 hidden"></p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="update_existing" name="update_existing" value="1"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="update_existing" class="text-[13px] text-gray-700">
                            Mettre à jour les produits existants (par SKU)
                        </label>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.import-export.template') }}" class="text-[13px] text-blue-600 hover:underline">
                            Télécharger le modèle
                        </a>
                        <button type="submit" class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">
                            Importer
                        </button>
                    </div>
                </form>

                @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="mt-4 p-4 bg-red-50 border border-red-100 rounded-xl">
                    <h4 class="text-[13px] font-medium text-red-800 mb-2">Erreurs d'import :</h4>
                    <ul class="text-[12px] text-red-600 list-disc list-inside max-h-40 overflow-y-auto space-y-1">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Instructions d'import</h3>
        <ul class="space-y-2 text-[13px] text-gray-600">
            <li class="flex items-start gap-2"><span class="text-gray-400 mt-1">•</span> Le fichier doit être au format <strong>CSV</strong> avec le séparateur <strong>point-virgule (;)</strong></li>
            <li class="flex items-start gap-2"><span class="text-gray-400 mt-1">•</span> La première ligne doit contenir les en-têtes des colonnes</li>
            <li class="flex items-start gap-2"><span class="text-gray-400 mt-1">•</span> Les colonnes obligatoires sont : <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">name</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">sale_price</code></li>
            <li class="flex items-start gap-2"><span class="text-gray-400 mt-1">•</span> Si une catégorie n'existe pas, elle sera créée automatiquement</li>
            <li class="flex items-start gap-2"><span class="text-gray-400 mt-1">•</span> Le statut peut être : <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">active</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">draft</code>, ou <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">archived</code></li>
            <li class="flex items-start gap-2"><span class="text-gray-400 mt-1">•</span> Pour les champs booléens, utilisez : <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">1</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">oui</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">yes</code>, ou <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[12px]">true</code></li>
        </ul>
    </div>
</div>

<script>
document.getElementById('file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const fileNameEl = document.getElementById('file-name');
    if (fileName) {
        fileNameEl.textContent = fileName;
        fileNameEl.classList.remove('hidden');
    } else {
        fileNameEl.classList.add('hidden');
    }
});
</script>
@endsection
