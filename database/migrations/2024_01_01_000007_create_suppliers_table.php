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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            
            // Adresse
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('France');
            
            // Contact principal
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            
            // Conditions
            $table->integer('payment_terms')->default(30); // Délai de paiement en jours
            $table->decimal('discount_rate', 5, 2)->default(0); // Remise négociée
            
            // Notes
            $table->text('notes')->nullable();
            
            // Statut
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Liaison produit-fournisseur avec prix d'achat spécifique
        Schema::create('product_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_sku')->nullable(); // Référence fournisseur
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->integer('min_order_quantity')->default(1);
            $table->integer('lead_time_days')->nullable(); // Délai de livraison
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->unique(['product_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
        Schema::dropIfExists('suppliers');
    }
};

