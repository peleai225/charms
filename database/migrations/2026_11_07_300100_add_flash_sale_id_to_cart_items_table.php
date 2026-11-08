<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marque les lignes de panier dont le prix vient d'une vente flash.
 *
 * Volontairement sans clé étrangère : si la vente est supprimée, la marque
 * reste et le panier sait qu'il doit revenir au prix normal. Une clé avec
 * nullOnDelete effacerait la marque et laisserait le prix flash en place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('flash_sale_id')->nullable()->after('unit_price')->index();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['flash_sale_id']);
            $table->dropColumn('flash_sale_id');
        });
    }
};
