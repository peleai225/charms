{{-- Skeleton Loader pour Order Card --}}
<div class="animate-pulse bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div class="flex-1">
            <div class="h-6 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded-lg w-40 mb-2" style="background-size: 200% 100%; animation: shimmer 2s infinite;"></div>
            <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded w-32" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.1s;"></div>
        </div>
        <div class="h-7 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded-full w-24" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.2s;"></div>
    </div>

    <div class="space-y-3">
        <div class="flex justify-between items-center">
            <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded w-24" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.3s;"></div>
            <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded w-32" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.4s;"></div>
        </div>
        <div class="flex justify-between items-center">
            <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded w-28" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.5s;"></div>
            <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded w-28" style="background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: 0.6s;"></div>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
}
</style>
