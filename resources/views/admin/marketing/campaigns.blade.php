@extends('layouts.admin')
@section('title', 'Campagnes Marketing')
@section('page-title', 'Campagnes Marketing')

@section('content')
<div class="space-y-6">

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $totalCampaigns = $campaigns->total();
            $activeCampaigns = \App\Models\Campaign::where('status', 'active')->count();
            $totalSent = \App\Models\Campaign::sum('sent_count');
            $totalDelivered = \App\Models\Campaign::sum('delivered_count');
        @endphp
        @foreach([
            ['Campagnes', $totalCampaigns, 'from-indigo-500 to-purple-500'],
            ['Actives', $activeCampaigns, 'from-emerald-500 to-green-500'],
            ['Messages envoyes', $totalSent, 'from-blue-500 to-cyan-500'],
            ['Delivres', $totalDelivered, 'from-amber-500 to-orange-500'],
        ] as [$label, $val, $gradient])
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <p class="text-2xl font-black text-slate-900">{{ number_format($val) }}</p>
            <p class="text-xs text-slate-400 font-medium mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Creer campagne --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 mb-4">Nouvelle Campagne</h3>
        <form method="POST" action="{{ route('admin.marketing.campaigns.store') }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Nom de la campagne</label>
                    <input type="text" name="name" required placeholder="Promo Noel 2026..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Canal</label>
                    <select name="type" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="push">Push Notification</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Cibler les tags (optionnel)</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-slate-200 cursor-pointer hover:border-indigo-400 transition-colors">
                        <input type="checkbox" name="target_tags[]" value="{{ $tag->id }}" class="rounded border-slate-300 text-indigo-600 w-3.5 h-3.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $tag->color }}"></span>
                        <span class="text-xs font-medium text-slate-700">{{ $tag->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Message</label>
                <textarea name="message_template" rows="4" required placeholder="Bonjour {prenom}, profitez de nos offres..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none resize-none"></textarea>
                <p class="text-[10px] text-slate-400 mt-1">Variables : {prenom}, {nom}, {total_depense}, {nb_commandes}</p>
            </div>
            <div class="flex items-center gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Programmer (optionnel)</label>
                    <input type="datetime-local" name="scheduled_at" class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
                </div>
                <button type="submit" class="mt-5 px-6 py-2 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-colors">
                    Creer la campagne
                </button>
            </div>
        </form>
    </div>

    {{-- Liste campagnes --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Campagnes</h3>
            <a href="{{ route('admin.marketing.automations') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Voir automatisations</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($campaigns as $campaign)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center
                        @if($campaign->type === 'whatsapp') bg-green-50 text-green-600
                        @elseif($campaign->type === 'email') bg-blue-50 text-blue-600
                        @elseif($campaign->type === 'push') bg-purple-50 text-purple-600
                        @else bg-slate-50 text-slate-600
                        @endif">
                        @if($campaign->type === 'whatsapp')
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-slate-900">{{ $campaign->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ ucfirst($campaign->type) }} &middot; {{ $campaign->recipients_count }} destinataires &middot; {{ $campaign->sent_count }} envoyes</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                        @if($campaign->status === 'active') bg-green-50 text-green-700
                        @elseif($campaign->status === 'draft') bg-slate-50 text-slate-600
                        @elseif($campaign->status === 'completed') bg-blue-50 text-blue-700
                        @elseif($campaign->status === 'scheduled') bg-amber-50 text-amber-700
                        @else bg-red-50 text-red-600
                        @endif">{{ ucfirst($campaign->status) }}</span>
                    <form method="POST" action="{{ route('admin.marketing.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-300 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <p class="text-sm">Aucune campagne. Creez-en une ci-dessus.</p>
            </div>
            @endforelse
        </div>
        @if($campaigns->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $campaigns->links() }}</div>
        @endif
    </div>
</div>
@endsection
