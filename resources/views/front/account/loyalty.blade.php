@extends('layouts.front')

@section('title', 'Programme de fidélité')

@section('content')

<div class="bg-slate-50 border-b border-slate-200 py-8">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-2 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span>/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-slate-700 transition-colors">Mon compte</a>
            <span>/</span>
            <span class="text-slate-700">Fidélité</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Programme de fidélité</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1 min-w-0 space-y-5">

                @php
                    $points = $customer->loyalty_points ?? 0;
                    $pointsValue = (int) floor($points / 100 * 500);
                    $pointsPerPalier = 500;
                    $progress = $pointsPerPalier > 0 ? min(100, ($points % $pointsPerPalier) / $pointsPerPalier * 100) : 0;
                    $pointsToNext = $pointsPerPalier - ($points % $pointsPerPalier);
                @endphp

                {{-- Card solde --}}
                <div class="bg-amber-500 rounded-2xl p-6 text-white">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-amber-100 mb-1">Votre solde actuel</p>
                            <p class="text-5xl font-black">{{ number_format($points) }}</p>
                            <p class="text-sm text-amber-100 mt-1">points de fidélité</p>
                        </div>
                        <div class="bg-white/20 rounded-xl px-5 py-4 text-center">
                            <p class="text-2xl font-bold">{{ number_format($pointsValue, 0, ',', ' ') }} F</p>
                            <p class="text-xs text-amber-100 mt-0.5">valeur en réduction</p>
                        </div>
                    </div>

                    {{-- Barre de progression --}}
                    <div class="mt-5">
                        <div class="flex justify-between text-xs text-amber-100 mb-1.5">
                            <span>Prochain palier</span>
                            <span>{{ $pointsToNext }} pts restants</span>
                        </div>
                        <div class="h-2 bg-white/25 rounded-full overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-500"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Comment ça marche --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="font-semibold text-slate-900 mb-4">Comment gagner des points ?</h2>
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach([
                            ['title' => 'Achetez', 'desc' => number_format(\App\Models\Setting::get('loyalty_points_per_1000', 10)) . ' pts pour 1 000 F dépensés', 'bg' => 'bg-amber-50', 'color' => 'text-amber-600'],
                            ['title' => 'Accumulez', 'desc' => 'Vos points s\'accumulent après chaque commande payée', 'bg' => 'bg-primary-50', 'color' => 'text-primary-600'],
                            ['title' => 'Profitez', 'desc' => '100 pts = 500 F CFA de réduction', 'bg' => 'bg-green-50', 'color' => 'text-green-600'],
                        ] as $item)
                        <div class="text-center p-4 {{ $item['bg'] }} rounded-xl">
                            <p class="font-semibold text-slate-800 mb-1">{{ $item['title'] }}</p>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Historique transactions --}}
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-semibold text-slate-900">Historique des points</h2>
                    </div>

                    @if($transactions->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <p class="font-medium text-slate-700 mb-1">Pas encore de points</p>
                        <p class="text-sm text-slate-500 mb-4">Passez votre première commande pour commencer à gagner des points !</p>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
                            Voir la boutique
                        </a>
                    </div>
                    @else
                    <div class="divide-y divide-slate-100">
                        @foreach($transactions as $tx)
                        <div class="flex items-center justify-between px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 {{ $tx->points > 0 ? 'bg-green-100' : 'bg-red-100' }}">
                                    @if($tx->points > 0)
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                    @else
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $tx->description }}</p>
                                    <p class="text-xs text-slate-400">{{ $tx->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm {{ $tx->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }} pts
                                </p>
                                <p class="text-xs text-slate-400">Solde : {{ number_format($tx->balance_after) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($transactions->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $transactions->links() }}
                    </div>
                    @endif
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
