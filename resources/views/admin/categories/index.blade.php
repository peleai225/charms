@extends('layouts.admin')

@section('title', 'Catégories')

@section('content')
<div class="p-4 sm:p-6 space-y-5" x-data>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Catégories</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">{{ $categories->count() }} catégorie(s) au total</p>
        </div>
        <button type="button" @click="$dispatch('open-modal', 'category-create')"
            class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle catégorie
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Catégorie</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Slug</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produits</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($tree as $category)
                        @include('admin.categories.partials.row', ['category' => $category, 'level' => 0])
                        @foreach($category->children as $child)
                            @include('admin.categories.partials.row', ['category' => $child, 'level' => 1])
                            @foreach($child->children as $grandchild)
                                @include('admin.categories.partials.row', ['category' => $grandchild, 'level' => 2])
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <p class="text-[13px] text-gray-400 mb-1">Aucune catégorie</p>
                                <p class="text-[12px] text-gray-300 mb-4">Créez votre première catégorie pour organiser vos produits</p>
                                <button type="button" @click="$dispatch('open-modal', 'category-create')"
                                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mx-auto">
                                    Nouvelle catégorie
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($tree as $category)
            @include('admin.categories.partials.mobile-card', ['category' => $category, 'level' => 0])
            @foreach($category->children as $child)
                @include('admin.categories.partials.mobile-card', ['category' => $child, 'level' => 1])
                @foreach($child->children as $grandchild)
                    @include('admin.categories.partials.mobile-card', ['category' => $grandchild, 'level' => 2])
                @endforeach
            @endforeach
        @empty
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
                <p class="text-[13px] text-gray-400">Aucune catégorie créée.</p>
            </div>
        @endforelse
    </div>

    {{-- Modals édition --}}
    @foreach($categories as $category)
    <x-admin.modal id="category-edit-{{ $category->id }}" title="Modifier {{ $category->name }}" :open="request('open_modal') === 'edit' && request('category_id') == $category->id">
        @include('admin.categories.partials.edit-form', ['category' => $category, 'tree' => $tree])
    </x-admin.modal>
    @endforeach

    {{-- Modal création --}}
    <x-admin.modal id="category-create" title="Nouvelle catégorie" :open="request('open_modal') === 'create' || ($errors->any() && request('open_modal') !== 'edit')">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="modal_name" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nom *</label>
                <input type="text" name="name" id="modal_name" value="{{ old('name') }}" required
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('name')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="modal_description" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</label>
                <textarea name="description" id="modal_description" rows="2"
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="modal_parent_id" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Catégorie parente</label>
                <select name="parent_id" id="modal_parent_id"
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Aucune (catégorie racine)</option>
                    @foreach($categories->whereNull('parent_id') as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_id', request('parent_id')) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @foreach($cat->children ?? [] as $child)
                            <option value="{{ $child->id }}" {{ old('parent_id', request('parent_id')) == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;└ {{ $child->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_image" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Image</label>
                <input type="file" name="image" id="modal_image" accept="image/*"
                    class="w-full text-[13px] border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[12px] file:font-medium file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200">
            </div>

            <div class="flex gap-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-700">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-[13px] text-gray-700">Mise en avant</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">
                    Créer la catégorie
                </button>
                <button type="button" @click="$dispatch('close-modal', 'category-create')"
                    class="h-9 px-4 bg-gray-100 text-gray-700 font-medium text-[13px] rounded-lg hover:bg-gray-200 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </x-admin.modal>

</div>
@endsection
