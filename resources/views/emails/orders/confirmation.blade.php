<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .order-number { background: #f0f9ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
        .order-number strong { font-size: 18px; color: #2563eb; }
        .items { margin: 20px 0; }
        .item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .item:last-child { border-bottom: none; }
        .totals { background: #f9fafb; padding: 15px; margin: 20px 0; border-radius: 8px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .total-row.final { font-size: 18px; font-weight: bold; border-top: 2px solid #e5e7eb; padding-top: 10px; margin-top: 10px; }
        .address { background: #f9fafb; padding: 15px; margin: 20px 0; border-radius: 8px; }
        .address h3 { margin: 0 0 10px 0; font-size: 14px; color: #666; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Commande confirmée !</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $order->billing_first_name }},</p>
            
            <p>Merci pour votre commande ! Nous l'avons bien reçue et nous la traitons dans les plus brefs délais.</p>
            
            <div class="order-number">
                <strong>Commande {{ $order->order_number }}</strong><br>
                <span style="color: #666;">{{ $order->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            
            <h2 style="font-size: 16px; margin-bottom: 10px;">Récapitulatif</h2>
            
            <div class="items">
                @foreach($order->items as $item)
                <div class="item">
                    <span>{{ $item->product_name }} @if($item->variant_name)({{ $item->variant_name }})@endif × {{ $item->quantity }}</span>
                    <span>{{ number_format($item->total, 0, ',', ' ') }} F</span>
                </div>
                @endforeach
            </div>
            
            <div class="totals">
                <div class="total-row">
                    <span>Sous-total</span>
                    <span>{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="total-row" style="color: #10b981;">
                    <span>Réduction</span>
                    <span>-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                </div>
                @endif
                <div class="total-row">
                    <span>Livraison</span>
                    <span>{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite' }}</span>
                </div>
                <div class="total-row final">
                    <span>Total</span>
                    <span>{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                </div>
            </div>
            
            <div class="address">
                <h3>Adresse de livraison</h3>
                {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                {{ $order->shipping_address }}<br>
                @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                {{ $order->shipping_postal_code }} {{ $order->shipping_city }}<br>
                {{ $order->shipping_country }}
            </div>
            
            @if($order->payment_method === 'cod')
            <p style="background: #fef3c7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                💰 <strong>Paiement à la livraison</strong><br>
                Vous réglerez {{ number_format($order->total, 0, ',', ' ') }} F CFA à la réception de votre colis.
            </p>
            @endif
            
            <p>Vous recevrez un email dès que votre commande sera expédiée avec le numéro de suivi.</p>
            
            <p>Merci de votre confiance !</p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

