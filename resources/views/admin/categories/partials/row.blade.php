<tr class="hover:bg-slate-50">
    <td class="px-6 py-4">
        <div class="flex items-center gap-3" style="padding-left: {{ $level * 24 }}px">
            @if($level > 0)
                <span class="text-slate-300">└─</span>
            @endif
            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="" class="w-full h-full object-cover">
                @else
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                @endif
            </div>
            <div>
                <p class="font-medium text-slate-900">{{ $category->name }}</p>
                @if($category->description)
                    <p class="text-sm text-slate-500 line-clamp-1">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-slate-600 font-mono">{{ $category->slug }}</td>
    <td class="px-6 py-4 text-sm">
        <span class="font-medium text-slate-900">{{ $category->products_count ?? $category->products()->count() }}</span>
    </td>
    <td class="px-6 py-4">
        @if($category->is_active)
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Active</span>
        @else
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Inactive</span>
        @endif
        @if($category->is_featured)
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700 ml-1">Featured</span>
        @endif
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.categories.index', ['open_modal' => 'create', 'parent_id' => $category->id]) }}"
               class="p-2 text-slate-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
               title="Ajouter une sous-catégorie">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
            <button type="button" @click="$dispatch('open-modal', 'category-edit-{{ $category->id }}')" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </form>
        </div>
    </td>
</tr>

