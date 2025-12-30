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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            
            // Produit concerné
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Type de mouvement
            $table->enum('type', [
                'purchase',      // Achat fournisseur (entrée)
                'sale',          // Vente (sortie)
                'return_in',     // Retour client (entrée)
                'return_out',    // Retour fournisseur (sortie)
                'adjustment_in', // Ajustement positif
                'adjustment_out',// Ajustement négatif
                'transfer_in',   // Transfert entrant
                'transfer_out',  // Transfert sortant
                'loss',          // Perte/casse
                'inventory',     // Inventaire
            ]);
            
            // Quantité (positive pour entrée, négative pour sortie)
            $table->integer('quantity');
            
            // Stock avant et après mouvement
            $table->integer('stock_before');
            $table->integer('stock_after');
            
            // Prix unitaire au moment du mouvement
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            
            // Référence du document source
            $table->string('reference_type')->nullable(); // Order, PurchaseOrder, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Numéro de lot / série
            $table->string('batch_number')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Utilisateur qui a effectué le mouvement
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
            
            // Index
            $table->index(['product_id', 'created_at']);
            $table->index(['product_variant_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

