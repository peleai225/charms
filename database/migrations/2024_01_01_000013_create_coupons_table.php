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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Type de remise
            $table->enum('type', [
                'percentage',    // Pourcentage
                'fixed_amount', // Montant fixe
                'free_shipping', // Livraison gratuite
            ]);
            
            // Valeur de la remise
            $table->decimal('value', 10, 2); // % ou montant
            
            // Conditions
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // Plafond pour %
            
            // Limites d'utilisation
            $table->integer('usage_limit')->nullable(); // Nombre total d'utilisations
            $table->integer('usage_limit_per_customer')->nullable();
            $table->integer('usage_count')->default(0);
            
            // Validité
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Restrictions
            $table->json('applicable_products')->nullable(); // IDs produits
            $table->json('applicable_categories')->nullable(); // IDs catégories
            $table->json('excluded_products')->nullable();
            $table->boolean('first_order_only')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index(['is_active', 'starts_at', 'expires_at']);
        });

        // Historique d'utilisation des coupons
        Schema::create('coupon_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            
            $table->index(['coupon_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usage');
        Schema::dropIfExists('coupons');
    }
};

