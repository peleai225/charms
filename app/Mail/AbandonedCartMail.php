<?php

namespace App\Mail;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Cart $cart,
        public Customer $customer,
    ) {}

    public function envelope(): Envelope
    {
        $siteName = Setting::get('site_name', config('app.name'));

        return new Envelope(
            subject: "Vous avez oublié quelque chose dans votre panier 🛒 — {$siteName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.abandoned-cart',
        );
    }
}
