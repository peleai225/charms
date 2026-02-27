@extends('layouts.admin')

@section('title', 'Guide - Configuration imprimante caisse')
@section('page-title', 'Configuration imprimante caisse POS')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-6">Prérequis</h2>
        <ul class="list-disc list-inside space-y-2 text-slate-700">
            <li><strong>Imprimante thermique</strong> compatible (58 mm ou 80 mm)</li>
            <li><strong>Pilote</strong> de l'imprimante installé sur l'ordinateur de caisse</li>
            <li><strong>Navigateur</strong> Chrome ou Edge (recommandé)</li>
        </ul>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Étape 1 : Installer l'imprimante</h2>
        <ol class="list-decimal list-inside space-y-2 text-slate-700">
            <li>Connectez l'imprimante thermique (USB ou réseau)</li>
            <li>Installez le pilote fourni par le fabricant</li>
            <li>Vérifiez que l'imprimante apparaît dans <strong>Paramètres Windows → Périphériques → Imprimantes</strong></li>
        </ol>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Étape 2 : Définir l'imprimante par défaut</h2>
        <ol class="list-decimal list-inside space-y-2 text-slate-700">
            <li>Ouvrez <strong>Paramètres Windows</strong> (ou Panneau de configuration)</li>
            <li>Allez dans <strong>Périphériques → Imprimantes et scanners</strong></li>
            <li>Cliquez sur votre <strong>imprimante thermique</strong></li>
            <li>Sélectionnez <strong>Gérer</strong> puis <strong>Définir par défaut</strong></li>
        </ol>
        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <p class="text-amber-800 font-medium">Important :</p>
            <p class="text-amber-700 text-sm mt-1">L'imprimante thermique doit être l'imprimante par défaut du système pour un flux rapide.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Étape 3 : Configurer dans l'admin</h2>
        <ol class="list-decimal list-inside space-y-2 text-slate-700">
            <li>Connectez-vous à l'<strong>administration</strong></li>
            <li>Allez dans <strong>Paramètres → Général</strong></li>
            <li>Section <strong>Caisse POS</strong> : cochez « Ouvrir le reçu et lancer l'impression après validation de vente »</li>
            <li>Cliquez sur <strong>Enregistrer</strong></li>
        </ol>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Utilisation lors d'une vente</h2>
        <ol class="list-decimal list-inside space-y-2 text-slate-700">
            <li>Scannez ou ajoutez les produits au panier</li>
            <li>Choisissez le mode de paiement (Espèces, Carte, Mobile Money)</li>
            <li>Cliquez sur <strong>Valider la vente</strong></li>
            <li>Le reçu s'ouvre dans une nouvelle fenêtre</li>
            <li>La boîte de dialogue d'impression apparaît</li>
            <li><strong>Appuyez sur Entrée</strong> (ou cliquez sur Imprimer) → le reçu s'imprime</li>
        </ol>
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
            <p class="text-green-800 font-medium">Astuce :</p>
            <p class="text-green-700 text-sm mt-1">Si l'imprimante thermique est définie par défaut, un simple <kbd class="px-1.5 py-0.5 bg-green-200 rounded">Entrée</kbd> suffit à chaque vente. Le flux devient : Valider → Entrée → Reçu imprimé.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Dépannage</h2>
        <div class="space-y-4">
            <div>
                <h3 class="font-semibold text-slate-800">Le reçu ne s'ouvre pas</h3>
                <ul class="list-disc list-inside text-slate-600 text-sm mt-1">
                    <li>Vérifiez que les <strong>fenêtres pop-up</strong> ne sont pas bloquées pour le site</li>
                    <li>Dans Chrome : icône cadenas → Paramètres du site → Fenêtres contextuelles → Autoriser</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">Mauvaise imprimante sélectionnée</h3>
                <ul class="list-disc list-inside text-slate-600 text-sm mt-1">
                    <li>Définissez l'imprimante thermique comme <strong>imprimante par défaut</strong> du système</li>
                    <li>Ou choisissez-la manuellement dans la boîte de dialogue à chaque impression</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">Le reçu est coupé ou mal formaté</h3>
                <ul class="list-disc list-inside text-slate-600 text-sm mt-1">
                    <li>Utilisez une imprimante <strong>80 mm</strong> pour un meilleur rendu</li>
                    <li>Le format du reçu est optimisé pour 80 mm</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Matériel recommandé</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Élément</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Recommandation</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600">
                    <tr class="border-b border-slate-100"><td class="py-3 px-4">Imprimante</td><td class="py-3 px-4">Thermique 80 mm (USB ou Ethernet)</td></tr>
                    <tr class="border-b border-slate-100"><td class="py-3 px-4">Papier</td><td class="py-3 px-4">Rouleau thermique 80 x 80 mm</td></tr>
                    <tr class="border-b border-slate-100"><td class="py-3 px-4">PC caisse</td><td class="py-3 px-4">Windows 10/11, 4 Go RAM minimum</td></tr>
                    <tr><td class="py-3 px-4">Navigateur</td><td class="py-3 px-4">Chrome ou Edge (dernière version)</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
