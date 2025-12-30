@extends('layouts.admin')

@section('title', 'Import / Export')
@section('page-title', 'Import / Export de données')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
            <div class="p-4 bg-blue-100 rounded-2xl">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-bold text-slate-900">{{ $stats['products_count'] }}</p>
                <p class="text-slate-500">Produits en base</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
            <div class="p-4 bg-green-100 rounded-2xl">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-bold text-slate-900">{{ $stats['categories_count'] }}</p>
                <p class="text-slate-500">Catégories en base</p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Export -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exporter les données
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 border border-slate-200 rounded-xl hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-slate-900">Exporter les produits</h3>
                            <p class="text-sm text-slate-500">Télécharger tous les produits au format CSV</p>
                        </div>
                        <a href="{{ route('admin.import-export.export-products') }}" 
                           class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                            Exporter
                        </a>
                    </div>
                </div>

                <div class="p-4 border border-slate-200 rounded-xl hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-slate-900">Exporter les catégories</h3>
                            <p class="text-sm text-slate-500">Télécharger toutes les catégories au format CSV</p>
                        </div>
                        <a href="{{ route('admin.import-export.export-categories') }}" 
                           class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                            Exporter
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Importer des produits
                </h2>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.import-export.import-products') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <label for="file" class="cursor-pointer">
                                <span class="text-blue-600 hover:underline font-medium">Choisir un fichier CSV</span>
                                <input type="file" id="file" name="file" accept=".csv,.txt" class="hidden" required>
                            </label>
                            <p class="text-sm text-slate-500 mt-1">ou glisser-déposer ici</p>
                            <p id="file-name" class="text-sm font-medium text-slate-900 mt-2 hidden"></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="update_existing" name="update_existing" value="1" 
                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="update_existing" class="text-sm text-slate-700">
                            Mettre à jour les produits existants (par SKU)
                        </label>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.import-export.template') }}" class="text-sm text-blue-600 hover:underline">
                            📥 Télécharger le modèle
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                            Importer
                        </button>
                    </div>
                </form>

                @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <h4 class="font-medium text-red-800 mb-2">Erreurs d'import :</h4>
                    <ul class="text-sm text-red-600 list-disc list-inside max-h-40 overflow-y-auto">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Instructions d'import</h3>
        <div class="prose prose-slate prose-sm max-w-none">
            <ul>
                <li>Le fichier doit être au format <strong>CSV</strong> avec le séparateur <strong>point-virgule (;)</strong></li>
                <li>La première ligne doit contenir les en-têtes des colonnes</li>
                <li>Les colonnes obligatoires sont : <code>name</code>, <code>sale_price</code></li>
                <li>Si une catégorie n'existe pas, elle sera créée automatiquement</li>
                <li>Le statut peut être : <code>active</code>, <code>draft</code>, ou <code>archived</code></li>
                <li>Pour les champs booléens (is_featured, is_new), utilisez : <code>1</code>, <code>oui</code>, <code>yes</code>, ou <code>true</code></li>
            </ul>
        </div>
    </div>
</div>

<script>
document.getElementById('file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const fileNameEl = document.getElementById('file-name');
    if (fileName) {
        fileNameEl.textContent = '📄 ' + fileName;
        fileNameEl.classList.remove('hidden');
    } else {
        fileNameEl.classList.add('hidden');
    }
});
</script>
@endsection

