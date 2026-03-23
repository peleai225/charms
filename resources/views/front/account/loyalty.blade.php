@extends('layouts.front')

@section('title', 'Mes points de fidélité')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-white transition-colors">Mon compte</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Fidélité</span>
        </nav>
        <h1 class="text-3xl font-bold">Programme de fidélité</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Vos points de fidélité</h2>

                {{-- Solde actuel --}}
                <div class="bg-gradient-to-r from-amber-400 to-orange-500 rounded-2xl p-6 text-white mb-6 shadow-lg">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <p class="text-sm opacity-80 mb-1">Votre solde de points</p>
                            <p class="text-5xl font-black">{{ number_format($customer->loyalty_points) }}</p>
                            <p class="text-sm opacity-90 mt-1">points de fidélité</p>
                        </div>
                        <div class="bg-white/20 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold">{{ number_format(floor($customer->loyalty_points / 100 * 500), 0, ',', ' ') }} F</p>
                            <p class="text-xs opacity-80 mt-1">valeur en réduction</p>
                        </div>
                    </div>
                </div>

                {{-- Comment ça marche --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Comment ça marche ?</h2>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-amber-50 rounded-xl">
                            <div class="text-3xl mb-2">🛍️</div>
                            <p class="font-semibold text-slate-800">Achetez</p>
                            <p class="text-sm text-slate-500 mt-1">{{ \App\Models\Setting::get('loyalty_points_per_1000', 10) }} pts pour chaque 1 000 F dépensés</p>
                        </div>
                        <div class="text-center p-4 bg-amber-50 rounded-xl">
                            <div class="text-3xl mb-2">⭐</div>
                            <p class="font-semibold text-slate-800">Accumulez</p>
                            <p class="text-sm text-slate-500 mt-1">Vos points s'accumulent après chaque commande payée</p>
                        </div>
                        <div class="text-center p-4 bg-amber-50 rounded-xl">
                            <div class="text-3xl mb-2">🎁</div>
                            <p class="font-semibold text-slate-800">Profitez</p>
                            <p class="text-sm text-slate-500 mt-1">100 pts = 500 F CFA de réduction sur votre prochaine commande</p>
                        </div>
                    </div>
                </div>

                {{-- Historique des transactions --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-100">
                        <h2 class="text-lg font-semibold text-slate-900">Historique des points</h2>
                    </div>

                    @if($transactions->isEmpty())
                        <div class="p-12 text-center text-slate-500">
                            <div class="text-5xl mb-3">⭐</div>
                            <p class="font-medium">Pas encore de points</p>
                            <p class="text-sm mt-1">Passez votre première commande pour commencer à gagner des points !</p>
                            <a href="{{ route('shop.index') }}" class="inline-block mt-4 px-6 py-2 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700 transition-colors">
                                Voir la boutique
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($transactions as $tx)
                            <div class="flex items-center justify-between p-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $tx->points > 0 ? 'bg-green-100' : 'bg-red-100' }}">
                                        @if($tx->points > 0)
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $tx->description }}</p>
                                        <p class="text-xs text-slate-500">{{ $tx->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold {{ $tx->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $tx->points > 0 ? '+' : '' }}{{ $tx->points }} pts
                                    </p>
                                    <p class="text-xs text-slate-500">Solde : {{ number_format($tx->balance_after) }} pts</p>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="p-4 border-t border-slate-100">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
