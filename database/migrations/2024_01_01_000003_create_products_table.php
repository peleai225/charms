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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            
            // Identifiants
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->string('barcode')->nullable()->unique(); // Code-barres EAN/UPC
            $table->string('qr_code')->nullable(); // QR Code
            
            // Prix
            $table->decimal('purchase_price', 10, 2)->default(0); // Prix d'achat HT
            $table->decimal('sale_price', 10, 2); // Prix de vente TTC
            $table->decimal('compare_price', 10, 2)->nullable(); // Ancien prix (barré)
            $table->decimal('cost_price', 10, 2)->nullable(); // Coût de revient
            
            // TVA
            $table->decimal('tax_rate', 5, 2)->default(20.00); // Taux de TVA
            
            // Stock (pour produits simples sans variantes)
            $table->integer('stock_quantity')->default(0);
            $table->integer('stock_alert_threshold')->default(5);
            $table->boolean('track_stock')->default(true);
            $table->boolean('allow_backorder')->default(false);
            
            // Catégorie
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            
            // Type de produit
            $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->boolean('has_variants')->default(false);
            
            // Poids et dimensions (pour livraison)
            $table->decimal('weight', 8, 3)->nullable(); // en kg
            $table->decimal('length', 8, 2)->nullable(); // en cm
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Statut
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            
            // Statistiques
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['status', 'is_featured']);
            $table->index('category_id');
            $table->index('sku');
            $table->index('barcode');
            // Index fulltext disponible uniquement pour MySQL
            // $table->fullText(['name', 'short_description', 'description']);
        });

        // Table pivot pour les attributs utilisés par un produit variable
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            
            $table->unique(['product_id', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('products');
    }
};

