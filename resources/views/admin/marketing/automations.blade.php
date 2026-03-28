@extends('layouts.admin')
@section('title', 'Automatisations Marketing')
@section('page-title', 'Automatisations')

@section('content')
<div class="space-y-6">

    {{-- Creer automatisation --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 mb-4">Nouvelle Automatisation</h3>
        <form method="POST" action="{{ route('admin.marketing.automations.store') }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Nom</label>
                    <input type="text" name="name" required placeholder="Relance panier..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Declencheur</label>
                    <select name="trigger" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                        <option value="abandoned_cart">Panier abandonne</option>
                        <option value="post_purchase">Apres achat</option>
                        <option value="post_delivery">Apres livraison</option>
                        <option value="inactive_customer">Client inactif</option>
                        <option value="birthday">Anniversaire</option>
                        <option value="loyalty_milestone">Palier fidelite</option>
                        <option value="new_customer">Nouveau client</option>
                        <option value="vip_upgrade">Passage VIP</option>
                        <option value="custom">Personnalise</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Canal</label>
                    <select name="channel" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="push">Push Notification</option>
                    </select>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Delai (heures)</label>
                    <input type="number" name="delay_hours" value="1" min="0" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Temps d'attente apres le declencheur. 0 = immediat.</p>
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-indigo-600">
                        <span class="text-sm text-slate-700 font-medium">Activer immediatement</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Message</label>
                <textarea name="message_template" rows="3" required placeholder="Bonjour {prenom} ! Votre panier vous attend..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none resize-none"></textarea>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-colors">
                Creer l'automatisation
            </button>
        </form>
    </div>

    {{-- Liste --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Automatisations actives</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($automations as $auto)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl {{ $auto->is_active ? 'bg-green-50' : 'bg-slate-50' }} flex items-center justify-center">
                        <div class="w-3 h-3 rounded-full {{ $auto->is_active ? 'bg-green-500 animate-pulse' : 'bg-slate-300' }}"></div>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-slate-900">{{ $auto->name }}</p>
                        <p class="text-[11px] text-slate-400">
                            @php
                            $triggerLabels = [
                                'abandoned_cart' => 'Panier abandonne',
                                'post_purchase' => 'Apres achat',
                                'post_delivery' => 'Apres livraison',
                                'inactive_customer' => 'Client inactif',
                                'birthday' => 'Anniversaire',
                                'loyalty_milestone' => 'Palier fidelite',
                                'new_customer' => 'Nouveau client',
                                'vip_upgrade' => 'Passage VIP',
                            ];
                            @endphp
                            {{ $triggerLabels[$auto->trigger] ?? ucfirst($auto->trigger) }}
                            &middot; {{ ucfirst($auto->channel) }}
                            &middot; Delai : {{ $auto->delay_hours }}h
                            &middot; {{ $auto->sent_count }} envoyes
                            @if($auto->conversion_rate > 0) &middot; {{ $auto->conversion_rate }}% conversion @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.marketing.automations.toggle', $auto) }}">
                        @csrf
                        <button class="px-3 py-1 rounded-lg text-xs font-bold {{ $auto->is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }} transition-colors">
                            {{ $auto->is_active ? 'Desactiver' : 'Activer' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.marketing.automations.destroy', $auto) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-300 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <p class="text-sm">Aucune automatisation. Creez-en une ci-dessus.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
