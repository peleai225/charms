@extends('layouts.admin')

@section('title', 'Commande ' . $order->order_number)
@section('page-title', 'Détails de la commande')

@section('breadcrumbs')
<nav class="flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('admin.orders.index') }}" class="hover:text-slate-900 transition-colors">Commandes</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-900 font-medium">{{ $order->order_number }}</span>
</nav>
@endsection

@section('content')
@php
    $statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
    $statusLabels = [
        'pending'    => 'En attente',
        'confirmed'  => 'Confirmée',
        'processing' => 'En préparation',
        'shipped'    => 'Expédiée',
        'delivered'  => 'Livrée',
        'cancelled'  => 'Annulée',
    ];
    $statusIcons = [
        'pending'    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'confirmed'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'processing' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        'shipped'    => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
        'delivered'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    ];
    $statusColors = [
        'pending'    => 'amber',
        'confirmed'  => 'blue',
        'processing' => 'indigo',
        'shipped'    => 'purple',
        'delivered'  => 'green',
        'cancelled'  => 'red',
    ];

    $isCancelled = $order->status === 'cancelled';
    $currentIdx  = $isCancelled ? -1 : (array_search($order->status, $statusOrder) ?? 0);

    // Date stamps per step (only shipped_at and delivered_at exist on the model)
    $stepDates = [
        'pending'    => $order->created_at,
        'confirmed'  => $currentIdx >= 1 ? $order->created_at : null,
        'processing' => $currentIdx >= 2 ? $order->created_at : null,
        'shipped'    => $order->shipped_at ?? ($currentIdx >= 3 ? $order->updated_at : null),
        'delivered'  => $order->delivered_at ?? ($currentIdx >= 4 ? $order->updated_at : null),
    ];
@endphp

<div class="space-y-6" x-data="orderShow()">

    <!-- En-tête -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-500 hover:text-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-900 font-mono">{{ $order->order_number }}</h2>
                    @switch($order->status)
                        @case('pending')
                            <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>En attente
                            </span>@break
                        @case('confirmed')
                            <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full bg-blue-50 text-blue-700 ring-1 ring-blue-200">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>Confirmée
                            </span>@break
                        @case('processing')
                            <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>En préparation
                            </span>@break
                        @case('shipped')
                            <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full bg-purple-50 text-purple-700 ring-1 ring-purple-200">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>Expédiée
                            </span>@break
                        @case('delivered')
                            <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full bg-green-50 text-green-700 ring-1 ring-green-200">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>Livrée
                            </span>@break
                        @case('cancelled')
                            <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-red-200">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>Annulée
                            </span>@break
                    @endswitch
                </div>
                <p class="text-slate-500 text-sm mt-0.5">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.orders.invoice.view', $order) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Voir facture
            </a>
            <a href="{{ route('admin.orders.invoice', $order) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors text-sm shadow-sm shadow-green-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Télécharger PDF
            </a>
        </div>
    </div>

    {{-- ===== TIMELINE ===== --}}
    @if(!$isCancelled)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-x-auto">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-6">Suivi de la commande</h3>
        <div class="relative min-w-[560px]">
            {{-- Progress bar background --}}
            <div class="absolute top-6 left-6 right-6 h-1 bg-slate-100 rounded-full"></div>
            {{-- Progress bar fill: width = currentIdx/4 * (100% - 48px) = currentIdx*25% - currentIdx*12px --}}
            @php
                $pW = $currentIdx * 25;     // percentage part
                $pPx = $currentIdx * 12;    // pixel offset (half of step width per step)
            @endphp
            <div class="absolute top-6 left-6 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-700"
                 style="width: calc({{ $pW }}% - {{ $pPx }}px)"></div>

            {{-- Steps --}}
            <div class="relative flex justify-between">
                @foreach($statusOrder as $i => $step)
                @php
                    $isDone    = $currentIdx >= $i;
                    $isActive  = $currentIdx === $i;
                    $stepDate  = $stepDates[$step] ?? null;
                    $color     = $statusColors[$step] ?? 'slate';
                @endphp
                <div class="flex flex-col items-center gap-2 flex-1">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-500 z-10
                        {{ $isDone
                            ? 'border-' . $color . '-500 bg-' . $color . '-500 text-white shadow-lg shadow-' . $color . '-500/30'
                            : 'border-slate-200 bg-white text-slate-300' }}">
                        @if($isActive)
                            <div class="w-3 h-3 rounded-full bg-white animate-ping absolute"></div>
                        @endif
                        <svg class="w-5 h-5 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcons[$step] }}"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold {{ $isDone ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ $statusLabels[$step] }}
                        </p>
                        @if($stepDate && $isDone)
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $stepDate->format('d/m/Y') }}</p>
                            <p class="text-[10px] text-slate-400">{{ $stepDate->format('H:i') }}</p>
                        @else
                            <p class="text-[10px] text-slate-300 mt-0.5">—</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <p class="font-semibold text-red-800">Commande annulée</p>
            <p class="text-sm text-red-600">Cette commande a été annulée le {{ $order->updated_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ===== COLONNE PRINCIPALE ===== -->
        <div class="lg:col-span-2 space-y-6">

            {{-- Articles --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Articles commandés</h3>
                    <span class="text-xs font-medium px-2 py-1 bg-slate-100 text-slate-600 rounded-lg">{{ $order->items->count() }} article(s)</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                    <div class="p-5 flex gap-4 hover:bg-slate-50/50 transition-colors">
                        <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-xl overflow-hidden flex-shrink-0">
                            @php $img = $item->product?->images->where('is_primary', true)->first() ?? $item->product?->images->first(); @endphp
                            @if($img)
                                <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900 leading-snug">{{ $item->product_name }}</p>
                            @if($item->variant_name)
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs bg-slate-100 text-slate-600 rounded-md font-medium">{{ $item->variant_name }}</span>
                            @endif
                            <p class="text-xs text-slate-400 mt-1 font-mono">{{ $item->sku }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm text-slate-500">{{ number_format($item->unit_price, 0, ',', ' ') }} F × {{ $item->quantity }}</p>
                            <p class="text-lg font-bold text-slate-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 space-y-2">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Sous-total</span>
                        <span>{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Réduction @if($order->coupon_code)<span class="font-mono font-semibold">({{ $order->coupon_code }})</span>@endif</span>
                        <span class="font-semibold">-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Livraison</span>
                        <span>{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite' }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold pt-2 border-t border-slate-200">
                        <span>Total</span>
                        <span class="text-lg text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                    </div>
                </div>
            </div>

            {{-- Adresses --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-900 text-sm">Livraison</h3>
                    </div>
                    <div class="text-sm text-slate-600 space-y-1">
                        <p class="font-semibold text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                        <p>{{ $order->shipping_address }}</p>
                        @if($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
                        <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                        <p>{{ $order->shipping_country }}</p>
                        @if($order->shipping_phone)
                            <a href="tel:{{ $order->shipping_phone }}" class="inline-flex items-center gap-1.5 mt-2 text-blue-600 hover:text-blue-700 text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $order->shipping_phone }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-900 text-sm">Facturation</h3>
                    </div>
                    <div class="text-sm text-slate-600 space-y-1">
                        <p class="font-semibold text-slate-900">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                        <p>{{ $order->billing_address }}</p>
                        @if($order->billing_address_2)<p>{{ $order->billing_address_2 }}</p>@endif
                        <p>{{ $order->billing_postal_code }} {{ $order->billing_city }}</p>
                        <p>{{ $order->billing_country }}</p>
                        <a href="mailto:{{ $order->billing_email }}" class="inline-flex items-center gap-1.5 mt-2 text-blue-600 hover:text-blue-700 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $order->billing_email }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Notes client --}}
            @if($order->notes)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <h4 class="text-sm font-semibold text-amber-800">Note du client</h4>
                </div>
                <p class="text-sm text-amber-700">{{ $order->notes }}</p>
            </div>
            @endif

            {{-- Notes internes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5" id="admin-notes-section">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-900">Notes internes</h3>
                    </div>
                </div>

                @if($order->admin_notes)
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-4">
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $order->admin_notes }}</p>
                </div>
                @endif

                {{-- AJAX Note Form --}}
                <div x-data="noteForm()" class="space-y-3">
                    <textarea
                        x-model="note"
                        @keydown.ctrl.enter="save()"
                        rows="3"
                        placeholder="Ajouter une note interne... (Ctrl+Entrée pour sauvegarder)"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none transition-colors placeholder:text-slate-400">
                    </textarea>
                    <button
                        @click="save()"
                        :disabled="saving || !note.trim()"
                        :class="saved ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="inline-flex items-center gap-2 px-4 py-2 text-white font-medium rounded-xl transition-all text-sm disabled:opacity-50">
                        <template x-if="saving">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <template x-if="!saving && saved">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="!saving && !saved">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </template>
                        <span x-text="saved ? 'Note sauvegardée !' : (saving ? 'Sauvegarde...' : 'Ajouter la note')"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== COLONNE LATÉRALE ===== -->
        <div class="space-y-6">

            {{-- Client info --}}
            @if($order->customer)
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/20">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($order->customer->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold">{{ $order->customer->full_name }}</p>
                        <p class="text-xs text-indigo-200">Client #{{ $order->customer->id }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div class="bg-white/10 rounded-xl p-3 text-center">
                        <p class="text-xl font-extrabold">{{ $order->customer->orders_count ?? '—' }}</p>
                        <p class="text-xs text-indigo-200 mt-0.5">Commandes</p>
                    </div>
                    <a href="{{ route('admin.customers.show', $order->customer) }}" class="bg-white/10 hover:bg-white/20 rounded-xl p-3 text-center transition-colors block">
                        <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <p class="text-xs text-indigo-200">Voir profil</p>
                    </a>
                </div>
            </div>
            @endif

            {{-- Statut AJAX --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5" x-data="statusForm()">
                <h3 class="font-semibold text-slate-900 mb-4">Mettre à jour le statut</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Statut</label>
                        <select x-model="formData.status" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">
                            <option value="pending">En attente</option>
                            <option value="confirmed">Confirmée</option>
                            <option value="processing">En préparation</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Transporteur</label>
                        <select x-model="formData.shipping_carrier" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">
                            <option value="">Sélectionner...</option>
                            <option value="chronopost">Chronopost</option>
                            <option value="dhl">DHL</option>
                            <option value="fedex">FedEx</option>
                            <option value="colissimo">Colissimo</option>
                            <option value="livraison_locale">Livraison locale</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">N° de suivi</label>
                        <input type="text" x-model="formData.tracking_number" placeholder="ABC123456789"
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 font-mono text-sm">
                    </div>

                    <button @click="submit()" :disabled="saving"
                        :class="saved ? 'bg-green-600 hover:bg-green-700 shadow-green-600/20' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20'"
                        class="w-full py-2.5 text-white font-semibold rounded-xl transition-all text-sm shadow-lg flex items-center justify-center gap-2 disabled:opacity-60">
                        <template x-if="saving"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></template>
                        <template x-if="!saving && saved"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></template>
                        <span x-text="saved ? 'Mis à jour !' : (saving ? 'Mise à jour...' : 'Mettre à jour')"></span>
                    </button>
                </div>

                @if($order->tracking_number)
                <div class="mt-4 p-3 bg-purple-50 border border-purple-100 rounded-xl">
                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider mb-1">Numéro de suivi</p>
                    <p class="font-mono text-purple-900 font-semibold">{{ $order->tracking_number }}</p>
                    @if($order->shipping_carrier)
                        <p class="text-xs text-purple-500 mt-1">via {{ ucfirst($order->shipping_carrier) }}</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Paiement --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3 class="font-semibold text-slate-900">Paiement</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Méthode</span>
                        <span class="text-sm font-semibold text-slate-900">
                            {{ $order->payment_method === 'cinetpay' ? 'CinetPay' : ($order->payment_method === 'cod' ? 'À la livraison' : ($order->payment_method ?? 'N/A')) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Statut</span>
                        @if($order->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Payée
                            </span>
                        @elseif($order->payment_status === 'refunded')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Remboursée</span>
                        @elseif($order->payment_status === 'failed')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-100 text-red-700">Échoué</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>En attente
                            </span>
                        @endif
                    </div>
                    @if($order->paid_at)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Payée le</span>
                        <span class="text-sm font-medium text-slate-900">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @php $totalRefunded = $order->refunds()->sum('amount'); @endphp
                    @if($totalRefunded > 0)
                    <div class="flex justify-between items-center text-amber-600">
                        <span class="text-sm">Remboursé</span>
                        <span class="text-sm font-semibold">-{{ number_format($totalRefunded, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900">Total</span>
                        <span class="text-xl font-extrabold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Actions rapides</h3>
                <div class="space-y-2">
                    {{-- Resend email --}}
                    <button x-data="{ sending: false, sent: false }"
                        @click="if(sending) return; sending=true; fetch('{{ route('admin.orders.resend', $order) }}', { method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' } }).then(r=>r.json()).then(d=>{ sending=false; sent=true; if(window.Alpine?.store('notify')) window.Alpine.store('notify').success('Email renvoyé'); setTimeout(()=>sent=false,3000); }).catch(()=>sending=false);"
                        :disabled="sending || sent"
                        class="w-full flex items-center gap-3 px-4 py-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors text-sm font-medium text-slate-700 disabled:opacity-60">
                        <template x-if="!sending && !sent">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </template>
                        <template x-if="sending">
                            <svg class="w-4 h-4 animate-spin text-slate-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                        <template x-if="sent && !sending">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <span x-text="sent ? 'Email envoyé !' : (sending ? 'Envoi...' : 'Renvoyer email de confirmation')"></span>
                    </button>

                    <a href="{{ route('admin.orders.invoice.view', $order) }}" target="_blank"
                       class="w-full flex items-center gap-3 px-4 py-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors text-sm font-medium text-slate-700">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Voir la facture
                    </a>

                    @if($order->billing_phone)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->billing_phone) }}?text={{ urlencode('Bonjour, concernant votre commande ' . $order->order_number) }}"
                       target="_blank"
                       class="w-full flex items-center gap-3 px-4 py-3 bg-green-50 hover:bg-green-100 rounded-xl transition-colors text-sm font-medium text-green-700">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        Contacter sur WhatsApp
                    </a>
                    @endif
                </div>
            </div>

            {{-- Remboursement --}}
            @if($order->is_refundable)
            @php $maxRefundable = max(0, (float)$order->total - $order->refunds()->sum('amount')); @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Créer un remboursement</h3>
                <form method="POST" action="{{ route('admin.refunds.store', $order) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Montant (max {{ number_format($maxRefundable, 0, ',', ' ') }} F)</label>
                        <input type="number" name="amount" step="1" min="1" max="{{ (int)$maxRefundable }}" value="{{ (int)$maxRefundable }}" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-slate-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Motif</label>
                        <select name="reason" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-slate-50 text-sm">
                            <option value="customer_request">Demande client</option>
                            <option value="product_defective">Produit défectueux</option>
                            <option value="wrong_item">Mauvais article</option>
                            <option value="not_delivered">Non livré</option>
                            <option value="duplicate">Doublon</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Notes (optionnel)</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-slate-50 text-sm resize-none" placeholder="Détails..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl transition-colors text-sm shadow-sm shadow-amber-600/20">
                        Créer le remboursement
                    </button>
                </form>
            </div>
            @endif

            @if($order->refunds->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Remboursements</h3>
                <ul class="space-y-2">
                    @foreach($order->refunds as $r)
                    <li class="flex justify-between items-center p-3 bg-slate-50 rounded-lg text-sm">
                        <span class="font-mono text-slate-600">{{ $r->refund_number }}</span>
                        <span class="font-semibold text-red-600">-{{ number_format($r->amount, 0, ',', ' ') }} F</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function orderShow() {
    return {};
}

function noteForm() {
    return {
        note: '',
        saving: false,
        saved: false,
        save() {
            if (!this.note.trim() || this.saving) return;
            this.saving = true;
            const noteText = this.note;
            fetch('{{ route('admin.orders.note', $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ note: noteText })
            })
            .then(r => r.json())
            .then(data => {
                this.saving = false;
                if (data.success !== false) {
                    this.saved = true;
                    // Update displayed notes
                    const section = document.getElementById('admin-notes-section');
                    const existing = section ? section.querySelector('.bg-indigo-50') : null;
                    const newContent = data.admin_notes || noteText;
                    if (existing) {
                        existing.querySelector('p').textContent = newContent;
                    } else if (section) {
                        const div = document.createElement('div');
                        div.className = 'bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-4';
                        div.innerHTML = '<p class="text-sm text-slate-700 whitespace-pre-line">' + newContent.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>';
                        section.querySelector('.space-y-3').before(div);
                    }
                    this.note = '';
                    setTimeout(() => this.saved = false, 3000);
                    if (window.Alpine?.store('notify')) window.Alpine.store('notify').success('Note ajoutée');
                }
            })
            .catch(() => { this.saving = false; });
        }
    };
}

function statusForm() {
    return {
        saving: false,
        saved: false,
        formData: {
            status: '{{ $order->status }}',
            shipping_carrier: '{{ $order->shipping_carrier ?? '' }}',
            tracking_number: '{{ $order->tracking_number ?? '' }}'
        },
        submit() {
            if (this.saving) return;
            this.saving = true;
            fetch('{{ route('admin.orders.status', $order) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.formData)
            })
            .then(r => r.json())
            .then(data => {
                this.saving = false;
                if (data.success !== false) {
                    this.saved = true;
                    if (window.Alpine?.store('notify')) window.Alpine.store('notify').success('Statut mis à jour');
                    // Recharger la page pour mettre à jour la timeline et le badge
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (window.Alpine?.store('notify')) window.Alpine.store('notify').error(data.message || 'Erreur');
                }
            })
            .catch(() => {
                this.saving = false;
                if (window.Alpine?.store('notify')) window.Alpine.store('notify').error('Erreur réseau');
            });
        }
    };
}
</script>
@endpush
@endsection
