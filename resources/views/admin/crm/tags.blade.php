@extends('layouts.admin')
@section('title', 'Tags Clients')
@section('page-title', 'Tags & Etiquettes')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Creer un tag --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 mb-4">Nouveau Tag</h3>
        <form method="POST" action="{{ route('admin.crm.tags.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Nom</label>
                <input type="text" name="name" required placeholder="Ex: VIP, Fidele..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
            </div>
            <div class="w-20">
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Couleur</label>
                <input type="color" name="color" value="#6366f1" class="w-full h-[38px] rounded-xl border border-slate-200 cursor-pointer">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Description</label>
                <input type="text" name="description" placeholder="Description optionnelle" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none">
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_auto" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-slate-600">Auto</span>
                </label>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-colors">
                Creer
            </button>
        </form>
    </div>

    {{-- Liste des tags --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Tags existants ({{ $tags->count() }})</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($tags as $tag)
            <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-center gap-4">
                    <span class="w-4 h-4 rounded-full" style="background: {{ $tag->color }}"></span>
                    <div>
                        <p class="font-semibold text-sm text-slate-900">{{ $tag->name }}</p>
                        @if($tag->description)
                        <p class="text-[11px] text-slate-400">{{ $tag->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-600">{{ $tag->customers_count }} clients</span>
                    @if($tag->is_auto)
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full">AUTO</span>
                    @endif
                    <form method="POST" action="{{ route('admin.crm.tags.destroy', $tag) }}" onsubmit="return confirm('Supprimer ce tag ?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <p class="text-sm">Aucun tag. Creez-en un ci-dessus.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
