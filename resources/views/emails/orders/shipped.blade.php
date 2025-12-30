<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande expédiée</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .tracking-box { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 2px solid #0ea5e9; padding: 20px; margin: 20px 0; border-radius: 12px; text-align: center; }
        .tracking-number { font-size: 24px; font-weight: bold; color: #0369a1; font-family: monospace; letter-spacing: 2px; }
        .carrier { color: #666; margin-top: 10px; }
        .timeline { margin: 30px 0; }
        .timeline-item { display: flex; align-items: flex-start; margin-bottom: 15px; }
        .timeline-dot { width: 12px; height: 12px; border-radius: 50%; margin-right: 15px; margin-top: 5px; }
        .timeline-dot.done { background: #10b981; }
        .timeline-dot.current { background: #8b5cf6; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2); }
        .timeline-dot.pending { background: #d1d5db; }
        .address { background: #f9fafb; padding: 15px; margin: 20px 0; border-radius: 8px; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Votre colis est en route !</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $order->shipping_first_name }},</p>
            
            <p>Bonne nouvelle ! Votre commande <strong>{{ $order->order_number }}</strong> a été expédiée et est en chemin vers vous.</p>
            
            @if($order->tracking_number)
            <div class="tracking-box">
                <p style="margin: 0 0 10px 0; color: #666;">Numéro de suivi</p>
                <div class="tracking-number">{{ $order->tracking_number }}</div>
                @if($order->shipping_carrier)
                <p class="carrier">via {{ ucfirst($order->shipping_carrier) }}</p>
                @endif
            </div>
            @endif
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot done"></div>
                    <div>
                        <strong>Commande confirmée</strong><br>
                        <small style="color: #666;">{{ $order->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot done"></div>
                    <div>
                        <strong>En préparation</strong><br>
                        <small style="color: #666;">Terminé</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot current"></div>
                    <div>
                        <strong>Expédiée</strong><br>
                        <small style="color: #666;">{{ $order->shipped_at?->format('d/m/Y') ?? 'Aujourd\'hui' }}</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot pending"></div>
                    <div>
                        <strong>Livraison</strong><br>
                        <small style="color: #666;">Bientôt chez vous !</small>
                    </div>
                </div>
            </div>
            
            <div class="address">
                <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Adresse de livraison</h3>
                {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                {{ $order->shipping_address }}<br>
                @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                {{ $order->shipping_postal_code }} {{ $order->shipping_city }}
            </div>
            
            <p>Vous pouvez suivre votre colis en utilisant le numéro de suivi ci-dessus sur le site du transporteur.</p>
            
            <p>Merci pour votre confiance !</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

