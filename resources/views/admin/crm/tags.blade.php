@extends('layouts.admin')
@section('title', 'Tags Clients')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tags & Étiquettes</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Organisez vos clients avec des étiquettes personnalisées</p>
        </div>
        <a href="{{ route('admin.crm.dashboard') }}" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
            ← CRM
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-[13px] text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Créer un tag --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Nouveau Tag</h3>
        <form method="POST" action="{{ route('admin.crm.tags.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[180px]">
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom</label>
                <input type="text" name="name" required placeholder="Ex: VIP, Fidèle..."
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="w-20">
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Couleur</label>
                <input type="color" name="color" value="#2563eb"
                    class="w-full h-9 rounded-lg border border-gray-200 cursor-pointer px-1">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Description</label>
                <input type="text" name="description" placeholder="Description optionnelle"
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex items-center gap-2 pb-0.5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_auto" value="1"
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-600">Tag automatique</span>
                </label>
            </div>
            <button type="submit" class="h-9 px-4 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">
                Créer
            </button>
        </form>
    </div>

    {{-- Liste des tags --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[13px] font-semibold text-gray-900">Tags existants <span class="text-gray-400 font-normal">({{ $tags->count() }})</span></h3>
        </div>

        @if($tags->isEmpty())
        <div class="py-16 text-center">
            <p class="text-[13px] text-gray-400 mb-1">Aucun tag créé</p>
            <p class="text-[12px] text-gray-300">Créez votre premier tag ci-dessus</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($tags as $tag)
            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" style="background: {{ $tag->color }}"></span>
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ $tag->name }}</p>
                        @if($tag->description)
                        <p class="text-[11px] text-gray-400">{{ $tag->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[13px] text-gray-500">{{ $tag->customers_count }} client(s)</span>
                    @if($tag->is_auto)
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full tracking-wide">AUTO</span>
                    @endif
                    <form method="POST" action="{{ route('admin.crm.tags.destroy', $tag) }}"
                        onsubmit="return confirm('Supprimer ce tag ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
