@extends('layouts.admin')

@section('title', 'Avis clients')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Avis clients</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Modérez et répondez aux avis de vos clients</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-[13px]">{{ session('success') }}</div>
    @endif

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="status" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvés</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
            </select>
            <button type="submit" class="h-9 px-4 bg-gray-800 text-white font-medium text-[13px] rounded-lg hover:bg-gray-700 transition-colors">Filtrer</button>
            @if(request('status'))
                <a href="{{ route('admin.reviews.index') }}" class="h-9 px-3 inline-flex items-center text-[13px] text-gray-500 hover:text-red-500 border border-gray-200 rounded-lg hover:border-red-200 transition-colors">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table Desktop --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Auteur</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Note</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Avis</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.products.edit', $review->product) }}" class="text-[13px] text-blue-600 font-medium hover:underline">
                                {{ Str::limit($review->product->name ?? 'N/A', 30) }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-[13px] font-medium text-gray-900">{{ $review->author_name }}</p>
                            <p class="text-[11px] text-gray-400">{{ $review->author_email }}</p>
                            @if($review->is_verified_purchase)
                                <span class="text-[10px] text-green-600 font-medium">✓ Achat vérifié</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex justify-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-5 py-4 max-w-xs">
                            @if($review->title)
                                <p class="text-[13px] font-medium text-gray-900">{{ Str::limit($review->title, 40) }}</p>
                            @endif
                            <p class="text-[12px] text-gray-500 line-clamp-2">{{ Str::limit($review->content, 80) }}</p>
                            @if($review->admin_response)
                                <div class="mt-1.5 pl-2 border-l-2 border-blue-200">
                                    <p class="text-[11px] text-blue-600 italic">{{ Str::limit($review->admin_response, 50) }}</p>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($review->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En attente
                                </span>
                            @elseif($review->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Approuvé
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejeté
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[12px] text-gray-400">{{ $review->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($review->status === 'pending')
                                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                        @csrf
                                        <button type="submit" class="h-7 px-2.5 bg-green-50 text-green-700 text-[11px] font-semibold rounded hover:bg-green-100 transition-colors">Approuver</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                                        @csrf
                                        <button type="submit" class="h-7 px-2.5 bg-red-50 text-red-700 text-[11px] font-semibold rounded hover:bg-red-100 transition-colors">Rejeter</button>
                                    </form>
                                @endif
                                @if(!$review->admin_response)
                                    <form method="POST" action="{{ route('admin.reviews.respond', $review) }}" x-data="{ open: false }">
                                        @csrf
                                        <button type="button" @click="open = true" class="h-7 px-2.5 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded hover:bg-blue-100 transition-colors">Répondre</button>
                                        <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="open = false">
                                            <div class="bg-white rounded-xl p-5 max-w-md w-full shadow-xl">
                                                <h3 class="text-[13px] font-semibold text-gray-900 mb-3">Répondre à l'avis</h3>
                                                <textarea name="admin_response" rows="4" required
                                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Votre réponse..."></textarea>
                                                <div class="mt-3 flex gap-2">
                                                    <button type="submit" class="h-9 px-4 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">Envoyer</button>
                                                    <button type="button" @click="open = false" class="h-9 px-4 bg-gray-100 text-gray-700 font-medium text-[13px] rounded-lg hover:bg-gray-200 transition-colors">Annuler</button>
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
                        <td colspan="7" class="px-5 py-16 text-center">
                            <p class="text-[13px] text-gray-400">Aucun avis pour le moment</p>
                            <p class="text-[12px] text-gray-300 mt-1">Les avis de vos clients apparaîtront ici</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $reviews->links() }}</div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($reviews as $review)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ $review->author_name }}</p>
                        <a href="{{ route('admin.products.edit', $review->product) }}" class="text-[12px] text-blue-600">{{ Str::limit($review->product->name ?? 'N/A', 30) }}</a>
                    </div>
                    @if($review->status === 'pending')
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-700">En attente</span>
                    @elseif($review->status === 'approved')
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">Approuvé</span>
                    @else
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 text-red-700">Rejeté</span>
                    @endif
                </div>
                <div class="flex gap-0.5 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-[13px] text-gray-600 line-clamp-2">{{ Str::limit($review->content, 100) }}</p>
                @if($review->status === 'pending')
                <div class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                        @csrf
                        <button type="submit" class="h-7 px-3 bg-green-50 text-green-700 text-[12px] font-semibold rounded">Approuver</button>
                    </form>
                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                        @csrf
                        <button type="submit" class="h-7 px-3 bg-red-50 text-red-700 text-[12px] font-semibold rounded">Rejeter</button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
                <p class="text-[13px] text-gray-400">Aucun avis</p>
            </div>
        @endforelse
        @if($reviews->hasPages())
        <div class="mt-4">{{ $reviews->links() }}</div>
        @endif
    </div>

</div>
@endsection
