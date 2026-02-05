<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .order-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-info h2 {
            margin-top: 0;
            color: #667eea;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .items-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .total {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .total-row.final {
            font-size: 1.2em;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #667eea;
            padding-top: 10px;
            margin-top: 10px;
        }
        .shipping-info {
            background: #e0e7ff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 0.9em;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛒 Nouvelle Commande</h1>
        <p>Commande #{{ $order->order_number }}</p>
    </div>

    <div class="content">
        <div class="order-info">
            <h2>Bonjour {{ $orderSupplier->supplier->name }},</h2>
            <p>Une nouvelle commande vous a été attribuée et nécessite votre attention.</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Référence</th>
                    <th>Quantité</th>
                    <th>Prix d'achat</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $itemData)
                    @php
                        $item = $itemData['order_item'];
                        $product = $itemData['product'];
                        $purchasePrice = $itemData['purchase_price'];
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->variant_name)
                                <br><small>{{ $item->variant_name }}</small>
                            @endif
                        </td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($purchasePrice, 0, ',', ' ') }} {{ $order->currency }}</td>
                        <td><strong>{{ number_format($purchasePrice * $item->quantity, 0, ',', ' ') }} {{ $order->currency }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            <div class="total-row">
                <span>Sous-total :</span>
                <span>{{ number_format($orderSupplier->subtotal, 0, ',', ' ') }} {{ $order->currency }}</span>
            </div>
            @if($orderSupplier->shipping_cost > 0)
            <div class="total-row">
                <span>Frais de livraison :</span>
                <span>{{ number_format($orderSupplier->shipping_cost, 0, ',', ' ') }} {{ $order->currency }}</span>
            </div>
            @endif
            <div class="total-row final">
                <span>Total :</span>
                <span>{{ number_format($orderSupplier->total, 0, ',', ' ') }} {{ $order->currency }}</span>
            </div>
        </div>

        <div class="shipping-info">
            <h3>📍 Adresse de livraison</h3>
            <p>
                <strong>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</strong><br>
                @if($order->shipping_company)
                    {{ $order->shipping_company }}<br>
                @endif
                {{ $order->shipping_address }}<br>
                @if($order->shipping_address_2)
                    {{ $order->shipping_address_2 }}<br>
                @endif
                {{ $order->shipping_postal_code }} {{ $order->shipping_city }}<br>
                {{ $order->shipping_country }}<br>
                <br>
                📞 {{ $order->shipping_phone }}<br>
                ✉️ {{ $order->shipping_email }}
            </p>
        </div>

        @if($order->customer_notes)
        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <strong>📝 Notes du client :</strong>
            <p>{{ $order->customer_notes }}</p>
        </div>
        @endif

        <div style="text-align: center; margin-top: 30px;">
            <p><strong>Action requise :</strong></p>
            <p>Veuillez confirmer la réception de cette commande et préparer l'expédition.</p>
            <p>Une fois expédiée, merci de nous communiquer le numéro de suivi.</p>
        </div>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement par le système de gestion des commandes.</p>
        <p>Pour toute question, contactez-nous à : {{ config('mail.from.address', 'contact@example.com') }}</p>
    </div>
</body>
</html>

