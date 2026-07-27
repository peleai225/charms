@extends('layouts.admin')
@section('title', 'Automatisations Marketing')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Automatisations</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Messages envoyés automatiquement selon des déclencheurs</p>
        </div>
        <a href="{{ route('admin.marketing.campaigns') }}" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
            ← Campagnes
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-[13px] text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Créer automatisation --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Nouvelle Automatisation</h3>
        <form method="POST" action="{{ route('admin.marketing.automations.store') }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom</label>
                    <input type="text" name="name" required placeholder="Relance panier..."
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Déclencheur</label>
                    <select name="trigger" class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="abandoned_cart">Panier abandonné</option>
                        <option value="post_purchase">Après achat</option>
                        <option value="post_delivery">Après livraison</option>
                        <option value="inactive_customer">Client inactif</option>
                        <option value="birthday">Anniversaire</option>
                        <option value="loyalty_milestone">Palier fidélité</option>
                        <option value="new_customer">Nouveau client</option>
                        <option value="vip_upgrade">Passage VIP</option>
                        <option value="custom">Personnalisé</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Canal</label>
                    <select name="channel" class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="push">Push Notification</option>
                    </select>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Délai (heures)</label>
                    <input type="number" name="delay_hours" value="1" min="0"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-[11px] text-gray-400 mt-1">0 = immédiat</p>
                </div>
                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-gray-700 font-medium">Activer immédiatement</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                <textarea name="message_template" rows="3" required
                    placeholder="Bonjour {prenom} ! Votre panier vous attend..."
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
            </div>
            <button type="submit" class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">
                Créer l'automatisation
            </button>
        </form>
    </div>

    {{-- Liste --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[13px] font-semibold text-gray-900">Automatisations <span class="text-gray-400 font-normal">({{ $automations->count() }})</span></h3>
        </div>

        @if($automations->isEmpty())
        <div class="py-16 text-center">
            <p class="text-[13px] text-gray-400">Aucune automatisation configurée</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($automations as $auto)
            @php
            $triggerLabels = [
                'abandoned_cart' => 'Panier abandonné',
                'post_purchase' => 'Après achat',
                'post_delivery' => 'Après livraison',
                'inactive_customer' => 'Client inactif',
                'birthday' => 'Anniversaire',
                'loyalty_milestone' => 'Palier fidélité',
                'new_customer' => 'Nouveau client',
                'vip_upgrade' => 'Passage VIP',
            ];
            @endphp
            <div class="flex items-center justify-between px-5 py-4 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $auto->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ $auto->name }}</p>
                        <p class="text-[11px] text-gray-400">
                            {{ $triggerLabels[$auto->trigger] ?? ucfirst($auto->trigger) }}
                            &middot; {{ ucfirst($auto->channel) }}
                            &middot; Délai : {{ $auto->delay_hours }}h
                            &middot; {{ $auto->sent_count }} envoyés
                            @if($auto->conversion_rate > 0) &middot; {{ $auto->conversion_rate }}% conversion @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.marketing.automations.toggle', $auto) }}">
                        @csrf
                        <button type="submit" class="h-7 px-3 text-[12px] font-medium rounded-lg transition-colors
                            {{ $auto->is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            {{ $auto->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.marketing.automations.destroy', $auto) }}"
                        onsubmit="return confirm('Supprimer cette automatisation ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
