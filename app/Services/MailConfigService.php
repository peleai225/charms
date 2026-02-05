<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class MailConfigService
{
    /**
     * Configurer la connexion mail depuis les paramètres de la base de données
     * Cette méthode doit être appelée avant chaque envoi d'email pour utiliser
     * les paramètres configurés dans l'admin
     */
    public static function configureFromSettings(): void
    {
        $mailDriver = Setting::get('mail_driver');
        $mailHost = Setting::get('mail_host');
        $mailPort = Setting::get('mail_port');
        $mailUsername = Setting::get('mail_username');
        $mailPassword = Setting::get('mail_password');
        $mailEncryption = Setting::get('mail_encryption');
        $mailFromName = Setting::get('mail_from_name');
        $mailFromAddress = Setting::get('mail_from_address');

        // Si aucun paramètre n'est configuré, utiliser les valeurs par défaut
        if (!$mailDriver && !$mailHost) {
            return;
        }

        if ($mailDriver) {
            Config::set('mail.default', $mailDriver);
        }

        if ($mailHost) {
            Config::set('mail.mailers.smtp.host', $mailHost);
        }

        if ($mailPort) {
            Config::set('mail.mailers.smtp.port', $mailPort);
        }

        if ($mailUsername) {
            Config::set('mail.mailers.smtp.username', $mailUsername);
        }

        if ($mailPassword) {
            Config::set('mail.mailers.smtp.password', $mailPassword);
        }

        if ($mailEncryption && $mailEncryption !== 'null') {
            Config::set('mail.mailers.smtp.encryption', $mailEncryption);
        } else {
            Config::set('mail.mailers.smtp.encryption', null);
        }

        if ($mailFromName) {
            Config::set('mail.from.name', $mailFromName);
        }

        if ($mailFromAddress) {
            Config::set('mail.from.address', $mailFromAddress);
        }
    }
}

