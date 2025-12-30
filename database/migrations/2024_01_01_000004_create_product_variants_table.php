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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Nom généré automatiquement (ex: "Rouge - M")
            $table->string('name')->nullable();
            
            // Identifiants uniques
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('qr_code')->nullable();
            
            // Prix (si différent du produit parent)
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('compare_price', 10, 2)->nullable();
            
            // Stock
            $table->integer('stock_quantity')->default(0);
            $table->integer('stock_alert_threshold')->nullable();
            
            // Poids (si différent)
            $table->decimal('weight', 8, 3)->nullable();
            
            // Image spécifique à la variante
            $table->string('image')->nullable();
            
            // Statut
            $table->boolean('is_active')->default(true);
            
            // Position pour l'ordre d'affichage
            $table->integer('position')->default(0);
            
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('sku');
            $table->index('barcode');
        });

        // Valeurs d'attributs pour chaque variante
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            
            $table->unique(['product_variant_id', 'attribute_id'], 'variant_attribute_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
    }
};

