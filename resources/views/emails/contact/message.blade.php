<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
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
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .label {
            font-weight: bold;
            color: #667eea;
            display: inline-block;
            min-width: 100px;
        }
        .message-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e5e7eb;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nouveau message de contact</h1>
    </div>
    
    <div class="content">
        <p>Vous avez reçu un nouveau message depuis le formulaire de contact du site.</p>
        
        <div class="info-box">
            <p><span class="label">Nom :</span> {{ $name }}</p>
            <p><span class="label">Email :</span> <a href="mailto:{{ $email }}">{{ $email }}</a></p>
            <p><span class="label">Sujet :</span> 
                @php
                    $subjectLabels = [
                        'order' => 'Question sur une commande',
                        'product' => 'Question sur un produit',
                        'return' => 'Retour / Remboursement',
                        'partnership' => 'Partenariat',
                        'other' => 'Autre',
                    ];
                @endphp
                {{ $subjectLabels[$messageSubject] ?? 'Autre' }}
            </p>
        </div>
        
        <div class="message-box">
            <h3 style="margin-top: 0; color: #667eea;">Message :</h3>
            <p style="white-space: pre-wrap;">{{ $message }}</p>
        </div>
        
        <div class="footer">
            <p>Vous pouvez répondre directement à cet email pour contacter {{ $name }}.</p>
            <p>Ce message a été envoyé depuis le formulaire de contact du site.</p>
        </div>
    </div>
</body>
</html>

