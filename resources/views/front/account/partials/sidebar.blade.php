<aside class="lg:w-64 flex-shrink-0">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <!-- User info -->
        <div class="flex items-center gap-4 pb-6 border-b border-slate-100 mb-4">
            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                <span class="text-xl font-bold text-primary-600">{{ substr(auth()->user()->name, 0, 1) }}</span>
            </div>
            <div>
                <p class="font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="space-y-1">
            <a href="{{ route('account.dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('account.dashboard') ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de bord
            </a>
            
            <a href="{{ route('account.orders') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('account.orders*') ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Mes commandes
            </a>
            
            <a href="{{ route('account.addresses') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('account.addresses*') ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Mes adresses
            </a>
            
            <div class="pt-4 mt-4 border-t border-slate-100">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </nav>
    </div>
</aside>

