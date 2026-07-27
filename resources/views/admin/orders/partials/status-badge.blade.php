@php
    $statusConfig = [
        'pending'    => ['bg-amber-50 text-amber-600',  'bg-amber-500',  'ring-amber-100'],
        'confirmed'  => ['bg-blue-50 text-blue-600',    'bg-blue-500',   'ring-blue-100'],
        'processing' => ['bg-indigo-50 text-indigo-600','bg-indigo-500', 'ring-indigo-100'],
        'shipped'    => ['bg-purple-50 text-purple-600','bg-purple-500', 'ring-purple-100'],
        'delivered'  => ['bg-green-50 text-green-600',  'bg-green-500',  'ring-green-100'],
        'cancelled'  => ['bg-red-50 text-red-600',      'bg-red-500',    'ring-red-100'],
        'refunded'   => ['bg-red-50 text-red-600',      'bg-red-500',    'ring-red-100'],
    ];
    $cfg    = $statusConfig[$status] ?? ['bg-slate-50 text-slate-600', 'bg-slate-400', 'ring-slate-200'];
    $ring   = $ring ?? false;
    $label  = $label ?? $order->status_label ?? $status;
@endphp
<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg {{ $cfg[0] }} {{ $ring ? 'ring-1 '.$cfg[2] : '' }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $cfg[1] }}"></span>{{ $label }}
</span>
