@extends('layouts.admin')

@section('title', 'Avis clients')
@section('page-title', 'Avis clients')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvés</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl">Filtrer</button>
        </form>
    </div>

    <!-- Liste -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Auteur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Note</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Avis</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.products.edit', $review->product) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                {{ Str::limit($review->product->name ?? 'N/A', 30) }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $review->author_name }}</p>
                            <p class="text-sm text-slate-500">{{ $review->author_email }}</p>
                            @if($review->is_verified_purchase)
                                <span class="text-xs text-green-600 font-medium">✓ Achat vérifié</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            @if($review->title)
                                <p class="font-medium text-slate-900">{{ Str::limit($review->title, 40) }}</p>
                            @endif
                            <p class="text-sm text-slate-600">{{ Str::limit($review->content, 80) }}</p>
                            @if($review->admin_response)
                                <p class="mt-2 text-sm text-blue-600 italic">Réponse: {{ Str::limit($review->admin_response, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($review->status === 'pending')
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-medium rounded-full">En attente</span>
                            @elseif($review->status === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Approuvé</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Rejeté</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $review->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($review->status === 'pending')
                                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-green-100 text-green-700 text-sm font-medium rounded-lg hover:bg-green-200">Approuver</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200">Rejeter</button>
                                    </form>
                                @endif
                                @if(!$review->admin_response)
                                    <form method="POST" action="{{ route('admin.reviews.respond', $review) }}" class="inline" x-data="{ open: false }">
                                        @csrf
                                        <button type="button" @click="open = true" class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200">Répondre</button>
                                        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open = false">
                                            <div class="bg-white rounded-2xl p-6 max-w-md w-full">
                                                <h3 class="font-semibold text-slate-900 mb-4">Répondre à l'avis</h3>
                                                <textarea name="admin_response" rows="4" required class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Votre réponse..."></textarea>
                                                <div class="mt-4 flex gap-2">
                                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Envoyer</button>
                                                    <button type="button" @click="open = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl">Annuler</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">Aucun avis pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
