<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow" style="{{ $level > 0 ? 'margin-left: ' . ($level * 16) . 'px' : '' }}">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-100 to-slate-50 flex items-center justify-center overflow-hidden shadow-sm ring-1 ring-slate-200/50 shrink-0">
            @if($category->image)
                <img src="{{ asset('storage/' . $category->image) }}" alt="" class="w-full h-full object-cover">
            @else
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                @if($level > 0)
                    <span class="text-slate-300 text-xs">└</span>
                @endif
                <p class="font-semibold text-slate-900 truncate">{{ $category->name }}</p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-slate-400">{{ $category->products_count ?? $category->products()->count() }} produits</span>
                @if($category->is_active)
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700">Active</span>
                @else
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Inactive</span>
                @endif
            </div>
        </div>
        <button type="button" @click="$dispatch('open-modal', 'category-edit-{{ $category->id }}')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</div>
