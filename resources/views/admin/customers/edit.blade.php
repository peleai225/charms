@extends('layouts.admin')

@section('title', 'Modifier - ' . $customer->full_name)
@section('page-title', 'Modifier le client')

@section('content')
<div class="max-w-3xl">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.customers.show', $customer) }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Modifier le client</h1>
            <p class="text-slate-500">{{ $customer->full_name }}</p>
        </div>
    </div>

    <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Informations personnelles</h2>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('first_name') border-red-500 @enderror">
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('last_name') border-red-500 @enderror">
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}"
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Statut du compte</h2>
            
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                <select id="status" name="status" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    <option value="blocked" {{ old('status', $customer->status) === 'blocked' ? 'selected' : '' }}>Bloqué</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Un client bloqué ne pourra plus passer de commande.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Notes internes</h2>
            
            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="4"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none"
                    placeholder="Notes visibles uniquement par l'équipe admin...">{{ old('notes', $customer->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.')) document.getElementById('delete-form').submit();"
                class="px-4 py-2 text-red-600 hover:text-red-700 font-medium">
                Supprimer le client
            </button>
            
            <div class="flex gap-3">
                <a href="{{ route('admin.customers.show', $customer) }}" class="px-6 py-2 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
    
    <form id="delete-form" action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

