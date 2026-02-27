@extends('layouts.front')

@section('title', 'Suivi de commande')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Suivi de commande</h1>
            <p class="text-slate-600">Entrez votre numéro de commande et l'email utilisé lors de l'achat pour suivre votre colis.</p>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <form method="GET" action="{{ route('order-tracking.show') }}" class="space-y-6">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-slate-700 mb-2">Numéro de commande *</label>
                    <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}" required
                        placeholder="Ex: CMD-20260227-ABC123"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    @error('order_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email de la commande *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        placeholder="votre@email.com"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full py-3 px-6 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    Voir le suivi
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            Vous avez un compte ? <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">Connectez-vous</a> pour accéder à toutes vos commandes.
        </p>
    </div>
</div>
@endsection
