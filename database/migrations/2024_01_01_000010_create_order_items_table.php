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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            
            // Produit
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            
            // Snapshot des données produit au moment de la commande
            $table->string('name');
            $table->string('sku');
            $table->string('variant_name')->nullable();
            $table->json('options')->nullable(); // Attributs sélectionnés
            
            // Quantités
            $table->integer('quantity');
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_refunded')->default(0);
            
            // Prix
            $table->decimal('unit_price', 10, 2); // Prix unitaire HT
            $table->decimal('tax_rate', 5, 2)->default(20);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 12, 2); // Total TTC ligne
            
            // Coût (pour calcul marge)
            $table->decimal('cost_price', 10, 2)->nullable();
            
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

