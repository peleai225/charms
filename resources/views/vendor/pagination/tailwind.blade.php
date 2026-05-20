@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3">
        {{-- Mobile : Précédent/Suivant simplifiés --}}
        <div class="flex sm:hidden flex-1 items-center justify-between gap-3">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-default select-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Précédent
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Précédent
                </a>
            @endif

            <span class="text-xs text-slate-500 font-medium">
                Page <span class="font-bold text-slate-900">{{ $paginator->currentPage() }}</span> / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-md shadow-primary-600/25 hover:shadow-lg hover:shadow-primary-600/40 transition-all">
                    Suivant
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-default select-none">
                    Suivant
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- Desktop : pagination complète --}}
        <div class="hidden sm:flex flex-1 items-center justify-between">
            <div>
                <p class="text-sm text-slate-600">
                    Affichage de
                    <span class="font-bold text-slate-900">{{ $paginator->firstItem() }}</span>
                    à
                    <span class="font-bold text-slate-900">{{ $paginator->lastItem() }}</span>
                    sur
                    <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
                    résultats
                </p>
            </div>

            <div>
                <span class="relative inline-flex items-center gap-1.5">
                    {{-- Précédent --}}
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex items-center justify-center w-10 h-10 text-slate-300 bg-slate-50 border border-slate-200 rounded-xl cursor-default">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                           class="inline-flex items-center justify-center w-10 h-10 text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-primary-400 hover:text-primary-600 rounded-xl shadow-sm transition-all"
                           aria-label="@lang('pagination.previous')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Numéros de page --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-2 text-sm font-medium text-slate-400">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 text-sm font-bold text-white bg-primary-600 border border-primary-600 rounded-xl shadow-md shadow-primary-600/25" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                       class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-primary-400 hover:text-primary-600 rounded-xl shadow-sm transition-all">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Suivant --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                           class="inline-flex items-center justify-center w-10 h-10 text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-primary-400 hover:text-primary-600 rounded-xl shadow-sm transition-all"
                           aria-label="@lang('pagination.next')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-10 h-10 text-slate-300 bg-slate-50 border border-slate-200 rounded-xl cursor-default">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
