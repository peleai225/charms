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
        // Journaux comptables
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // VE, AC, BQ, OD
            $table->string('name'); // Ventes, Achats, Banque, Opérations diverses
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Comptes comptables
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 701000, 411000, etc.
            $table->string('name');
            $table->enum('type', [
                'asset',      // Actif
                'liability',  // Passif
                'equity',     // Capitaux propres
                'revenue',    // Produits
                'expense',    // Charges
            ]);
            $table->foreignId('parent_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('type');
        });

        // Écritures comptables
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            
            // Journal
            $table->foreignId('journal_id')->constrained('accounting_journals');
            
            // Numéro de pièce
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            
            // Description
            $table->string('label');
            $table->text('description')->nullable();
            
            // Document source
            $table->string('reference_type')->nullable(); // Order, Payment, StockMovement
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('document_number')->nullable(); // Numéro facture, etc.
            
            // Statut
            $table->enum('status', [
                'draft',
                'validated',
                'locked',
            ])->default('draft');
            
            // Période comptable
            $table->string('fiscal_year')->nullable();
            $table->string('fiscal_period')->nullable(); // 2024-01
            
            // Utilisateur
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['entry_date', 'journal_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('fiscal_period');
        });

        // Lignes d'écritures (débit/crédit)
        Schema::create('accounting_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('accounting_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounting_accounts');
            
            $table->string('label')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            
            // Analytique (optionnel)
            $table->string('cost_center')->nullable();
            
            $table->timestamps();
            
            $table->index(['entry_id', 'account_id']);
        });

        // Périodes comptables (pour verrouillage)
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 2024-01
            $table->string('name'); // Janvier 2024
            $table->date('start_date');
            $table->date('end_date');
            $table->string('fiscal_year');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('accounting_entry_lines');
        Schema::dropIfExists('accounting_entries');
        Schema::dropIfExists('accounting_accounts');
        Schema::dropIfExists('accounting_journals');
    }
};

