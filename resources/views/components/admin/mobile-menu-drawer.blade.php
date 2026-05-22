{{-- Mobile Menu Drawer - Grille type Shopify/iOS --}}
<div
    x-data="mobileMenuDrawer()"
    @mobile-menu-toggle.window="menuOpen = $event.detail.open"
    x-cloak
    class="lg:hidden"
>
    {{-- Overlay --}}
    <div
        x-show="menuOpen"
        @click="close()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60]"
    ></div>

    {{-- Bottom Sheet --}}
    <div
        x-show="menuOpen"
        x-transition:enter="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-400"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 left-0 right-0 z-[70] bg-white rounded-t-[28px] shadow-2xl max-h-[80vh] overflow-hidden flex flex-col"
        style="padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 4rem);"
        @touchstart.passive="dragStart($event)"
        @touchmove.passive="dragMove($event)"
        @touchend.passive="dragEnd()"
    >
        {{-- Handle Bar --}}
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 bg-slate-300/80 rounded-full"></div>
        </div>

        {{-- Header compact --}}
        <div class="px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-md">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-500">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</p>
                </div>
            </div>
            <button @click="close()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 active:scale-90 transition-transform">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Grid Content --}}
        <div class="flex-1 overflow-y-auto px-4 pb-4" style="-webkit-overflow-scrolling: touch;">

            {{-- Raccourcis rapides - Grille 4 colonnes --}}
            <div class="grid grid-cols-4 gap-2 py-3">
                @php
                    $quickItems = [
                        ['route' => 'admin.stock.index', 'match' => 'admin.stock.*', 'label' => 'Stock', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4', 'color' => 'from-teal-500 to-emerald-600', 'text' => 'text-teal-700'],
                        ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'label' => 'Rapports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'from-blue-500 to-indigo-600', 'text' => 'text-blue-700'],
                        ['route' => 'admin.coupons.index', 'match' => 'admin.coupons.*', 'label' => 'Promos', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color' => 'from-amber-500 to-orange-600', 'text' => 'text-amber-700', 'role' => ['admin','manager']],
                        ['route' => 'admin.scanner.index', 'match' => 'admin.scanner.*', 'label' => 'Caisse', 'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z', 'color' => 'from-lime-500 to-green-600', 'text' => 'text-lime-700'],
                        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Catégories', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color' => 'from-violet-500 to-purple-600', 'text' => 'text-violet-700'],
                        ['route' => 'admin.refunds.index', 'match' => 'admin.refunds.*', 'label' => 'Retours', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'color' => 'from-red-500 to-rose-600', 'text' => 'text-red-700'],
                        ['route' => 'admin.whatsapp.index', 'match' => 'admin.whatsapp.*', 'label' => 'WhatsApp', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'from-green-500 to-emerald-600', 'text' => 'text-green-700'],
                        ['route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'label' => 'Avis', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'from-yellow-500 to-amber-600', 'text' => 'text-yellow-700'],
                    ];
                @endphp

                @foreach($quickItems as $item)
                    @if(!isset($item['role']) || in_array(auth()->user()->role, $item['role']))
                    <a href="{{ route($item['route']) }}" @click="close()"
                       class="flex flex-col items-center gap-1.5 py-3 rounded-2xl transition-all active:scale-90 {{ request()->routeIs($item['match']) ? 'bg-slate-100' : '' }}">
                        <div class="w-11 h-11 rounded-[14px] bg-gradient-to-br {{ $item['color'] }} flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        </div>
                        <span class="text-[10px] font-semibold {{ $item['text'] }} text-center leading-tight">{{ $item['label'] }}</span>
                    </a>
                    @endif
                @endforeach
            </div>

            {{-- Séparateur --}}
            <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent my-2"></div>

            {{-- Administration --}}
            @if(auth()->user()->role === 'admin')
            <div class="py-3">
                <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Administration</p>
                <div class="grid grid-cols-4 gap-2">
                    @php
                        $adminItems = [
                            ['route' => 'admin.suppliers.index', 'match' => 'admin.suppliers.*', 'label' => 'Fournisseurs', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'from-slate-500 to-gray-600', 'text' => 'text-slate-600'],
                            ['route' => 'admin.accounting.index', 'match' => 'admin.accounting.*', 'label' => 'Compta', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'from-emerald-500 to-teal-600', 'text' => 'text-emerald-700'],
                            ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Équipe', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'from-purple-500 to-violet-600', 'text' => 'text-purple-700'],
                            ['route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'label' => 'Réglages', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'color' => 'from-gray-500 to-slate-600', 'text' => 'text-gray-600'],
                            ['route' => 'admin.banners.index', 'match' => 'admin.banners.*', 'label' => 'Bannières', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'from-rose-500 to-pink-600', 'text' => 'text-rose-700'],
                            ['route' => 'admin.import-export.index', 'match' => 'admin.import-export.*', 'label' => 'Import', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'color' => 'from-indigo-500 to-blue-600', 'text' => 'text-indigo-700'],
                            ['route' => 'admin.system.index', 'match' => 'admin.system.*', 'label' => 'Système', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'from-cyan-500 to-sky-600', 'text' => 'text-cyan-700'],
                            ['route' => 'admin.barcodes.index', 'match' => 'admin.barcodes.*', 'label' => 'Codes-barres', 'icon' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z', 'color' => 'from-sky-500 to-cyan-600', 'text' => 'text-sky-700'],
                        ];
                    @endphp

                    @foreach($adminItems as $item)
                    <a href="{{ route($item['route']) }}" @click="close()"
                       class="flex flex-col items-center gap-1.5 py-3 rounded-2xl transition-all active:scale-90 {{ request()->routeIs($item['match']) ? 'bg-slate-100' : '' }}">
                        <div class="w-11 h-11 rounded-[14px] bg-gradient-to-br {{ $item['color'] }} flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        </div>
                        <span class="text-[10px] font-semibold {{ $item['text'] }} text-center leading-tight">{{ $item['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @elseif(in_array(auth()->user()->role, ['manager']))
            <div class="py-3">
                <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Gestion</p>
                <div class="grid grid-cols-4 gap-2">
                    <a href="{{ route('admin.suppliers.index') }}" @click="close()" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl transition-all active:scale-90">
                        <div class="w-11 h-11 rounded-[14px] bg-gradient-to-br from-slate-500 to-gray-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-600 text-center leading-tight">Fournisseurs</span>
                    </a>
                    <a href="{{ route('admin.banners.index') }}" @click="close()" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl transition-all active:scale-90">
                        <div class="w-11 h-11 rounded-[14px] bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-[10px] font-semibold text-rose-700 text-center leading-tight">Bannières</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Voir le site --}}
            <div class="pt-2 pb-2">
                <a href="{{ route('home') }}" target="_blank" @click="close()"
                   class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-slate-900 text-white font-semibold text-sm active:scale-[0.98] transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Voir la boutique
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function mobileMenuDrawer() {
    return {
        menuOpen: false,
        dragStartY: 0,
        dragging: false,

        close() {
            this.menuOpen = false;
            this.$dispatch('mobile-menu-toggle', { open: false });
        },

        dragStart(e) {
            this.dragStartY = e.touches[0].clientY;
            this.dragging = true;
        },

        dragMove(e) {
            if (!this.dragging) return;
            const diff = e.touches[0].clientY - this.dragStartY;
            if (diff > 60) {
                this.close();
                this.dragging = false;
            }
        },

        dragEnd() {
            this.dragging = false;
        }
    };
}
</script>
