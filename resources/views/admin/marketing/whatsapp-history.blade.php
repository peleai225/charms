@extends('layouts.admin')
@section('title', 'Historique WhatsApp')
@section('page-title', 'Historique Messages WhatsApp')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total messages', $stats['total'], 'from-green-500 to-emerald-500'],
            ['Envoyes', $stats['sent'], 'from-blue-500 to-cyan-500'],
            ['Delivres', $stats['delivered'], 'from-indigo-500 to-purple-500'],
            ['En attente', $stats['pending'], 'from-amber-500 to-orange-500'],
        ] as [$label, $val, $gradient])
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <p class="text-2xl font-black text-slate-900">{{ number_format($val) }}</p>
            <p class="text-xs text-slate-400 font-medium mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Messages --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Messages</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($messages as $msg)
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                            @if($msg->type === 'order_confirmation') bg-blue-50 text-blue-600
                            @elseif($msg->type === 'abandoned_cart') bg-amber-50 text-amber-600
                            @elseif($msg->type === 'promo') bg-purple-50 text-purple-600
                            @else bg-green-50 text-green-600
                            @endif">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="font-semibold text-sm text-slate-900">{{ $msg->customer?->full_name ?? $msg->phone }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">{{ str_replace('_', ' ', ucfirst($msg->type)) }}</span>
                            </div>
                            <p class="text-sm text-slate-600 line-clamp-2">{{ $msg->message }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[11px] text-slate-400">{{ $msg->created_at->locale('fr')->diffForHumans() }}</p>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                            @if($msg->status === 'delivered') bg-green-50 text-green-700
                            @elseif($msg->status === 'sent') bg-blue-50 text-blue-700
                            @elseif($msg->status === 'failed') bg-red-50 text-red-700
                            @else bg-slate-50 text-slate-500
                            @endif">{{ ucfirst($msg->status) }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <p class="text-sm">Aucun message WhatsApp enregistre</p>
            </div>
            @endforelse
        </div>
        @if($messages->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $messages->links() }}</div>
        @endif
    </div>
</div>
@endsection
