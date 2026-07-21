@extends('layouts.admin')

@section('title', 'Paramètres paiement')
@section('page-title', 'Paramètres de paiement')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-2 flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Général</a>
        <a href="{{ route('admin.settings.shipping') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Livraison</a>
        <a href="{{ route('admin.settings.payment') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl">Paiement</a>
        <a href="{{ route('admin.settings.emails') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Emails</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.payment.update') }}" class="space-y-6">
        @csrf

        <!-- Paiement à la livraison -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Paiement à la livraison</h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="payment_cod_enabled" value="1" {{ ($settings['payment_cod_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <p class="text-sm text-slate-500">Permettre aux clients de payer en espèces à la réception de leur commande.</p>
        </div>

        <!-- MoneyFusion -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xs">MF</span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">MoneyFusion</h3>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="payment_moneyfusion_enabled" value="1" {{ ($settings['payment_moneyfusion_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer" id="moneyfusionToggle">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                </label>
            </div>
            <p class="text-sm text-slate-500 mb-4">Solution de paiement mobile money (Orange Money, MTN, Wave, Moov) pour l'Afrique.</p>

            <div id="moneyfusionSettings" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">URL API</label>
                    <input type="text" name="moneyfusion_api_url" value="{{ $settings['moneyfusion_api_url'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="https://...">
                    <p class="mt-1 text-xs text-slate-500">Récupérez votre URL API sur votre tableau de bord MoneyFusion.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Clé API (optionnel)</label>
                    <input type="password" name="moneyfusion_api_key" value="{{ $settings['moneyfusion_api_key'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Votre clé API MoneyFusion">
                    <p class="mt-1 text-xs text-slate-500">Si votre compte nécessite une clé API, renseignez-la ici.</p>
                </div>
                <div class="p-4 bg-green-50 border border-green-200 rounded-xl space-y-2">
                    <p class="text-sm text-green-800">
                        <strong>URL de Webhook :</strong> Configurez cette URL dans votre tableau de bord MoneyFusion :<br>
                        <code class="bg-green-100 px-2 py-1 rounded">{{ route('webhook.moneyfusion') }}</code>
                    </p>
                    <p class="text-sm text-green-800">
                        <strong>URL de retour :</strong> Le client sera redirigé automatiquement vers votre site après le paiement.
                    </p>
                </div>
            </div>
        </div>

        <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Enregistrer
        </button>
    </form>

    <!-- Pusher - Notifications temps réel -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Notifications temps réel (Pusher)</h3>
        </div>
        <p class="text-sm text-slate-600 mb-4">Configurez Pusher pour recevoir des notifications en direct (nouvelle commande, son, voix « Nouvelle commande ») sans recharger le backoffice.</p>
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <p class="text-sm text-slate-700 mb-2">Ajoutez ces variables dans votre fichier <code class="bg-slate-200 px-1 rounded">.env</code> :</p>
            <pre class="text-xs bg-slate-800 text-slate-100 p-4 rounded-lg overflow-x-auto">PUSHER_APP_ID=votre_app_id
PUSHER_APP_KEY=votre_cle
PUSHER_APP_SECRET=votre_secret
PUSHER_APP_CLUSTER=mt1
BROADCAST_CONNECTION=pusher

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"</pre>
            <p class="text-xs text-slate-500 mt-2">Créez une app gratuite sur <a href="https://dashboard.pusher.com" target="_blank" class="text-blue-600 hover:underline">dashboard.pusher.com</a></p>
            <p class="text-xs text-amber-600 mt-1"><strong>Important :</strong> Lancez <code class="bg-amber-100 px-1 rounded">php artisan queue:work</code> pour que les notifications soient diffusées.</p>
        </div>
    </div>

    <!-- Test des connexions (formulaires séparés, hors du formulaire principal) -->
    <div class="mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-200">
        <h4 class="font-medium text-slate-900 mb-2">Tester les connexions API</h4>
        <p class="text-sm text-slate-600 mb-3">Enregistrez d'abord vos paramètres ci-dessus, puis testez les connexions.</p>
        <form method="POST" action="{{ route('admin.settings.payment.test-moneyfusion') }}" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                Tester MoneyFusion
            </button>
        </form>
    </div>
</div>

<script>
    // Éviter les listeners multiples avec wire:navigate
    if (!window.paymentSettingsInitialized) {
        window.paymentSettingsInitialized = true;

        const initPaymentSettings = () => {
            const toggle = document.getElementById('moneyfusionToggle');
            const settings = document.getElementById('moneyfusionSettings');

            if (toggle && settings) {
                // Retirer ancien listener si présent
                toggle.removeEventListener('change', window.moneyfusionToggleHandler);

                // Handler global
                window.moneyfusionToggleHandler = function() {
                    settings.style.display = this.checked ? 'block' : 'none';
                };

                toggle.addEventListener('change', window.moneyfusionToggleHandler);

                // État initial
                settings.style.display = toggle.checked ? 'block' : 'none';
            }
        };

        // Init au chargement
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPaymentSettings);
        } else {
            initPaymentSettings();
        }

        // Re-init après navigation Livewire
        document.addEventListener('livewire:navigated', initPaymentSettings);
    }
</script>
@endsection

