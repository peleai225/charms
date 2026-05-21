{{-- Skeleton Loader pour Table Row --}}
<tr class="animate-pulse border-b border-slate-100 hover:bg-slate-50/50">
    @for($i = 0; $i < ($columns ?? 5); $i++)
        <td class="px-4 py-4">
            <div class="h-4 bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200 rounded"
                 style="width: {{ ['60%', '80%', '70%', '50%', '65%'][$i % 5] }}; background-size: 200% 100%; animation: shimmer 2s infinite; animation-delay: {{ $i * 0.1 }}s;"></div>
        </td>
    @endfor
</tr>

<style>
@keyframes shimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
}
</style>
