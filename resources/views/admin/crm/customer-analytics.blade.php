@extends('layouts.admin')
@section('title', 'Analyse — ' . $customer->full_name)

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Analyse Client</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Profil détaillé et historique</p>
        </div>
        <a href="{{ route('admin.crm.dashboard') }}" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
            ← CRM
        </a>
    </div>

    {{-- Profil --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row items-start gap-5">
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 text-lg font-bold flex-shrink-0">
                {{ $customer->initials }}
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-900">{{ $customer->full_name }}</h2>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ $customer->email }} &middot; {{ $customer->phone ?? 'Pas de téléphone' }}</p>
                <div class="flex flex-wrap gap-1.5 mt-3">
                    @foreach($customer->tags as $tag)
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium text-white" style="background: {{ $tag->color }}">{{ $tag->name }}</span>
                    @endforeach
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">Client depuis {{ $customer->created_at->locale('fr')->diffForHumans() }}</span>
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                @if($customer->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank"
                    class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-100 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                </a>
                @endif
                <a href="{{ route('admin.customers.edit', $customer) }}"
                    class="w-9 h-9 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Métriques --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-100">
            @foreach([
                ['Commandes', $customer->orders_count],
                ['Dépense totale', number_format($customer->total_spent, 0, ',', ' ') . ' F'],
                ['Panier moyen', number_format($customer->average_order_value, 0, ',', ' ') . ' F'],
                ['Points fidélité', number_format($customer->loyalty_points)],
                ['Dernière cmd', $customer->last_order_at ? $customer->last_order_at->locale('fr')->diffForHumans() : 'Jamais'],
            ] as [$label, $val])
            <div class="p-4 text-center">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
                <p class="text-xl font-bold text-gray-900 tabular-nums">{{ $val }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        {{-- Produits préférés --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[13px] font-semibold text-gray-900">Produits préférés</h3>
            </div>
            @if($topProducts->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($topProducts as $tp)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-[13px] font-medium text-gray-800">{{ $tp->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $tp->qty }} acheté(s)</p>
                    </div>
                    <span class="text-[13px] font-medium text-gray-700">{{ number_format($tp->revenue, 0, ',', ' ') }} F</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="py-12 text-center text-[13px] text-gray-400">Aucun achat enregistré</div>
            @endif
        </div>

        {{-- Historique commandes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[13px] font-semibold text-gray-900">Dernières commandes</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($customer->orders->take(10) as $order)
                <a href="{{ route('admin.orders.show', $order) }}"
                    class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors group">
                    <div>
                        <p class="text-[13px] font-medium text-gray-900 group-hover:text-blue-600">#{{ $order->order_number }}</p>
                        <p class="text-[11px] text-gray-400">{{ $order->created_at->locale('fr')->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[13px] font-medium text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                            @if($order->status === 'delivered') bg-green-50 text-green-700
                            @elseif($order->status === 'pending') bg-yellow-50 text-yellow-700
                            @elseif($order->status === 'cancelled') bg-red-50 text-red-700
                            @else bg-blue-50 text-blue-700
                            @endif">{{ $order->status_label }}</span>
                    </div>
                </a>
                @empty
                <div class="py-12 text-center text-[13px] text-gray-400">Aucune commande</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Historique WhatsApp --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[13px] font-semibold text-gray-900">Messages WhatsApp</h3>
        </div>
        @if($customer->whatsappMessages->count() > 0)
        <div class="p-5 space-y-2 max-h-96 overflow-y-auto">
            @foreach($customer->whatsappMessages->take(20) as $msg)
            <div class="flex gap-3 {{ $msg->direction === 'outgoing' ? '' : 'flex-row-reverse' }}">
                <div class="max-w-[80%] p-3 rounded-xl text-[13px]
                    {{ $msg->direction === 'outgoing' ? 'bg-green-50 border border-green-100' : 'bg-gray-50 border border-gray-100' }}">
                    <p class="text-gray-700 whitespace-pre-line">{{ Str::limit($msg->message, 200) }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-[10px] text-gray-400">{{ $msg->created_at->locale('fr')->format('d/m H:i') }}</span>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                            @if($msg->status === 'delivered') bg-green-100 text-green-700
                            @elseif($msg->status === 'sent') bg-blue-100 text-blue-700
                            @elseif($msg->status === 'failed') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-500
                            @endif">{{ ucfirst($msg->status) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-12 text-center text-[13px] text-gray-400">Aucun message WhatsApp</div>
        @endif
    </div>

</div>
@endsection
