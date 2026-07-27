<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
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
        $keys = ['mail_driver','mail_host','mail_port','mail_username','mail_password','mail_encryption','mail_from_name','mail_from_address'];
        $settings = Cache::remember('settings.mail', 60, fn () => Setting::getMany($keys));

        $mailDriver   = $settings['mail_driver']      ?? null;
        $mailHost     = $settings['mail_host']         ?? null;
        $mailPort     = $settings['mail_port']         ?? null;
        $mailUsername = $settings['mail_username']     ?? null;
        $mailPassword = $settings['mail_password']     ?? null;
        $mailEncryption = $settings['mail_encryption'] ?? null;
        $mailFromName   = $settings['mail_from_name']  ?? null;
        $mailFromAddress = $settings['mail_from_address'] ?? null;

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

