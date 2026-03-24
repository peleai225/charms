@extends('layouts.admin')

@section('title', 'Avis clients')
@section('page-title', 'Avis clients')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="status" class="px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvés</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white font-medium rounded-xl hover:bg-slate-700 transition-colors">Filtrer</button>
            @if(request('status'))
                <a href="{{ route('admin.reviews.index') }}" class="p-2.5 text-slate-400 hover:text-red-500 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Desktop Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-slate-100/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Auteur</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Note</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Avis</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reviews as $review)
                    <tr class="group hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.products.edit', $review->product) }}" class="text-blue-600 hover:text-blue-700 font-medium hover:underline transition-colors">
                                {{ Str::limit($review->product->name ?? 'N/A', 30) }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $review->author_name }}</p>
                            <p class="text-xs text-slate-400">{{ $review->author_email }}</p>
                            @if($review->is_verified_purchase)
                                <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    Achat vérifié
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            @if($review->title)
                                <p class="font-medium text-slate-900 text-sm">{{ Str::limit($review->title, 40) }}</p>
                            @endif
                            <p class="text-sm text-slate-500 line-clamp-2">{{ Str::limit($review->content, 80) }}</p>
                            @if($review->admin_response)
                                <div class="mt-2 pl-3 border-l-2 border-blue-200">
                                    <p class="text-xs text-blue-600 italic">{{ Str::limit($review->admin_response, 50) }}</p>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($review->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    En attente
                                </span>
                            @elseif($review->status === 'approved')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Approuvé
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-50 text-red-700 ring-1 ring-red-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Rejeté
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">{{ $review->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if($review->status === 'pending')
                                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-100 ring-1 ring-emerald-100 transition-colors">Approuver</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-100 ring-1 ring-red-100 transition-colors">Rejeter</button>
                                    </form>
                                @endif
                                @if(!$review->admin_response)
                                    <form method="POST" action="{{ route('admin.reviews.respond', $review) }}" class="inline" x-data="{ open: false }">
                                        @csrf
                                        <button type="button" @click="open = true" class="px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg hover:bg-blue-100 ring-1 ring-blue-100 transition-colors">Répondre</button>
                                        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open = false">
                                            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                                                <h3 class="font-semibold text-slate-900 mb-4">Répondre à l'avis</h3>
                                                <textarea name="admin_response" rows="4" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Votre réponse..."></textarea>
                                                <div class="mt-4 flex gap-2">
                                                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 font-medium transition-all">Envoyer</button>
                                                    <button type="button" @click="open = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 font-medium transition-colors">Annuler</button>
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
                        <td colspan="7" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500/10 to-yellow-500/10 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                </div>
                                <p class="font-semibold text-slate-800 text-lg">Aucun avis pour le moment</p>
                                <p class="text-sm text-slate-500 mt-1">Les avis de vos clients apparaîtront ici.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $reviews->links() }}
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
        @forelse($reviews as $review)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="font-medium text-slate-900 text-sm">{{ $review->author_name }}</p>
                        <a href="{{ route('admin.products.edit', $review->product) }}" class="text-xs text-blue-600">{{ Str::limit($review->product->name ?? 'N/A', 30) }}</a>
                    </div>
                    @if($review->status === 'pending')
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700">En attente</span>
                    @elseif($review->status === 'approved')
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700">Approuvé</span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-50 text-red-700">Rejeté</span>
                    @endif
                </div>
                <div class="flex gap-0.5 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-sm text-slate-600 line-clamp-2">{{ Str::limit($review->content, 100) }}</p>
                @if($review->status === 'pending')
                <div class="mt-3 pt-3 border-t border-slate-100 flex gap-2">
                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">Approuver</button>
                    </form>
                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 text-xs font-semibold rounded-lg">Rejeter</button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
                <p class="font-semibold text-slate-800">Aucun avis</p>
            </div>
        @endforelse
        <div class="mt-4">{{ $reviews->links() }}</div>
    </div>
</div>
@endsection
