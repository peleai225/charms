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
        Schema::table('orders', function (Blueprint $table) {
            // Champs de paiement
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('admin_notes');
            }
            if (!Schema::hasColumn('orders', 'shipping_email')) {
                $table->string('shipping_email')->nullable()->after('shipping_phone');
            }
            
            // Renommer les colonnes address2 si elles existent avec l'ancien nom
            // (SQLite ne supporte pas renameColumn donc on ajoute les nouvelles)
            if (!Schema::hasColumn('orders', 'billing_address_2')) {
                $table->string('billing_address_2')->nullable()->after('billing_address');
            }
            if (!Schema::hasColumn('orders', 'shipping_address_2')) {
                $table->string('shipping_address_2')->nullable()->after('shipping_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'paid_at',
                'notes',
                'shipping_email',
                'billing_address_2',
                'shipping_address_2',
            ]);
        });
    }
};

