@if ($errors->any())
<div class="mb-5 flex gap-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
    <div>
        <p class="text-[13px] font-semibold text-orange-800">Erreurs de validation</p>
        <ul class="mt-1 space-y-0.5">
            @foreach ($errors->all() as $error)
            <li class="text-[12px] text-orange-700">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@if (session('success'))
<div class="mb-5 flex gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-[13px] text-green-800">{{ session('success') }}</p>
</div>
@endif

@if (session('error'))
<div class="mb-5 flex gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    <p class="text-[13px] text-red-800">{{ session('error') }}</p>
</div>
@endif
