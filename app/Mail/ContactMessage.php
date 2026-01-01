<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $messageSubject,
        public string $message
    ) {}

    public function envelope(): Envelope
    {
        $subjectLabels = [
            'order' => 'Question sur une commande',
            'product' => 'Question sur un produit',
            'return' => 'Retour / Remboursement',
            'partnership' => 'Partenariat',
            'other' => 'Autre',
        ];

        $subjectLabel = $subjectLabels[$this->messageSubject] ?? 'Message de contact';

        return new Envelope(
            subject: 'Nouveau message de contact : ' . $subjectLabel,
            replyTo: [$this->email => $this->name],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.message',
        );
    }
}

