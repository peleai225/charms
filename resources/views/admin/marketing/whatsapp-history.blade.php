@extends('layouts.admin')
@section('title', 'Historique WhatsApp')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Historique WhatsApp</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Tous les messages envoyés via WhatsApp</p>
        </div>
        <a href="{{ route('admin.marketing.campaigns') }}" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
            ← Campagnes
        </a>
    </div>

    {{-- KPI Strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
            @foreach([
                ['Total messages', $stats['total']],
                ['Envoyés', $stats['sent']],
                ['Délivrés', $stats['delivered']],
                ['En attente', $stats['pending']],
            ] as [$label, $val])
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($val) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Messages --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[13px] font-semibold text-gray-900">Messages</h3>
        </div>

        @if($messages->isEmpty())
        <div class="py-16 text-center">
            <p class="text-[13px] text-gray-400">Aucun message WhatsApp enregistré</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($messages as $msg)
            <div class="px-5 py-4 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[13px] font-medium text-gray-900">{{ $msg->customer?->full_name ?? $msg->phone }}</span>
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ str_replace('_', ' ', ucfirst($msg->type)) }}</span>
                            </div>
                            <p class="text-[13px] text-gray-500 line-clamp-2">{{ $msg->message }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[11px] text-gray-400">{{ $msg->created_at->locale('fr')->diffForHumans() }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                            @if($msg->status === 'delivered') bg-green-50 text-green-700
                            @elseif($msg->status === 'sent') bg-blue-50 text-blue-700
                            @elseif($msg->status === 'failed') bg-red-50 text-red-700
                            @else bg-gray-100 text-gray-500
                            @endif">{{ ucfirst($msg->status) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($messages->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $messages->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
