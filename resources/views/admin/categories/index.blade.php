@extends('layouts.admin')

@section('title', 'Catégories')
@section('page-title', 'Catégories')

@section('content')
<div class="space-y-6" x-data>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-slate-600">{{ $categories->count() }} catégorie(s)</p>
        </div>
        <button type="button"
                @click="$dispatch('open-modal', 'category-create')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouvelle catégorie
        </button>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Produits</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
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
                            <td colspan="5" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-slate-700">Aucune catégorie</p>
                                    <p class="text-sm text-slate-500 mt-1">Créez votre première catégorie pour organiser vos produits.</p>
                                    <button type="button"
                                            @click="$dispatch('open-modal', 'category-create')"
                                            class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Nouvelle catégorie
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals édition -->
    @foreach($categories as $category)
    <x-admin.modal id="category-edit-{{ $category->id }}" title="Modifier {{ $category->name }}" :open="request('open_modal') === 'edit' && request('category_id') == $category->id">
        @include('admin.categories.partials.edit-form', ['category' => $category, 'tree' => $tree])
    </x-admin.modal>
    @endforeach

    <!-- Modal création -->
    <x-admin.modal id="category-create" title="Nouvelle catégorie" :open="request('open_modal') === 'create' || ($errors->any() && request('open_modal') !== 'edit')">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="modal_name" class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                <input type="text" name="name" id="modal_name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="modal_description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" id="modal_description" rows="2"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="modal_parent_id" class="block text-sm font-medium text-slate-700 mb-1">Catégorie parente</label>
                <select name="parent_id" id="modal_parent_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
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
                <label for="modal_image" class="block text-sm font-medium text-slate-700 mb-1">Image</label>
                <input type="file" name="image" id="modal_image" accept="image/*"
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm">
            </div>

            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">Mise en avant</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-200">
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                    Créer la catégorie
                </button>
                <button type="button"
                        @click="$dispatch('close-modal', 'category-create')"
                        class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </x-admin.modal>
</div>
@endsection
