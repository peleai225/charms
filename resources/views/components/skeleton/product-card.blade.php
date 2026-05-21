{{-- Skeleton Loader pour Product Card --}}
<div class="animate-pulse bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="aspect-square bg-gradient-to-br from-slate-200 via-slate-100 to-slate-200 relative overflow-hidden">
        <div class="absolute inset-0" style="background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent); animation: shimmer-wave 2s infinite;"></div>
    </div>
    <div class="p-4 space-y-3">
        <div class="h-5 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded-lg w-3/4" style="background-size: 200% 100%; animation: shimmer 2s infinite;"></div>
        <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded w-1/2" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.15s;"></div>
        <div class="flex items-center justify-between pt-2">
            <div class="h-6 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded-lg w-20" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.3s;"></div>
            <div class="h-9 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded-xl w-9" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.45s;"></div>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
}

@keyframes shimmer-wave {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
