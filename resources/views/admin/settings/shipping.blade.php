@extends('layouts.admin')

@section('title', 'Paramètres livraison')
@section('page-title', 'Paramètres de livraison')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-2 flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Général</a>
        <a href="{{ route('admin.settings.shipping') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl">Livraison</a>
        <a href="{{ route('admin.settings.payment') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Paiement</a>
        <a href="{{ route('admin.settings.emails') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Emails</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.shipping.update') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Options de livraison</h3>
            <div class="space-y-4">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="shipping_enabled" value="1" {{ ($settings['shipping_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-slate-700">Activer la livraison</span>
                </label>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Livraison gratuite à partir de</label>
                        <div class="relative">
                            <input type="number" name="free_shipping_threshold" value="{{ $settings['free_shipping_threshold'] ?? '' }}" step="100" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: 50000">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">F CFA</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tarif forfaitaire</label>
                        <div class="relative">
                            <input type="number" name="flat_rate_shipping" value="{{ $settings['flat_rate_shipping'] ?? '' }}" step="100" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: 2000">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">F CFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Zones de livraison</h3>
                <button type="button" onclick="addZone()" class="px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Ajouter</button>
            </div>
            
            <div id="zones-container" class="space-y-4">
                @php $zones = json_decode($settings['shipping_zones'] ?? '[]', true) ?: []; @endphp
                @forelse($zones as $index => $zone)
                <div class="zone-item p-4 border border-slate-200 rounded-xl">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nom de la zone</label>
                            <input type="text" name="shipping_zones[{{ $index }}][name]" value="{{ $zone['name'] }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: Abidjan">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Villes (séparées par virgule)</label>
                            <input type="text" name="shipping_zones[{{ $index }}][cities]" value="{{ $zone['cities'] }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: Cocody, Plateau, Marcory">
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Prix (F CFA)</label>
                                <input type="number" name="shipping_zones[{{ $index }}][price]" value="{{ $zone['price'] }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                            </div>
                            <button type="button" onclick="this.closest('.zone-item').remove()" class="mt-6 p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-slate-500 text-sm">Aucune zone configurée. Utilisez le tarif forfaitaire ou ajoutez des zones.</p>
                @endforelse
            </div>
        </div>

        <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Enregistrer
        </button>
    </form>
</div>

<script>
let zoneIndex = {{ count($zones) }};
function addZone() {
    const container = document.getElementById('zones-container');
    const html = `
        <div class="zone-item p-4 border border-slate-200 rounded-xl">
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom de la zone</label>
                    <input type="text" name="shipping_zones[${zoneIndex}][name]" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: Abidjan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Villes (séparées par virgule)</label>
                    <input type="text" name="shipping_zones[${zoneIndex}][cities]" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Ex: Cocody, Plateau">
                </div>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prix (F CFA)</label>
                        <input type="number" name="shipping_zones[${zoneIndex}][price]" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    </div>
                    <button type="button" onclick="this.closest('.zone-item').remove()" class="mt-6 p-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    zoneIndex++;
}
</script>
@endsection

