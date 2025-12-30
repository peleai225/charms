<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            
            // Référence unique du paiement
            $table->string('transaction_id')->unique();
            
            // Méthode de paiement
            $table->enum('method', [
                'card',         // Carte bancaire
                'paypal',       // PayPal
                'bank_transfer', // Virement bancaire
                'check',        // Chèque
                'cash',         // Espèces (POS)
                'other',
            ]);
            
            // Gateway de paiement utilisée
            $table->string('gateway')->nullable(); // stripe, paypal, etc.
            $table->string('gateway_transaction_id')->nullable();
            
            // Montant
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EUR');
            
            // Statut
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'partially_refunded',
            ])->default('pending');
            
            // Remboursement
            $table->decimal('refunded_amount', 12, 2)->default(0);
            
            // Détails de la carte (masqués)
            $table->string('card_brand')->nullable(); // visa, mastercard
            $table->string('card_last4')->nullable(); // 4 derniers chiffres
            
            // Métadonnées du gateway
            $table->json('gateway_response')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Utilisateur (pour les paiements manuels)
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
            $table->index('transaction_id');
        });

        // Table des remboursements
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('refund_number')->unique();
            $table->decimal('amount', 12, 2);
            
            $table->enum('reason', [
                'customer_request',
                'product_defective',
                'wrong_item',
                'not_delivered',
                'duplicate',
                'other',
            ]);
            
            $table->text('notes')->nullable();
            
            $table->enum('status', [
                'pending',
                'approved',
                'processed',
                'rejected',
            ])->default('pending');
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
    }
};

