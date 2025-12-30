@extends('layouts.admin')

@section('title', 'Alertes stock')
@section('page-title', 'Alertes de stock')

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.stock.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour
    </a>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Ruptures -->
        <div class="bg-white rounded-2xl shadow-sm border border-red-200">
            <div class="p-6 border-b border-red-100 flex items-center gap-3 bg-red-50 rounded-t-2xl">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-red-900">Ruptures de stock ({{ $outOfStock->count() }})</h3>
            </div>
            @if($outOfStock->count() > 0)
            <div class="divide-y divide-red-100">
                @foreach($outOfStock as $product)
                <div class="p-4 flex items-center justify-between hover:bg-red-50">
                    <div>
                        <p class="font-medium text-slate-900">{{ $product->name }}</p>
                        <p class="text-sm text-slate-500">{{ $product->category?->name ?? 'Sans catégorie' }}</p>
                    </div>
                    <a href="{{ route('admin.stock.create-movement') }}?product={{ $product->id }}" class="px-3 py-1 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        + Stock
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-green-600">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Aucune rupture de stock
            </div>
            @endif
        </div>

        <!-- Stock faible -->
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200">
            <div class="p-6 border-b border-amber-100 flex items-center gap-3 bg-amber-50 rounded-t-2xl">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-amber-900">Stock faible ({{ $lowStock->count() }})</h3>
            </div>
            @if($lowStock->count() > 0)
            <div class="divide-y divide-amber-100">
                @foreach($lowStock as $product)
                <div class="p-4 flex items-center justify-between hover:bg-amber-50">
                    <div>
                        <p class="font-medium text-slate-900">{{ $product->name }}</p>
                        <p class="text-sm text-slate-500">
                            Stock: <span class="font-semibold text-amber-600">{{ $product->stock_quantity }}</span>
                            / Seuil: {{ $product->stock_alert_threshold }}
                        </p>
                    </div>
                    <a href="{{ route('admin.stock.create-movement') }}?product={{ $product->id }}" class="px-3 py-1 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        + Stock
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-green-600">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tous les stocks sont suffisants
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

