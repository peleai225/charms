<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de commande</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .status-box { background: #f0f9ff; border-left: 4px solid #2563eb; padding: 20px; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mise à jour de votre commande</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $order->billing_first_name }},</p>
            
            <p>Le statut de votre commande <strong>{{ $order->order_number }}</strong> a été mis à jour.</p>
            
            <div class="status-box">
                <p style="margin: 0;">
                    <strong>Nouveau statut :</strong> {{ $order->status_label }}
                </p>
            </div>
            
            @if($order->tracking_number && $order->status === 'shipped')
            <p>
                <strong>Numéro de suivi :</strong> {{ $order->tracking_number }}
                @if($order->shipping_carrier)
                    ({{ ucfirst($order->shipping_carrier) }})
                @endif
            </p>
            @endif
            
            <p>Merci pour votre confiance !</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

