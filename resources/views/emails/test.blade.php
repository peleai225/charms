<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
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
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info-box {
            background: #eff6ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
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
            <h1>✅ Email de Test</h1>
        </div>
        
        <div class="content">
            <div class="success-box">
                <p style="margin: 0; font-size: 16px; font-weight: 600; color: #065f46;">
                    🎉 Félicitations ! Votre configuration email fonctionne !
                </p>
            </div>

            <p>Bonjour,</p>
            
            <p>Cet email est un test de votre configuration SMTP.</p>
            
            <div class="info-box">
                <p style="margin: 0 0 10px 0; font-weight: 600;">Informations :</p>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Date d'envoi : {{ now()->format('d/m/Y à H:i') }}</li>
                    <li>Serveur : {{ config('mail.mailers.smtp.host', 'N/A') }}</li>
                    <li>Port : {{ config('mail.mailers.smtp.port', 'N/A') }}</li>
                    <li>Chiffrement : {{ config('mail.mailers.smtp.encryption', 'Aucun') ?? 'Aucun' }}</li>
                </ul>
            </div>

            <p>Si vous recevez cet email, cela signifie que votre configuration SMTP est correcte et que les emails seront envoyés automatiquement.</p>
            
            <p>Vous pouvez maintenant fermer cet email.</p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par le système de test.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

