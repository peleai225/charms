<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Facture</title>
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
            background: linear-gradient(135deg, #10b981, #059669);
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
        .invoice-info {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .invoice-info strong {
            font-size: 20px;
            color: #059669;
        }
        .message {
            background: #eff6ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .button {
            display: inline-block;
            background: #10b981;
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
        .note {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Votre Facture</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $order->billing_first_name }},</p>
            
            <p>Votre paiement a été confirmé avec succès !</p>
            
            <div class="invoice-info">
                <strong>Facture {{ $order->order_number }}</strong><br>
                <span style="color: #666;">Date : {{ $order->paid_at?->format('d/m/Y à H:i') ?? $order->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            
            <div class="message">
                <p><strong>✅ Paiement confirmé</strong></p>
                <p>Votre commande a été payée avec succès. Votre facture est jointe à cet email en format PDF.</p>
            </div>
            
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Récapitulatif de la commande :</h3>
                <p><strong>Montant total :</strong> {{ number_format($order->total, 0, ',', ' ') }} {{ $order->currency }}</p>
                <p><strong>Méthode de paiement :</strong> {{ $order->payment_method_label ?? ucfirst($order->payment_method) }}</p>
                <p><strong>Statut :</strong> {{ ucfirst($order->status) }}</p>
            </div>
            
            <div class="note">
                <p><strong>📎 Pièce jointe :</strong></p>
                <p>Votre facture au format PDF est jointe à cet email. Vous pouvez la télécharger et la conserver pour vos archives.</p>
            </div>
            
            <p>Vous pouvez également consulter votre commande et télécharger la facture depuis votre espace client.</p>
            
            <p>Merci de votre confiance !</p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

