@extends('layouts.front')

@section('title', 'Mes commandes')

@section('content')
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('order-tracking.index') }}" class="hover:text-white transition-colors">Suivi de commande</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Mes commandes</span>
        </nav>
        <h1 class="text-3xl font-bold">Commandes pour {{ $email }}</h1>
        <p class="text-slate-400 mt-1">{{ $orders->count() }} commande(s) trouvée(s)</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto space-y-4">
        @foreach($orders as $order)
        <a href="{{ route('order-tracking.show', ['order_number' => $order->order_number, 'email' => $email]) }}"
           class="block bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="font-bold text-slate-900 text-lg">{{ $order->order_number }}</p>
                    <p class="text-sm text-slate-500">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $colors = [
                            'pending' => 'bg-amber-100 text-amber-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-indigo-100 text-indigo-800',
                            'shipped' => 'bg-purple-100 text-purple-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'refunded' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $colors[$order->status] ?? 'bg-slate-100 text-slate-800' }}">
                        {{ $order->status_label }}
                    </span>
                    <p class="font-bold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
            @if($order->items->count() > 0)
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2 text-sm text-slate-500">
                <span>{{ $order->items->sum('quantity') }} article(s)</span>
                <span class="text-slate-300">|</span>
                <span>{{ $order->items->pluck('name')->take(2)->implode(', ') }}{{ $order->items->count() > 2 ? '...' : '' }}</span>
            </div>
            @endif
        </a>
        @endforeach

        <div class="pt-6 text-center">
            <a href="{{ route('order-tracking.index') }}" class="inline-flex items-center gap-2 px-5 py-3 text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>
</div>
@endsection
