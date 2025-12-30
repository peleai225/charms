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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Liaison avec un compte utilisateur (optionnel)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Informations personnelles
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            // Entreprise (B2B)
            $table->string('company_name')->nullable();
            $table->string('vat_number')->nullable(); // Numéro TVA
            $table->string('siret')->nullable();
            
            // Statut client
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->enum('type', ['individual', 'company'])->default('individual');
            
            // Groupe client (pour tarifs différenciés)
            $table->string('customer_group')->default('default');
            
            // Marketing
            $table->boolean('newsletter')->default(false);
            $table->boolean('accepts_marketing')->default(false);
            $table->string('referral_source')->nullable(); // Comment nous ont-ils trouvé
            
            // Notes internes
            $table->text('notes')->nullable();
            
            // Statistiques
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->timestamp('last_order_at')->nullable();
            
            // Points fidélité
            $table->unsignedInteger('loyalty_points')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('email');
            $table->index('status');
            $table->index(['last_name', 'first_name']);
        });

        // Adresses des clients
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            
            $table->enum('type', ['billing', 'shipping'])->default('shipping');
            $table->boolean('is_default')->default(false);
            
            // Destinataire
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            
            // Adresse
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('postal_code');
            $table->string('state')->nullable(); // Région/Département
            $table->string('country')->default('FR');
            
            // Instructions de livraison
            $table->text('delivery_instructions')->nullable();
            
            $table->timestamps();
            
            $table->index(['customer_id', 'type', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};

