<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Afficher la page de contact
     */
    public function index()
    {
        return view('front.pages.contact');
    }

    /**
     * Traiter le formulaire de contact
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Récupérer l'email de contact depuis les paramètres
        $contactEmail = Setting::get('contact_email', config('mail.from.address'));

        try {
            // Configurer la connexion mail depuis les paramètres si disponibles
            \App\Services\MailConfigService::configureFromSettings();

            // Envoyer l'email
            Mail::to($contactEmail)->send(new ContactMessage(
                $validated['name'],
                $validated['email'],
                $validated['subject'],
                $validated['message']
            ));

            return back()->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            \Log::error('Contact form error trace: ' . $e->getTraceAsString());
            
            // En mode debug, afficher l'erreur exacte
            $errorMessage = config('app.debug') 
                ? 'Erreur : ' . $e->getMessage() 
                : 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer plus tard.';
            
            return back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Configurer la connexion mail depuis les paramètres de la base de données
     */
    protected function configureMailFromSettings(): void
    {
        $mailDriver = Setting::get('mail_driver');
        $mailHost = Setting::get('mail_host');
        $mailPort = Setting::get('mail_port');
        $mailUsername = Setting::get('mail_username');
        $mailPassword = Setting::get('mail_password');
        $mailEncryption = Setting::get('mail_encryption');
        $mailFromName = Setting::get('mail_from_name');
        $mailFromAddress = Setting::get('mail_from_address');

        if ($mailDriver) {
            config(['mail.default' => $mailDriver]);
        }

        if ($mailHost) {
            config(['mail.mailers.smtp.host' => $mailHost]);
        }

        if ($mailPort) {
            config(['mail.mailers.smtp.port' => $mailPort]);
        }

        if ($mailUsername) {
            config(['mail.mailers.smtp.username' => $mailUsername]);
        }

        if ($mailPassword) {
            config(['mail.mailers.smtp.password' => $mailPassword]);
        }

        if ($mailEncryption && $mailEncryption !== 'null') {
            config(['mail.mailers.smtp.encryption' => $mailEncryption]);
        } else {
            config(['mail.mailers.smtp.encryption' => null]);
        }

        if ($mailFromName) {
            config(['mail.from.name' => $mailFromName]);
        }

        if ($mailFromAddress) {
            config(['mail.from.address' => $mailFromAddress]);
        }
    }
}

