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
                        <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="smtp.example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Port</label>
                        <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? 587 }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
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
                        <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="null" {{ ($settings['mail_encryption'] ?? '') === 'null' ? 'selected' : '' }}>Aucun</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                Enregistrer
            </button>
            <button type="button" onclick="testEmail()" class="px-6 py-3 bg-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-300 transition-colors">
                Envoyer un test
            </button>
        </div>
    </form>
</div>

<script>
function testEmail() {
    alert('Un email de test sera envoyé à l\'adresse configurée.');
}
</script>
@endsection

