@extends('layouts.admin')

@section('title', 'Codes-barres & QR Codes')
@section('page-title', 'Codes-barres & QR Codes')

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex flex-wrap gap-3">
        <button onclick="openScanner()" class="px-4 py-2 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            Scanner
        </button>
        <form method="POST" action="{{ route('admin.barcodes.bulk-generate') }}" id="bulkForm">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Générer codes manquants
            </button>
        </form>
        <button onclick="printSelected()" class="px-4 py-2 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimer étiquettes
        </button>
    </div>

    <!-- Recherche -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex gap-4">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, SKU ou code-barres..." class="flex-1 px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Rechercher</button>
        </form>
    </div>

    <!-- Liste produits -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-4 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-slate-300" onchange="toggleAll(this)">
                        </th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Code-barres</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Prix</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4">
                            <input type="checkbox" name="products[]" value="{{ $product->id }}" form="bulkForm" class="product-checkbox rounded border-slate-300">
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($product->primary_image_url)
                                    <img src="{{ $product->primary_image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <span class="font-medium text-slate-900">{{ Str::limit($product->name, 30) }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-slate-600 font-mono text-sm">{{ $product->sku ?? '-' }}</td>
                        <td class="px-4 py-4">
                            @if($product->barcode)
                                <span class="font-mono text-sm text-slate-900">{{ $product->barcode }}</span>
                            @else
                                <span class="text-slate-400 text-sm">Non généré</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-semibold text-slate-900">{{ format_price($product->sale_price) }}</td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="showBarcode({{ $product->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Voir code-barres">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </button>
                                <button onclick="showQrCode({{ $product->id }}, '{{ $product->name }}')" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg" title="Voir QR Code">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Aucun produit</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">{{ $products->links() }}</div>
        @endif
    </div>
</div>

<!-- Modal Scanner -->
<div id="scannerModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl w-full max-w-lg mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Scanner un code</h3>
            <button onclick="closeScanner()" class="p-2 hover:bg-slate-100 rounded-lg">&times;</button>
        </div>
        <div class="space-y-4">
            <input type="text" id="scanInput" placeholder="Scannez ou saisissez le code..." class="w-full px-4 py-3 border border-slate-300 rounded-xl text-lg font-mono" autofocus>
            <div id="scanResult" class="hidden p-4 bg-slate-50 rounded-xl"></div>
        </div>
    </div>
</div>

<!-- Modal Code-barres -->
<div id="barcodeModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 p-6 text-center">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Code-barres</h3>
            <button onclick="closeBarcodeModal()" class="p-2 hover:bg-slate-100 rounded-lg">&times;</button>
        </div>
        <div id="barcodeContent" class="py-8"></div>
        <button onclick="printBarcode()" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Imprimer</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checkbox.checked);
}

function openScanner() {
    document.getElementById('scannerModal').classList.remove('hidden');
    document.getElementById('scannerModal').classList.add('flex');
    document.getElementById('scanInput').focus();
}

function closeScanner() {
    document.getElementById('scannerModal').classList.add('hidden');
    document.getElementById('scannerModal').classList.remove('flex');
}

document.getElementById('scanInput')?.addEventListener('keypress', async function(e) {
    if (e.key === 'Enter') {
        const code = this.value.trim();
        if (!code) return;
        
        try {
            const response = await fetch('{{ route("admin.barcodes.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code })
            });
            
            const data = await response.json();
            const resultDiv = document.getElementById('scanResult');
            resultDiv.classList.remove('hidden');
            
            if (data.found) {
                resultDiv.innerHTML = `
                    <div class="text-green-600 font-semibold mb-2">✓ Produit trouvé</div>
                    <p class="font-medium">${data.data.name}</p>
                    <p class="text-sm text-slate-600">SKU: ${data.data.sku || 'N/A'}</p>
                    <p class="text-sm text-slate-600">Stock: ${data.data.stock}</p>
                    <p class="font-semibold mt-2">${new Intl.NumberFormat('fr-FR').format(data.data.price)} F CFA</p>
                `;
            } else {
                resultDiv.innerHTML = `<div class="text-red-600">Produit non trouvé</div>`;
            }
        } catch (error) {
            console.error(error);
        }
        
        this.value = '';
    }
});

async function showBarcode(productId) {
    try {
        const response = await fetch(`/admin/barcodes/${productId}/generate`);
        const data = await response.json();
        
        document.getElementById('barcodeContent').innerHTML = `
            <img src="${data.barcode_svg}" alt="Code-barres" class="mx-auto mb-4">
            <p class="font-mono text-lg">${data.barcode}</p>
        `;
        document.getElementById('barcodeModal').classList.remove('hidden');
        document.getElementById('barcodeModal').classList.add('flex');
    } catch (error) {
        console.error(error);
    }
}

function closeBarcodeModal() {
    document.getElementById('barcodeModal').classList.add('hidden');
    document.getElementById('barcodeModal').classList.remove('flex');
}

async function showQrCode(productId, productName) {
    document.getElementById('barcodeContent').innerHTML = `
        <div class="flex justify-center py-4">
            <svg class="animate-spin h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    document.getElementById('barcodeModal').classList.remove('hidden');
    document.getElementById('barcodeModal').classList.add('flex');
    
    try {
        const response = await fetch(`/admin/barcodes/${productId}/qrcode`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('barcodeContent').innerHTML = `
                <div class="flex justify-center mb-4">
                    <img src="${data.qr_code}" alt="QR Code" class="w-48 h-48">
                </div>
                <p class="font-medium text-slate-900">${data.product.name}</p>
                <p class="text-sm text-slate-600 mt-1">SKU: ${data.product.sku || 'N/A'}</p>
                <p class="text-sm text-slate-600 mt-1">${new Intl.NumberFormat('fr-FR').format(data.product.price)} F CFA</p>
                <p class="text-xs text-slate-400 mt-2 break-all">${data.qr_url}</p>
                <a href="${data.qr_url}" target="_blank" class="inline-block mt-3 text-sm text-purple-600 hover:text-purple-700">Tester le lien →</a>
            `;
        } else {
            document.getElementById('barcodeContent').innerHTML = `<p class="text-red-600">Erreur lors de la génération du QR code</p>`;
        }
    } catch (error) {
        console.error(error);
        document.getElementById('barcodeContent').innerHTML = `<p class="text-red-600">Erreur de connexion</p>`;
    }
}

function printSelected() {
    const selected = [...document.querySelectorAll('.product-checkbox:checked')].map(cb => cb.value);
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins un produit');
        return;
    }
    window.open('{{ route("admin.barcodes.print-labels") }}?products=' + selected.join(','), '_blank');
}

function printBarcode() {
    window.print();
}
</script>
@endsection

