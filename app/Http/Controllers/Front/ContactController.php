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

}

