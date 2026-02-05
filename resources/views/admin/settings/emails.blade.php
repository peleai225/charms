@extends('layouts.admin')

@section('title', 'Paramètres emails')
@section('page-title', 'Paramètres email')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-2 flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Général</a>
        <a href="{{ route('admin.settings.shipping') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Livraison</a>
        <a href="{{ route('admin.settings.payment') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Paiement</a>
        <a href="{{ route('admin.settings.emails') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl">Emails</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.emails.update') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Expéditeur</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom d'expéditeur *</label>
                    <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? config('app.name') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email d'expéditeur *</label>
                    <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Configuration SMTP</h3>
            
            <!-- Info Gmail -->
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-sm text-blue-900 font-medium mb-2">📧 Configuration Gmail</p>
                <p class="text-xs text-blue-700">
                    Pour Gmail : <code class="bg-blue-100 px-1 rounded">smtp.gmail.com</code> | Port <code class="bg-blue-100 px-1 rounded">587</code> (TLS) ou <code class="bg-blue-100 px-1 rounded">465</code> (SSL)<br>
                    <strong>Important :</strong> Utilisez un <strong>mot de passe d'application</strong> Gmail (pas votre mot de passe normal).<br>
                    <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-600 hover:underline">Créer un mot de passe d'application →</a>
                </p>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Driver</label>
                    <select name="mail_driver" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                        <option value="smtp" {{ ($settings['mail_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ ($settings['mail_driver'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="mailgun" {{ ($settings['mail_driver'] ?? '') === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                    </select>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Serveur SMTP</label>
                        <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="smtp.gmail.com">
                        <p class="text-xs text-slate-500 mt-1">Ex: smtp.gmail.com pour Gmail</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Port</label>
                        <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? 587 }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                        <p class="text-xs text-slate-500 mt-1">587 (TLS) ou 465 (SSL) pour Gmail</p>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom d'utilisateur</label>
                        <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
                        <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="••••••••">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chiffrement</label>
                    <select name="mail_encryption" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                        <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Recommandé pour Gmail)</option>
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="null" {{ ($settings['mail_encryption'] ?? '') === 'null' ? 'selected' : '' }}>Aucun</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">TLS pour port 587, SSL pour port 465</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                Enregistrer
            </button>
        </div>
    </form>

    <!-- Test Email -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mt-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Tester la Configuration</h3>
        <form method="POST" action="{{ route('admin.settings.emails.test') }}" class="flex gap-4">
            @csrf
            <input type="email" name="test_email" value="{{ $settings['mail_from_address'] ?? auth()->user()->email }}" 
                placeholder="Email de test" required
                class="flex-1 px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <button type="submit" class="px-6 py-2 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                📧 Envoyer un test
            </button>
        </form>
        <p class="text-xs text-slate-500 mt-2">
            Un email de test sera envoyé à cette adresse pour vérifier que la configuration fonctionne.
        </p>
    </div>
</div>
@endsection

