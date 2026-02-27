<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permet de recréer un produit avec le même slug après suppression (soft-delete).
     * L'unicité du slug ne s'applique qu'aux produits actifs (non supprimés).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        // Index unique partiel : seul un produit actif (deleted_at IS NULL) peut avoir un slug donné.
        // Les produits soft-deleted peuvent partager des slugs, permettant la recréation.
        \DB::statement('CREATE UNIQUE INDEX products_slug_unique ON products (slug, (IF(deleted_at IS NULL, 1, NULL)))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_slug_unique');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};
