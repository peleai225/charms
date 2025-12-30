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
        // Journal d'activité (audit trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Utilisateur
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_type')->nullable(); // admin, customer
            
            // Action
            $table->string('action'); // created, updated, deleted, viewed, login, logout
            $table->string('description');
            
            // Modèle concerné
            $table->string('subject_type')->nullable(); // Product, Order, etc.
            $table->unsignedBigInteger('subject_id')->nullable();
            
            // Données avant/après modification
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Contexte
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
            $table->index('created_at');
        });

        // Reviews / Avis clients
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('author_name');
            $table->string('author_email');
            
            $table->tinyInteger('rating'); // 1-5
            $table->string('title')->nullable();
            $table->text('content');
            
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');
            
            $table->boolean('is_verified_purchase')->default(false);
            
            $table->timestamps();
            
            $table->index(['product_id', 'status', 'rating']);
            $table->index(['status', 'created_at']);
        });

        // Wishlist
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['customer_id', 'product_id', 'product_variant_id'], 'wishlist_unique');
        });

        // Panier persistant
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('coupon_code')->nullable();
            $table->timestamps();
            
            $table->index('session_id');
            $table->index('customer_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('activity_logs');
    }
};

