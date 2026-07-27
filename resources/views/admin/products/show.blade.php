@extends('layouts.admin')

@section('title', $product->name)
@section('page-title', 'Fiche produit')

@section('content')

@if (session('success'))
<div class="mb-5 flex gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-[13px] text-green-800">{{ session('success') }}</p>
</div>
@endif

@php
    $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $otherImages  = $product->images->where('id', '!=', $primaryImage?->id ?? 0)->values();
    $totalStock   = $product->has_variants
        ? $product->variants->sum('stock_quantity')
        : $product->stock_quantity;
    $totalSales   = $product->sales_count ?? 0;
    $revenue      = $totalSales * ($product->sale_price ?? 0);
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-6 gap-4">
    <div class="flex items-center gap-4 min-w-0">
        <a href="{{ route('admin.products.index') }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-[15px] font-bold text-gray-900 truncate">{{ $product->name }}</h1>
            <p class="text-[12px] text-gray-400 font-mono">{{ $product->sku }}</p>
        </div>
        {{-- Badges statut --}}
        <div class="flex items-center gap-1.5 flex-shrink-0">
            @switch($product->status)
                @case('active')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-50 text-green-700 ring-1 ring-green-100">Actif</span>
                    @break
                @case('draft')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-100">Brouillon</span>
                    @break
                @case('archived')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500 ring-1 ring-gray-200">Archivé</span>
                    @break
            @endswitch
            @if($product->is_featured)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-orange-700 ring-1 ring-orange-100">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Mis en avant
                </span>
            @endif
            @if($product->is_new)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-100">Nouveauté</span>
            @endif
        </div>
    </div>
    <a href="{{ route('admin.products.edit', $product) }}"
        class="h-9 px-4 inline-flex items-center gap-2 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        Modifier
    </a>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-1">Ventes totales</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSales) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">unités vendues</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-1">Stock actuel</p>
        <p class="text-2xl font-bold {{ $totalStock <= 0 ? 'text-red-600' : ($totalStock <= ($product->stock_alert_threshold ?? 5) ? 'text-amber-600' : 'text-gray-900') }}">
            {{ number_format($totalStock) }}
        </p>
        <p class="text-[11px] text-gray-400 mt-0.5">unités en stock</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-1">CA généré</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($revenue, 0, ',', ' ') }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">F CFA estimés</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-1">Variantes</p>
        <p class="text-2xl font-bold text-gray-900">{{ $product->variants->count() }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">variante{{ $product->variants->count() > 1 ? 's' : '' }}</p>
    </div>
</div>

{{-- Layout 2 colonnes --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Colonne gauche : galerie + infos générales + description --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Galerie images --}}
        @if($product->images->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Images</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Image principale --}}
                @if($primaryImage)
                <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50 sm:row-span-2">
                    <img src="{{ asset('storage/' . $primaryImage->path) }}"
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover">
                    <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-orange-500 text-white text-[10px] font-semibold rounded">Principale</span>
                </div>
                @endif
                {{-- Miniatures --}}
                @foreach($otherImages->take(4) as $image)
                <div class="aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                    <img src="{{ asset('storage/' . $image->path) }}"
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover">
                </div>
                @endforeach
                @if($otherImages->count() > 4)
                <div class="aspect-square rounded-lg border border-gray-200 bg-gray-100 flex items-center justify-center">
                    <span class="text-[13px] font-semibold text-gray-500">+{{ $otherImages->count() - 4 }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Informations générales --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations générales</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">SKU</p>
                    <p class="text-[13px] font-mono text-gray-900">{{ $product->sku }}</p>
                </div>
                @if($product->barcode)
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Code-barres</p>
                    <p class="text-[13px] font-mono text-gray-900">{{ $product->barcode }}</p>
                </div>
                @endif
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Catégorie</p>
                    <p class="text-[13px] text-gray-900">{{ $product->category?->name ?? '—' }}</p>
                </div>
                @if($product->weight)
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Poids</p>
                    <p class="text-[13px] text-gray-900">{{ $product->weight }} kg</p>
                </div>
                @endif
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Créé le</p>
                    <p class="text-[13px] text-gray-900">{{ $product->created_at->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Mis à jour</p>
                    <p class="text-[13px] text-gray-900">{{ $product->updated_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($product->short_description || $product->description)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Description</h2>
            @if($product->short_description)
            <div class="mb-3">
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-1">Description courte</p>
                <p class="text-[13px] text-gray-700 leading-relaxed">{{ $product->short_description }}</p>
            </div>
            @endif
            @if($product->description)
            <div>
                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-1">Description complète</p>
                <div class="text-[13px] text-gray-700 leading-relaxed prose prose-sm max-w-none">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Variantes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Variantes</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                        {{ $product->variants->count() > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $product->variants->count() }} variante{{ $product->variants->count() > 1 ? 's' : '' }}
                    </span>
                </div>
                @if($product->variants->count() > 0)
                <span class="text-[12px] text-gray-400">{{ $product->variants->sum('stock_quantity') }} pcs au total</span>
                @endif
            </div>

            @if($product->variants->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Variante</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-28">Stock</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Prix</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-24">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($product->variants as $variant)
                        @php
                            $vColor  = $variant->attributeValues->firstWhere(fn($v) => $v->attribute && $v->attribute->slug === 'couleur');
                            $vOthers = $variant->attributeValues->filter(fn($v) => $v->attribute && $v->attribute->slug !== 'couleur')->values();
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    @if($variant->image)
                                        <img src="{{ asset('storage/' . $variant->image) }}" class="w-8 h-8 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                    @elseif($vColor && $vColor->color_code)
                                        <span class="w-8 h-8 rounded-lg border border-gray-200 flex-shrink-0 inline-block" style="background:{{ $vColor->color_code }}"></span>
                                    @else
                                        <span class="w-8 h-8 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 inline-block"></span>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-1 min-w-0">
                                        @if($vColor)
                                            <span class="font-medium text-gray-900">{{ $vColor->value }}</span>
                                        @endif
                                        @foreach($vOthers as $av)
                                            <span class="text-[11px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $av->value }}</span>
                                        @endforeach
                                        @if(!$vColor && $vOthers->isEmpty())
                                            <span class="text-gray-600">{{ $variant->name ?: 'Variante' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-[12px] text-gray-400">{{ $variant->sku }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    {{ $variant->stock_quantity <= 0 ? 'bg-red-100 text-red-700' : ($variant->stock_quantity <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $variant->stock_quantity <= 0 ? 'Rupture' : $variant->stock_quantity . ' pcs' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-[13px] text-gray-700 tabular-nums">
                                {{ $variant->sale_price !== null ? number_format($variant->sale_price, 0, ',', ' ') . ' F' : '— (produit)' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    {{ $variant->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="py-10 text-center">
                <svg class="w-8 h-8 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                <p class="text-[13px] text-gray-400">Aucune variante pour ce produit.</p>
                <a href="{{ route('admin.products.edit', $product) }}" class="mt-2 inline-block text-[12px] text-orange-600 hover:text-orange-700 font-medium underline underline-offset-2">
                    Ajouter depuis l'édition
                </a>
            </div>
            @endif
        </div>

        {{-- Mouvements de stock --}}
        @if($product->stockMovements->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Mouvements de stock récents</h2>
                <p class="text-[12px] text-gray-400 mt-0.5">10 derniers mouvements</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-24">Qté</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-24">Avant</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-24">Après</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($product->stockMovements as $movement)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-2.5 text-[12px] text-gray-500 whitespace-nowrap">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $isEntry = in_array($movement->type, ['purchase', 'return_in', 'adjustment_in', 'transfer_in', 'inventory']);
                                    $typeLabels = [
                                        'purchase' => 'Achat',
                                        'sale' => 'Vente',
                                        'return_in' => 'Retour client',
                                        'return_out' => 'Retour fourn.',
                                        'adjustment_in' => 'Ajust. +',
                                        'adjustment_out' => 'Ajust. -',
                                        'transfer_in' => 'Transfert +',
                                        'transfer_out' => 'Transfert -',
                                        'loss' => 'Perte',
                                        'inventory' => 'Inventaire',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                    {{ $isEntry ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $typeLabels[$movement->type] ?? $movement->type }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-center font-semibold tabular-nums
                                {{ $isEntry ? 'text-green-700' : 'text-red-700' }}">
                                {{ $isEntry ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-4 py-2.5 text-center text-[12px] text-gray-500 tabular-nums">{{ $movement->stock_before }}</td>
                            <td class="px-4 py-2.5 text-center text-[12px] font-medium text-gray-800 tabular-nums">{{ $movement->stock_after }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- Sidebar droite : prix, stock, badges --}}
    <div class="space-y-5">

        {{-- Prix --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Tarification</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-[12px] text-gray-500">Prix de vente TTC</span>
                    <span class="text-[15px] font-bold text-gray-900">{{ number_format($product->sale_price, 0, ',', ' ') }} F</span>
                </div>
                @if($product->compare_price)
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-[12px] text-gray-500">Prix barré</span>
                    <span class="text-[13px] text-gray-400 line-through">{{ number_format($product->compare_price, 0, ',', ' ') }} F</span>
                </div>
                @endif
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-[12px] text-gray-500">Prix d'achat HT</span>
                    <span class="text-[13px] text-gray-700">{{ number_format($product->purchase_price, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-[12px] text-gray-500">TVA</span>
                    <span class="text-[13px] text-gray-700">{{ $product->tax_rate }}%</span>
                </div>
            </div>
        </div>

        {{-- Stock --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock</h2>
            <div class="space-y-2.5">
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <span class="text-[12px] text-gray-500">Quantité totale</span>
                    <span class="text-[13px] font-semibold {{ $totalStock <= 0 ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $totalStock }} pcs
                    </span>
                </div>
                @if(!$product->has_variants)
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <span class="text-[12px] text-gray-500">Seuil d'alerte</span>
                    <span class="text-[13px] text-gray-700">{{ $product->stock_alert_threshold }} pcs</span>
                </div>
                @endif
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <span class="text-[12px] text-gray-500">Suivi de stock</span>
                    <span class="text-[13px] {{ $product->track_stock ? 'text-green-700' : 'text-gray-400' }}">
                        {{ $product->track_stock ? 'Activé' : 'Désactivé' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-1.5">
                    <span class="text-[12px] text-gray-500">Commande en rupture</span>
                    <span class="text-[13px] {{ $product->allow_backorder ? 'text-green-700' : 'text-gray-400' }}">
                        {{ $product->allow_backorder ? 'Autorisé' : 'Non autorisé' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Caractéristiques --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Caractéristiques</h2>
            <div class="space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                        @if($product->has_variants)
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </span>
                    <span class="text-[13px] text-gray-700">Produit avec variantes</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                        @if($product->is_featured)
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </span>
                    <span class="text-[13px] text-gray-700">Mis en avant</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                        @if($product->is_new)
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </span>
                    <span class="text-[13px] text-gray-700">Nouveauté</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                        @if($product->is_dropshipping)
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </span>
                    <span class="text-[13px] text-gray-700">Dropshipping</span>
                </div>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Actions</h2>
            <div class="space-y-2">
                <a href="{{ route('admin.products.edit', $product) }}"
                    class="w-full h-9 inline-flex items-center justify-center gap-2 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Modifier le produit
                </a>
                <a href="{{ route('admin.products.index') }}"
                    class="w-full h-9 inline-flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Retour à la liste
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
