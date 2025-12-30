<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étiquettes produits</title>
    <style>
        @page { margin: 5mm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .labels-container { display: flex; flex-wrap: wrap; gap: 2mm; }
        .label {
            width: 50mm;
            height: 30mm;
            border: 1px solid #ccc;
            padding: 2mm;
            box-sizing: border-box;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .label-small { width: 38mm; height: 21mm; }
        .label-price { width: 60mm; height: 40mm; }
        .product-name { font-size: 8pt; font-weight: bold; line-height: 1.2; overflow: hidden; max-height: 2.4em; }
        .barcode-img { text-align: center; }
        .barcode-img img { max-width: 100%; height: 15mm; }
        .barcode-text { font-family: monospace; font-size: 7pt; text-align: center; }
        .price { font-size: 10pt; font-weight: bold; text-align: right; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 20px; background: #f0f0f0; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">🖨️ Imprimer</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">Fermer</button>
        <span style="margin-left: 20px;">{{ $products->count() }} produit(s) × {{ $quantity }} = {{ $products->count() * $quantity }} étiquette(s)</span>
    </div>

    <div class="labels-container">
        @foreach($products as $product)
            @for($i = 0; $i < $quantity; $i++)
            <div class="label {{ $labelFormat === 'small' ? 'label-small' : ($labelFormat === 'price_tag' ? 'label-price' : '') }}">
                <div class="product-name">{{ Str::limit($product->name, 40) }}</div>
                @if($product->barcode_svg)
                <div class="barcode-img">
                    <img src="{{ $product->barcode_svg }}" alt="{{ $product->barcode }}">
                </div>
                <div class="barcode-text">{{ $product->barcode }}</div>
                @endif
                <div class="price">{{ number_format($product->sale_price, 0, ',', ' ') }} F CFA</div>
            </div>
            @endfor
        @endforeach
    </div>
</body>
</html>

