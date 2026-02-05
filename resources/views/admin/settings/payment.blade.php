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

        <!-- CinetPay -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <img src="https://cinetpay.com/favicon.ico" alt="CinetPay" class="w-8 h-8">
                    <h3 class="text-lg font-semibold text-slate-900">CinetPay</h3>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="payment_cinetpay_enabled" value="1" {{ ($settings['payment_cinetpay_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer" id="cinetpayToggle">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            
            <div id="cinetpaySettings" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Site ID</label>
                        <input type="text" name="cinetpay_site_id" value="{{ $settings['cinetpay_site_id'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mode</label>
                        <select name="cinetpay_mode" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                            <option value="sandbox" {{ ($settings['cinetpay_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                            <option value="live" {{ ($settings['cinetpay_mode'] ?? '') === 'live' ? 'selected' : '' }}>Production</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                    <input type="password" name="cinetpay_api_key" value="{{ $settings['cinetpay_api_key'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Secret Key</label>
                    <input type="password" name="cinetpay_secret_key" value="{{ $settings['cinetpay_secret_key'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="••••••••">
                </div>
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-sm text-amber-800">
                        <strong>Note :</strong> Configurez l'URL de notification (IPN) sur votre tableau de bord CinetPay :<br>
                        <code class="bg-amber-100 px-2 py-1 rounded">{{ route('webhook.cinetpay') }}</code>
                    </p>
                </div>
            </div>
        </div>

        <!-- Lygos Pay -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">LY</span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Lygos Pay</h3>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="payment_lygos_enabled" value="1" {{ ($settings['payment_lygos_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer" id="lygosToggle">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <p class="text-sm text-slate-500 mb-4">Solution de paiement mobile money et paiements internationaux pour l'Afrique francophone.</p>
            
            <div id="lygosSettings" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Clé API</label>
                    <input type="password" name="lygos_api_key" value="{{ $settings['lygos_api_key'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Votre clé API Lygos Pay">
                    <p class="mt-1 text-xs text-slate-500">Récupérez votre clé API sur <a href="https://dashboard.lygosapp.com" target="_blank" class="text-blue-600 hover:underline">dashboard.lygosapp.com</a></p>
                </div>
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-sm text-blue-800 mb-3">
                        <strong>Note :</strong> Lygos Pay nécessite uniquement votre clé API pour fonctionner. Configurez l'URL de webhook (si disponible) sur votre tableau de bord Lygos :<br>
                        <code class="bg-blue-100 px-2 py-1 rounded">{{ route('webhook.lygos') }}</code>
                    </p>
                    <form method="POST" action="{{ route('admin.settings.payment.test-lygos') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            Tester la connexion API
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Enregistrer
        </button>
    </form>
</div>

<script>
    // Afficher/masquer les paramètres selon l'état du toggle
    document.getElementById('cinetpayToggle')?.addEventListener('change', function() {
        document.getElementById('cinetpaySettings').style.display = this.checked ? 'block' : 'none';
    });
    document.getElementById('lygosToggle')?.addEventListener('change', function() {
        document.getElementById('lygosSettings').style.display = this.checked ? 'block' : 'none';
    });
    
    // Initialiser l'affichage au chargement
    document.addEventListener('DOMContentLoaded', function() {
        const cinetpayToggle = document.getElementById('cinetpayToggle');
        const lygosToggle = document.getElementById('lygosToggle');
        if (cinetpayToggle) {
            document.getElementById('cinetpaySettings').style.display = cinetpayToggle.checked ? 'block' : 'none';
        }
        if (lygosToggle) {
            document.getElementById('lygosSettings').style.display = lygosToggle.checked ? 'block' : 'none';
        }
    });
</script>
@endsection

