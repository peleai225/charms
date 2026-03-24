<tr class="group hover:bg-blue-50/30 transition-colors">
    <td class="px-6 py-4">
        <div class="flex items-center gap-3" style="padding-left: {{ $level * 24 }}px">
            @if($level > 0)
                <span class="text-slate-300 text-sm">└─</span>
            @endif
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-50 flex items-center justify-center overflow-hidden shadow-sm ring-1 ring-slate-200/50">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="" class="w-full h-full object-cover">
                @else
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                @endif
            </div>
            <div>
                <p class="font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">{{ $category->name }}</p>
                @if($category->description)
                    <p class="text-xs text-slate-400 line-clamp-1">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </td>
    <td class="px-6 py-4 text-xs text-slate-400 font-mono">{{ $category->slug }}</td>
    <td class="px-6 py-4 text-center">
        <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg">
            {{ $category->products_count ?? $category->products()->count() }}
        </span>
    </td>
    <td class="px-6 py-4 text-center">
        <div class="flex items-center justify-center gap-1.5">
            @if($category->is_active)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-slate-50 text-slate-600 ring-1 ring-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Inactive
                </span>
            @endif
            @if($category->is_featured)
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </span>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <a href="{{ route('admin.categories.index', ['open_modal' => 'create', 'parent_id' => $category->id]) }}"
               class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
               title="Ajouter une sous-catégorie">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
            <button type="button" @click="$dispatch('open-modal', 'category-edit-{{ $category->id }}')" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Modifier">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </form>
        </div>
    </td>
</tr>
