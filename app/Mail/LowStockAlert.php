<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Product $product,
        public ?ProductVariant $variant,
        public int $currentStock,
        public int $threshold,
        public bool $isOutOfStock = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $productName = $this->variant 
            ? "{$this->product->name} - {$this->variant->name}" 
            : $this->product->name;

        $subject = $this->isOutOfStock
            ? "🚨 Rupture de stock : {$productName}"
            : "⚠️ Stock bas : {$productName} ({$this->currentStock} restant)";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.low-stock-alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
