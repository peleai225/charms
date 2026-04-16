<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInvoice extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre facture - Commande ' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        try {
            // Générer la facture PDF
            $this->order->load(['customer', 'items.product', 'items.productVariant']);
            $pdf = Pdf::loadView('admin.orders.invoice', ['order' => $this->order]);
            
            return [
                Attachment::fromData(
                    fn () => $pdf->output(),
                    "facture-{$this->order->order_number}.pdf"
                )
                ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur génération facture PDF pour email', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }
}
