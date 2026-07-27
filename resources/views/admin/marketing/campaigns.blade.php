@extends('layouts.admin')
@section('title', 'Campagnes Marketing')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Campagnes Marketing</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Créez et gérez vos campagnes clients</p>
        </div>
        <a href="{{ route('admin.marketing.automations') }}" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
            Automatisations →
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-[13px] text-green-700">{{ session('success') }}</div>
    @endif

    {{-- KPI Strip --}}
    @php
        $totalCampaigns = $campaigns->total();
        $activeCampaigns = \App\Models\Campaign::where('status', 'active')->count();
        $totalSent = \App\Models\Campaign::sum('sent_count');
        $totalDelivered = \App\Models\Campaign::sum('delivered_count');
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
            @foreach([
                ['Campagnes', $totalCampaigns],
                ['Actives', $activeCampaigns],
                ['Messages envoyés', $totalSent],
                ['Délivrés', $totalDelivered],
            ] as [$label, $val])
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($val) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Créer campagne --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Nouvelle Campagne</h3>
        <form method="POST" action="{{ route('admin.marketing.campaigns.store') }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom de la campagne</label>
                    <input type="text" name="name" required placeholder="Promo Noël 2026..."
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Canal</label>
                    <select name="type" class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="push">Push Notification</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
            </div>
            @if($tags->count() > 0)
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Cibler les tags (optionnel)</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-colors">
                        <input type="checkbox" name="target_tags[]" value="{{ $tag->id }}" class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $tag->color }}"></span>
                        <span class="text-[12px] font-medium text-gray-700">{{ $tag->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            <div>
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                <textarea name="message_template" rows="4" required
                    placeholder="Bonjour {prenom}, profitez de nos offres..."
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">Variables : {prenom}, {nom}, {total_depense}, {nb_commandes}</p>
            </div>
            <div class="flex items-end gap-4">
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Programmer (optionnel)</label>
                    <input type="datetime-local" name="scheduled_at"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit" class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">
                    Créer la campagne
                </button>
            </div>
        </form>
    </div>

    {{-- Liste campagnes --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[13px] font-semibold text-gray-900">Campagnes <span class="text-gray-400 font-normal">({{ $campaigns->total() }})</span></h3>
        </div>

        @if($campaigns->isEmpty())
        <div class="py-16 text-center">
            <p class="text-[13px] text-gray-400">Aucune campagne créée</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($campaigns as $campaign)
            <div class="flex items-center justify-between px-5 py-4 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0
                        @if($campaign->type === 'whatsapp') bg-green-50 text-green-600
                        @elseif($campaign->type === 'email') bg-blue-50 text-blue-600
                        @elseif($campaign->type === 'push') bg-purple-50 text-purple-600
                        @else bg-gray-100 text-gray-500
                        @endif">
                        @if($campaign->type === 'whatsapp')
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        @elseif($campaign->type === 'email')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ $campaign->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ ucfirst($campaign->type) }} &middot; {{ $campaign->recipients_count }} destinataires &middot; {{ $campaign->sent_count }} envoyés</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                        @if($campaign->status === 'active') bg-green-50 text-green-700
                        @elseif($campaign->status === 'draft') bg-gray-100 text-gray-600
                        @elseif($campaign->status === 'completed') bg-blue-50 text-blue-700
                        @elseif($campaign->status === 'scheduled') bg-amber-50 text-amber-700
                        @else bg-red-50 text-red-600
                        @endif">{{ ucfirst($campaign->status) }}</span>
                    <form method="POST" action="{{ route('admin.marketing.campaigns.destroy', $campaign) }}"
                        onsubmit="return confirm('Supprimer cette campagne ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @if($campaigns->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $campaigns->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
