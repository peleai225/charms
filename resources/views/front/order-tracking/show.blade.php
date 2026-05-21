@extends('layouts.front')

@section('title', 'Suivi - Commande #' . $order->order_number)

@push('styles')
<style>
    /* Skeleton loader */
    .skeleton-pulse {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
    }
    @keyframes skeleton-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Pulse animation for current step */
    @keyframes pulse-ring {
        0% { transform: scale(1); opacity: 1; }
        80%, 100% { transform: scale(1.8); opacity: 0; }
    }
    .step-pulse::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.3;
        animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* Timeline connector animation */
    @keyframes line-fill {
        from { height: 0; }
        to { height: 100%; }
    }
    .timeline-line-animated {
        animation: line-fill 0.8s ease-out forwards;
    }

    /* Step appear animation */
    @keyframes step-appear {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .step-animate {
        opacity: 0;
        animation: step-appear 0.5s ease-out forwards;
    }

    /* Checkmark draw animation */
    @keyframes draw-check {
        from { stroke-dashoffset: 24; }
        to { stroke-dashoffset: 0; }
    }
    .check-animated path {
        stroke-dasharray: 24;
        stroke-dashoffset: 24;
        animation: draw-check 0.4s ease-out forwards;
    }

    /* Badge glow */
    @keyframes badge-glow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(var(--badge-color), 0.4); }
        50% { box-shadow: 0 0 20px 4px rgba(var(--badge-color), 0.15); }
    }
    .badge-glow {
        animation: badge-glow 3s ease-in-out infinite;
    }

    /* Fade in page */
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-up {
        animation: fade-up 0.6s ease-out forwards;
    }
    .fade-up-delay-1 { animation-delay: 0.1s; opacity: 0; }
    .fade-up-delay-2 { animation-delay: 0.2s; opacity: 0; }
    .fade-up-delay-3 { animation-delay: 0.3s; opacity: 0; }
    .fade-up-delay-4 { animation-delay: 0.4s; opacity: 0; }
    .fade-up-delay-5 { animation-delay: 0.5s; opacity: 0; }

    /* Copy success animation */
    @keyframes copy-success {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    .copy-bounce {
        animation: copy-success 0.3s ease-out;
    }

    /* Cancelled state */
    @keyframes cancelled-shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-2px); }
        40% { transform: translateX(2px); }
        60% { transform: translateX(-1px); }
        80% { transform: translateX(1px); }
    }
</style>
@endpush

@section('content')
@php
    $steps = [
        ['key' => 'pending', 'label' => 'Commande recue', 'date' => $order->created_at, 'icon' => 'inbox'],
        ['key' => 'confirmed', 'label' => 'Paiement confirme', 'date' => $order->paid_at ?? ($order->payment_status === 'paid' ? $order->updated_at : null), 'icon' => 'credit-card'],
        ['key' => 'processing', 'label' => 'En preparation', 'date' => null, 'icon' => 'package'],
        ['key' => 'shipped', 'label' => 'Expediee', 'date' => $order->shipped_at, 'icon' => 'truck'],
        ['key' => 'in_transit', 'label' => 'Livreur en route', 'date' => ($order->status === 'shipped' && $order->tracking_number) ? $order->shipped_at : null, 'icon' => 'navigation'],
        ['key' => 'delivered', 'label' => 'Livree', 'date' => $order->delivered_at, 'icon' => 'check-circle'],
    ];

    $statusOrder = [
        'pending' => 0,
        'confirmed' => 1,
        'processing' => 2,
        'shipped' => 3,
        'in_transit' => 4,
        'delivered' => 5,
        'cancelled' => -1,
        'refunded' => -1,
    ];

    // Determine effective status for timeline
    $effectiveStatus = $order->status;
    if ($order->status === 'shipped' && $order->tracking_number) {
        $effectiveStatus = 'in_transit';
    }

    $currentIndex = $statusOrder[$effectiveStatus] ?? 0;
    $isCancelled = in_array($order->status, ['cancelled', 'refunded']);

    // Badge colors
    $badgeConfig = match($order->status) {
        'delivered' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500', 'glow' => '16, 185, 129'],
        'shipped' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500', 'glow' => '59, 130, 246'],
        'processing' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500', 'glow' => '245, 158, 11'],
        'confirmed' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500', 'glow' => '99, 102, 241'],
        'cancelled', 'refunded' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'dot' => 'bg-red-500', 'glow' => '239, 68, 68'],
        default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-500', 'glow' => '100, 116, 139'],
    };
@endphp

<div x-data="{ loaded: false, copied: false }" x-init="setTimeout(() => loaded = true, 100)" class="min-h-screen bg-gradient-to-b from-slate-50 to-white">

    {{-- Top navigation --}}
    <div class="bg-white border-b border-slate-100">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('order-tracking.index') }}" class="hover:text-slate-900 transition-colors">Suivi</a>
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium">#{{ $order->order_number }}</span>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6 lg:py-10 max-w-3xl">

        {{-- ===== STATUS HERO CARD ===== --}}
        <div class="fade-up mb-8">
            <div class="relative overflow-hidden rounded-3xl {{ $badgeConfig['bg'] }} border {{ $badgeConfig['border'] }} p-6 sm:p-8" style="--badge-color: {{ $badgeConfig['glow'] }}">
                {{-- Background decoration --}}
                <div class="absolute top-0 right-0 w-40 h-40 opacity-10">
                    <svg viewBox="0 0 200 200" fill="none" class="w-full h-full {{ $badgeConfig['text'] }}">
                        <circle cx="100" cy="100" r="80" stroke="currentColor" stroke-width="2" stroke-dasharray="8 4"/>
                        <circle cx="100" cy="100" r="50" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 6"/>
                    </svg>
                </div>

                <div class="relative flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl {{ $isCancelled ? 'bg-red-100' : ($order->status === 'delivered' ? 'bg-emerald-100' : 'bg-white/80') }} flex items-center justify-center shadow-sm badge-glow">
                            @if($isCancelled)
                                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @elseif($order->status === 'delivered')
                                <svg class="w-7 h-7 text-emerald-600 check-animated" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($order->status === 'shipped')
                                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                            @else
                                <svg class="w-7 h-7 {{ $badgeConfig['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $badgeConfig['bg'] }} {{ $badgeConfig['text'] }} border {{ $badgeConfig['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badgeConfig['dot'] }}"></span>
                                {{ $order->status_label }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mb-1">
                            Commande #{{ $order->order_number }}
                        </h1>
                        <p class="text-sm text-slate-500">
                            Passee le {{ $order->created_at->format('d/m/Y') }} a {{ $order->created_at->format('H:i') }}
                        </p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-2xl sm:text-3xl font-bold text-slate-900">{{ format_price($order->total) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $order->items->sum('quantity') }} article{{ $order->items->sum('quantity') > 1 ? 's' : '' }}</p>
                    </div>
                </div>

                {{-- Estimated delivery (if shipped and not yet delivered) --}}
                @if(in_array($order->status, ['shipped', 'processing']) && !$order->delivered_at && $order->shipped_at)
                <div class="mt-5 pt-5 border-t {{ $badgeConfig['border'] }}">
                    <div class="flex items-center gap-2 text-sm {{ $badgeConfig['text'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-medium">Livraison estimee :</span>
                        <span>{{ $order->shipped_at->addDays(3)->format('d/m/Y') }} - {{ $order->shipped_at->addDays(5)->format('d/m/Y') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ===== CANCELLED STATE ===== --}}
        @if($isCancelled)
        <div class="fade-up fade-up-delay-1 mb-8">
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-red-900 mb-2">Commande annulee</h3>
                <p class="text-red-700 text-sm max-w-md mx-auto">
                    Cette commande a ete annulee. Si vous avez des questions, n'hesitez pas a nous contacter.
                </p>
                @if($order->cancellation_reason ?? false)
                <div class="mt-4 p-3 bg-red-100/50 rounded-xl">
                    <p class="text-sm text-red-800"><span class="font-medium">Raison :</span> {{ $order->cancellation_reason }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ===== TIMELINE ===== --}}
        @if(!$isCancelled)
        <div class="fade-up fade-up-delay-1 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-7">
                <h2 class="text-base font-bold text-slate-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Progression de la commande
                </h2>

                <div class="relative">
                    @foreach($steps as $i => $step)
                        @php
                            $isDone = $i <= $currentIndex;
                            $isCurrent = $i === $currentIndex;
                            $isFuture = $i > $currentIndex;
                            $delay = ($i * 0.15) + 0.2;
                        @endphp
                        <div class="step-animate flex gap-4 {{ !$loop->last ? 'pb-8' : '' }}" style="animation-delay: {{ $delay }}s">
                            {{-- Connector line --}}
                            @if(!$loop->last)
                            <div class="absolute ml-[18px] mt-10 w-0.5 {{ $isDone && !$isCurrent ? 'bg-emerald-300' : 'bg-slate-200' }}" style="height: calc(100% - 2.5rem); top: auto; left: 0; position: relative; display: none;"></div>
                            @endif

                            {{-- Step indicator --}}
                            <div class="relative flex-shrink-0 flex flex-col items-center">
                                <div class="relative w-9 h-9 rounded-full flex items-center justify-center transition-all duration-500
                                    {{ $isDone && !$isCurrent ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : '' }}
                                    {{ $isCurrent ? 'bg-white border-2 border-emerald-500 text-emerald-600 shadow-lg shadow-emerald-100' : '' }}
                                    {{ $isFuture ? 'bg-slate-100 text-slate-400 border border-slate-200' : '' }}
                                ">
                                    @if($isCurrent)
                                    <span class="step-pulse absolute inset-0 rounded-full text-emerald-400"></span>
                                    @endif

                                    @if($isDone && !$isCurrent)
                                        <svg class="w-4.5 h-4.5 check-animated" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($step['icon'] === 'inbox')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    @elseif($step['icon'] === 'credit-card')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    @elseif($step['icon'] === 'package')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @elseif($step['icon'] === 'truck')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                    @elseif($step['icon'] === 'navigation')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @elseif($step['icon'] === 'check-circle')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>

                                {{-- Vertical connector --}}
                                @if(!$loop->last)
                                <div class="w-0.5 flex-1 mt-2 rounded-full transition-all duration-700 {{ $isDone && !$isCurrent ? 'bg-emerald-300' : 'bg-slate-200' }} {{ $isDone && !$isCurrent ? 'timeline-line-animated' : '' }}"></div>
                                @endif
                            </div>

                            {{-- Step content --}}
                            <div class="flex-1 min-w-0 {{ !$loop->last ? 'pb-0' : '' }}">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-semibold text-sm {{ $isDone ? 'text-slate-900' : 'text-slate-400' }} {{ $isCurrent ? 'text-emerald-700' : '' }}">
                                        {{ $step['label'] }}
                                    </p>
                                    @if($isDone && $step['date'])
                                        <span class="text-xs text-slate-400 tabular-nums whitespace-nowrap">
                                            {{ $step['date']->format('d/m H:i') }}
                                        </span>
                                    @endif
                                </div>
                                @if($isCurrent && $step['date'])
                                    <p class="text-xs text-emerald-600 mt-0.5">{{ $step['date']->diffForHumans() }}</p>
                                @elseif($isCurrent && !$step['date'])
                                    <p class="text-xs text-emerald-600 mt-0.5">En cours...</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ===== TRACKING NUMBER CARD ===== --}}
        @if($order->tracking_number)
        <div class="fade-up fade-up-delay-2 mb-8" x-data="{ justCopied: false }">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wider mb-1">Numero de suivi</p>
                        <p class="text-lg font-bold text-slate-900 font-mono truncate">{{ $order->tracking_number }}</p>
                        @if($order->shipping_carrier ?? false)
                            <p class="text-sm text-blue-700 mt-1">{{ $order->shipping_carrier }}</p>
                        @endif
                    </div>
                    <button
                        @click="navigator.clipboard.writeText('{{ $order->tracking_number }}'); justCopied = true; setTimeout(() => justCopied = false, 2000)"
                        class="flex-shrink-0 p-2.5 rounded-xl bg-white border border-blue-200 text-blue-600 hover:bg-blue-50 transition-all active:scale-95"
                        :class="{ 'copy-bounce': justCopied }"
                    >
                        <svg x-show="!justCopied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <svg x-show="justCopied" x-cloak class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-blue-500 mt-3" x-show="justCopied" x-transition>Copie dans le presse-papiers !</p>
            </div>
        </div>
        @endif

        {{-- ===== DELIVERY ADDRESS ===== --}}
        <div class="fade-up fade-up-delay-2 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Adresse de livraison
                </h2>
                <div class="text-sm text-slate-600 space-y-1">
                    <p class="font-medium text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                    <p>{{ $order->shipping_address }}</p>
                    <p>{{ $order->shipping_city }}{{ $order->shipping_country ? ', ' . $order->shipping_country : '' }}</p>
                    @if($order->shipping_phone)
                    <p class="flex items-center gap-1.5 text-slate-500 pt-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $order->shipping_phone }}
                    </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== ORDER ITEMS ===== --}}
        <div class="fade-up fade-up-delay-3 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Articles ({{ $order->items->count() }})
                    </h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                    <div class="p-4 sm:p-5 flex gap-4 hover:bg-slate-50/50 transition-colors">
                        {{-- Product image --}}
                        <div class="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-xl overflow-hidden">
                            @if($item->product && $item->product->images && $item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->path) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            @elseif($item->product && $item->product->primary_image_url)
                                <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-100">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Product info --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 text-sm sm:text-base truncate">{{ $item->product?->name ?? $item->name }}</p>
                            @if($item->variant_name)
                                <p class="text-xs text-slate-500 mt-0.5">{{ $item->variant_name }}</p>
                            @endif
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">Qte: {{ $item->quantity }}</span>
                                <span class="text-xs text-slate-400">{{ format_price($item->unit_price) }} / unite</span>
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="flex-shrink-0 text-right">
                            <p class="font-bold text-slate-900 text-sm sm:text-base">{{ format_price($item->total) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Totals breakdown --}}
                <div class="bg-slate-50 p-5 sm:p-6 space-y-3">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Sous-total</span>
                        <span>{{ format_price($order->subtotal) }}</span>
                    </div>
                    @if($order->shipping_amount > 0)
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Livraison</span>
                        <span>{{ format_price($order->shipping_amount) }}</span>
                    </div>
                    @else
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Livraison</span>
                        <span class="text-emerald-600 font-medium">Gratuite</span>
                    </div>
                    @endif
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-emerald-600">
                        <span>Reduction</span>
                        <span>-{{ format_price($order->discount_amount) }}</span>
                    </div>
                    @endif
                    <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">Total</span>
                        <span class="text-xl font-bold text-slate-900">{{ format_price($order->total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PAYMENT INFO ===== --}}
        <div class="fade-up fade-up-delay-4 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Paiement
                </h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Statut du paiement</p>
                        <p class="font-medium text-slate-900 mt-0.5">
                            @if($order->payment_status === 'paid')
                                <span class="inline-flex items-center gap-1.5 text-emerald-700">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    Paye
                                </span>
                            @elseif($order->payment_status === 'pending')
                                <span class="inline-flex items-center gap-1.5 text-amber-700">
                                    <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                                    En attente
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-slate-700">
                                    <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($order->paid_at)
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Paye le</p>
                        <p class="text-sm text-slate-600">{{ $order->paid_at->format('d/m/Y a H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== HELP SECTION ===== --}}
        <div class="fade-up fade-up-delay-5 mb-8">
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200 p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-900 mb-1">Besoin d'aide ?</h3>
                        <p class="text-sm text-slate-600 mb-4">Une question sur votre commande ? Notre equipe est la pour vous aider.</p>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->shipping_phone ?? '') }}?text={{ urlencode('Bonjour, je souhaite avoir des informations sur ma commande #' . $order->order_number) }}"
                           target="_blank"
                           rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-all active:scale-95 shadow-sm shadow-emerald-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Contacter via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== BACK LINK ===== --}}
        <div class="fade-up fade-up-delay-5 text-center pb-8">
            <a href="{{ route('order-tracking.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Suivre une autre commande
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Smooth skeleton removal on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.skeleton-pulse').forEach(function(el) {
            el.classList.remove('skeleton-pulse');
        });
    });
</script>
@endpush
