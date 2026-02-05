<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px;">
                            <!-- Logo / En-tête -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <h1 style="margin: 0; color: #1e293b; font-size: 24px; font-weight: 700;">
                                    {{ \App\Models\Setting::get('site_name', config('app.name')) }}
                                </h1>
                            </div>

                            <!-- Titre -->
                            <h2 style="margin: 0 0 20px 0; color: #1e293b; font-size: 20px; font-weight: 600;">
                                Réinitialisation de votre mot de passe
                            </h2>

                            <!-- Message -->
                            <p style="margin: 0 0 20px 0; color: #64748b; font-size: 16px; line-height: 1.6;">
                                Bonjour,
                            </p>
                            <p style="margin: 0 0 30px 0; color: #64748b; font-size: 16px; line-height: 1.6;">
                                Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :
                            </p>

                            <!-- Bouton -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $resetUrl }}" 
                                   style="display: inline-block; padding: 14px 32px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
                                    Réinitialiser mon mot de passe
                                </a>
                            </div>

                            <!-- Lien alternatif -->
                            <p style="margin: 30px 0 20px 0; color: #64748b; font-size: 14px; line-height: 1.6;">
                                Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
                            </p>
                            <p style="margin: 0 0 30px 0; word-break: break-all;">
                                <a href="{{ $resetUrl }}" style="color: #4f46e5; text-decoration: underline; font-size: 14px;">
                                    {{ $resetUrl }}
                                </a>
                            </p>

                            <!-- Avertissement -->
                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 4px; margin: 30px 0;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.6;">
                                    <strong>⚠️ Important :</strong> Ce lien est valide pendant <strong>60 minutes</strong> uniquement. Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                                </p>
                            </div>

                            <!-- Footer -->
                            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e2e8f0; text-align: center;">
                                <p style="margin: 0; color: #94a3b8; font-size: 12px;">
                                    Cet email a été envoyé à <strong>{{ $email }}</strong>
                                </p>
                                <p style="margin: 10px 0 0 0; color: #94a3b8; font-size: 12px;">
                                    © {{ date('Y') }} {{ \App\Models\Setting::get('site_name', config('app.name')) }}. Tous droits réservés.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

