@extends('layouts.admin')
@section('title', 'Analyse Client - ' . $customer->full_name)
@section('page-title', 'Analyse Client')

@section('content')
<div class="space-y-6">

    {{-- Profil --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-black flex-shrink-0">
                {{ $customer->initials }}
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-black text-slate-900">{{ $customer->full_name }}</h2>
                <p class="text-slate-400 text-sm mt-0.5">{{ $customer->email }} &middot; {{ $customer->phone ?? 'Pas de telephone' }}</p>
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach($customer->tags as $tag)
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold text-white" style="background: {{ $tag->color }}">{{ $tag->name }}</span>
                    @endforeach
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">Client depuis {{ $customer->created_at->locale('fr')->diffForHumans() }}</span>
                </div>
            </div>
            <div class="flex gap-3">
                @if($customer->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-100 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                </a>
                @endif
                <a href="{{ route('admin.customers.edit', $customer) }}" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Metriques --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach([
            ['Commandes', $customer->orders_count, 'text-blue-600'],
            ['Depense totale', number_format($customer->total_spent, 0, ',', ' ') . ' F', 'text-emerald-600'],
            ['Panier moyen', number_format($customer->average_order_value, 0, ',', ' ') . ' F', 'text-indigo-600'],
            ['Points fidelite', number_format($customer->loyalty_points), 'text-amber-600'],
            ['Derniere commande', $customer->last_order_at ? $customer->last_order_at->locale('fr')->diffForHumans() : 'Jamais', 'text-slate-600'],
        ] as [$label, $val, $color])
        <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm text-center">
            <p class="text-xl font-black {{ $color }}">{{ $val }}</p>
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Produits preferes --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-slate-900 mb-4">Produits Preferes</h3>
            @if($topProducts->count() > 0)
            <div class="space-y-3">
                @foreach($topProducts as $tp)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-sm text-slate-800">{{ $tp->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $tp->qty }} achetes</p>
                    </div>
                    <span class="text-sm font-bold text-slate-700">{{ number_format($tp->revenue, 0, ',', ' ') }} F</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-8">Aucun achat enregistre</p>
            @endif
        </div>

        {{-- Historique commandes --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-slate-900 mb-4">Dernieres Commandes</h3>
            <div class="space-y-2">
                @foreach($customer->orders->take(10) as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-50 transition-colors group">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600">#{{ $order->order_number }}</p>
                        <p class="text-[11px] text-slate-400">{{ $order->created_at->locale('fr')->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                            @if($order->status === 'delivered') bg-green-50 text-green-600
                            @elseif($order->status === 'pending') bg-yellow-50 text-yellow-600
                            @elseif($order->status === 'cancelled') bg-red-50 text-red-600
                            @else bg-blue-50 text-blue-600
                            @endif">{{ $order->status_label }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Historique WhatsApp --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 mb-4">Messages WhatsApp</h3>
        @if($customer->whatsappMessages->count() > 0)
        <div class="space-y-3 max-h-[400px] overflow-y-auto">
            @foreach($customer->whatsappMessages->take(20) as $msg)
            <div class="flex gap-3 {{ $msg->direction === 'outgoing' ? '' : 'flex-row-reverse' }}">
                <div class="max-w-[80%] p-3 rounded-2xl {{ $msg->direction === 'outgoing' ? 'bg-green-50 border border-green-100' : 'bg-slate-50 border border-slate-100' }}">
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ Str::limit($msg->message, 200) }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-[10px] text-slate-400">{{ $msg->created_at->locale('fr')->format('d/m H:i') }}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                            @if($msg->status === 'delivered') bg-green-100 text-green-700
                            @elseif($msg->status === 'sent') bg-blue-100 text-blue-700
                            @elseif($msg->status === 'failed') bg-red-100 text-red-700
                            @else bg-slate-100 text-slate-500
                            @endif">{{ ucfirst($msg->status) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-slate-400 text-center py-8">Aucun message WhatsApp</p>
        @endif
    </div>
</div>
@endsection
