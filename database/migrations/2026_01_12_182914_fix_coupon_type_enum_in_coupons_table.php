<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum pour utiliser 'fixed' au lieu de 'fixed_amount'
        // MySQL ne permet pas de modifier directement un enum, donc on doit le recréer
        DB::statement("ALTER TABLE `coupons` MODIFY COLUMN `type` ENUM('percentage', 'fixed', 'free_shipping') NOT NULL");
        
        // Mettre à jour les valeurs existantes
        DB::statement("UPDATE `coupons` SET `type` = 'fixed' WHERE `type` = 'fixed_amount'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre l'ancien enum
        DB::statement("UPDATE `coupons` SET `type` = 'fixed_amount' WHERE `type` = 'fixed'");
        DB::statement("ALTER TABLE `coupons` MODIFY COLUMN `type` ENUM('percentage', 'fixed_amount', 'free_shipping') NOT NULL");
    }
};
