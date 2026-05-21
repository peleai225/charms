{{-- Mobile Menu Drawer / Bottom Sheet Premium --}}
<div
    x-data="mobileMenuDrawer()"
    @mobile-menu-toggle.window="menuOpen = $event.detail.open"
    x-cloak
    class="lg:hidden"
>
    {{-- Overlay --}}
    <div
        x-show="menuOpen"
        @click="menuOpen = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60]"
        style="padding-bottom: env(safe-area-inset-bottom);"
    ></div>

    {{-- Bottom Sheet Drawer --}}
    <div
        x-show="menuOpen"
        @click.away="menuOpen = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 left-0 right-0 z-[70] bg-white rounded-t-3xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col"
        style="padding-bottom: calc(env(safe-area-inset-bottom) + 4rem);"
    >
        {{-- Handle Bar --}}
        <div class="flex justify-center pt-3 pb-2">
            <div class="w-12 h-1.5 bg-slate-300 rounded-full"></div>
        </div>

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Menu complet</h3>
                <p class="text-xs text-slate-500 mt-0.5">Accédez à tous les modules</p>
            </div>
            <button
                @click="menuOpen = false"
                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors flex items-center justify-center text-slate-500"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Content - Scrollable --}}
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">

            {{-- Catalogue Section --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Catalogue</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.categories.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-violet-500/10">
                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Catégories</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    <a href="{{ route('admin.attributes.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-fuchsia-500/10">
                            <svg class="w-5 h-5 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Attributs</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif

                    <a href="{{ route('admin.barcodes.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.barcodes.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-cyan-500/10">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Codes-barres</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('admin.scanner.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.scanner.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-lime-500/10">
                            <svg class="w-5 h-5 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Scanner / Caisse</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Ventes Section --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Ventes</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.refunds.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-red-500/10">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Remboursements</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('admin.reviews.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-amber-500/10">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Avis clients</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    <a href="{{ route('admin.coupons.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-yellow-500/10">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Codes promo</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Stock Section --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Stock</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.stock.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-teal-500/10">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Gestion stock</span>
                        @php
                            $stockAlerts = \App\Models\Product::active()
                                ->where('track_stock', true)
                                ->where(function($q) {
                                    $q->where('stock_quantity', 0)
                                      ->orWhereColumn('stock_quantity', '<=', 'stock_alert_threshold');
                                })->count();
                        @endphp
                        @if($stockAlerts > 0)
                            <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-700 rounded-full">{{ $stockAlerts }}</span>
                        @endif
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    <a href="{{ route('admin.suppliers.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-slate-500/10">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Fournisseurs</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Finances Section --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Finances</p>
                <div class="space-y-1">
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.accounting.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.accounting.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-green-500/10">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Comptabilité</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif

                    <a href="{{ route('admin.reports.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-blue-500/10">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Rapports</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Contenu Section --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Contenu</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.whatsapp.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-green-500/10">
                            <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">WhatsApp Business</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    <a href="{{ route('admin.banners.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-rose-500/10">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Bannières</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Configuration Section (Admin only) --}}
            @if(auth()->user()->role === 'admin')
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Configuration</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.import-export.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.import-export.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-indigo-500/10">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Import / Export</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('admin.users.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-purple-500/10">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Utilisateurs</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-slate-500/10">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Paramètres</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('admin.system.index') }}" @click="menuOpen = false" class="menu-item {{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
                        <div class="icon-wrapper bg-emerald-500/10">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="flex-1 font-medium">Système</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endif

            {{-- Bottom Padding --}}
            <div class="h-4"></div>
        </div>
    </div>
</div>

<script>
function mobileMenuDrawer() {
    return {
        menuOpen: false
    };
}
</script>

<style>
.menu-item {
    @apply flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 text-slate-700;
    @apply hover:bg-slate-50 active:scale-[0.98];
}

.menu-item.active {
    @apply bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 font-semibold;
    @apply ring-1 ring-blue-100;
}

.menu-item .icon-wrapper {
    @apply w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-200 flex-shrink-0;
}

.menu-item:active .icon-wrapper {
    @apply scale-90;
}
</style>
