<form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}" required
            class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
        @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('description', $category->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Catégorie parente</label>
        <select name="parent_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <option value="">Aucune (catégorie racine)</option>
            @foreach($tree as $cat)
                @if($cat->id != $category->id)
                <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endif
                @foreach($cat->children ?? [] as $child)
                    @if($child->id != $category->id)
                    <option value="{{ $child->id }}" {{ old('parent_id', $category->parent_id) == $child->id ? 'selected' : '' }}>
                        &nbsp;&nbsp;└ {{ $child->name }}
                    </option>
                    @endif
                @endforeach
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Image</label>
        @if($category->image)
            <div class="mb-2">
                <img src="{{ asset('storage/' . $category->image) }}" alt="" class="w-16 h-16 object-cover rounded-lg">
            </div>
        @endif
        <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border border-slate-300 rounded-xl text-sm">
    </div>

    <div class="flex gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-slate-700">Active</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}
                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-slate-700">Mise en avant</span>
        </label>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
        <a href="{{ route('admin.categories.edit', $category) }}" class="text-sm text-slate-500 hover:text-slate-700">Formulaire complet →</a>
        <div class="flex gap-3">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Enregistrer
            </button>
            <button type="button"
                    @click="$dispatch('close-modal', 'category-edit-{{ $category->id }}')"
                    class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                Annuler
            </button>
        </div>
    </div>
</form>
