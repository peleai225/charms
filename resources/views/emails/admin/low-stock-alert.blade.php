<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Stock</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
        }
        .header {
            background: {{ $isOutOfStock ? 'linear-gradient(135deg, #dc2626, #b91c1c)' : 'linear-gradient(135deg, #f59e0b, #d97706)' }};
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #fff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background: {{ $isOutOfStock ? '#fee2e2' : '#fef3c7' }};
            border-left: 4px solid {{ $isOutOfStock ? '#dc2626' : '#f59e0b' }};
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .product-info {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .product-info h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #1f2937;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            color: #1f2937;
            font-weight: 500;
        }
        .stock-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        .stock-low {
            background: #fef3c7;
            color: #92400e;
        }
        .stock-out {
            background: #fee2e2;
            color: #991b1b;
        }
        .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $isOutOfStock ? '🚨 Rupture de Stock' : '⚠️ Alerte Stock Bas' }}</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <p style="margin: 0; font-size: 16px; font-weight: 600;">
                    {{ $isOutOfStock 
                        ? '⚠️ ATTENTION : Rupture de stock détectée !' 
                        : "⚠️ Le stock est en dessous du seuil d'alerte" }}
                </p>
            </div>

            <div class="product-info">
                <h3>Informations Produit</h3>
                
                <div class="info-row">
                    <span class="info-label">Produit :</span>
                    <span class="info-value">{{ $product->name }}</span>
                </div>

                @if($variant)
                <div class="info-row">
                    <span class="info-label">Variante :</span>
                    <span class="info-value">{{ $variant->name }}</span>
                </div>
                @endif

                <div class="info-row">
                    <span class="info-label">SKU :</span>
                    <span class="info-value">{{ $variant?->sku ?? $product->sku }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Stock actuel :</span>
                    <span class="info-value">
                        <span class="stock-badge {{ $isOutOfStock ? 'stock-out' : 'stock-low' }}">
                            {{ $currentStock }} unité(s)
                        </span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Seuil d'alerte :</span>
                    <span class="info-value">{{ $threshold }} unité(s)</span>
                </div>

                @if($product->stock_quantity !== null)
                <div class="info-row">
                    <span class="info-label">Stock total produit :</span>
                    <span class="info-value">{{ $product->stock_quantity }} unité(s)</span>
                </div>
                @endif
            </div>

            <p>
                <strong>Action recommandée :</strong><br>
                @if($isOutOfStock)
                    Veuillez réapprovisionner ce produit en urgence. Le produit est actuellement en rupture de stock.
                @else
                    Le stock de ce produit est en dessous du seuil d'alerte. Pensez à réapprovisionner prochainement.
                @endif
            </p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ route('admin.products.edit', $product) }}" class="button">
                    Gérer le produit
                </a>
            </p>

            <p style="color: #6b7280; font-size: 14px;">
                Cette alerte a été générée automatiquement par le système de gestion des stocks.
            </p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

