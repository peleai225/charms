<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier le ENUM pour inclure 'lygos'
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM(
            'card',
            'paypal',
            'bank_transfer',
            'check',
            'cash',
            'cinetpay',
            'lygos',
            'mobile_money',
            'cod',
            'other'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'lygos' de l'ENUM
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM(
            'card',
            'paypal',
            'bank_transfer',
            'check',
            'cash',
            'cinetpay',
            'mobile_money',
            'cod',
            'other'
        )");
    }
};

