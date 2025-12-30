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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Numéro de commande unique
            $table->string('order_number')->unique();
            
            // Client
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            
            // Statut
            $table->enum('status', [
                'pending',      // En attente de paiement
                'confirmed',    // Paiement confirmé
                'processing',   // En préparation
                'shipped',      // Expédiée
                'delivered',    // Livrée
                'cancelled',    // Annulée
                'refunded',     // Remboursée
            ])->default('pending');
            
            // Statut du paiement
            $table->enum('payment_status', [
                'pending',
                'paid',
                'partially_paid',
                'refunded',
                'failed',
            ])->default('pending');
            
            // Montants
            $table->decimal('subtotal', 12, 2); // Sous-total HT
            $table->decimal('tax_amount', 10, 2)->default(0); // TVA
            $table->decimal('shipping_amount', 10, 2)->default(0); // Frais de port
            $table->decimal('discount_amount', 10, 2)->default(0); // Remise
            $table->decimal('total', 12, 2); // Total TTC
            
            // Code promo
            $table->string('coupon_code')->nullable();
            
            // Devise
            $table->string('currency', 3)->default('EUR');
            $table->decimal('currency_rate', 10, 6)->default(1);
            
            // Adresse de facturation
            $table->string('billing_first_name');
            $table->string('billing_last_name');
            $table->string('billing_company')->nullable();
            $table->string('billing_address');
            $table->string('billing_address2')->nullable();
            $table->string('billing_city');
            $table->string('billing_postal_code');
            $table->string('billing_country');
            $table->string('billing_phone')->nullable();
            $table->string('billing_email');
            
            // Adresse de livraison
            $table->string('shipping_first_name');
            $table->string('shipping_last_name');
            $table->string('shipping_company')->nullable();
            $table->string('shipping_address');
            $table->string('shipping_address2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_postal_code');
            $table->string('shipping_country');
            $table->string('shipping_phone')->nullable();
            
            // Livraison
            $table->string('shipping_method')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Poids total
            $table->decimal('total_weight', 8, 3)->nullable();
            
            // Notes
            $table->text('customer_notes')->nullable(); // Notes du client
            $table->text('admin_notes')->nullable(); // Notes internes
            
            // Source
            $table->string('source')->default('web'); // web, pos, phone, etc.
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('order_number');
            $table->index(['status', 'created_at']);
            $table->index(['customer_id', 'created_at']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

