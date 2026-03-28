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

        // Ajouter une colonne virtuelle qui vaut le slug pour les produits actifs
        // et l'ID pour les produits supprimés, puis indexer dessus.
        // Compatible MariaDB 10.2+ et MySQL 5.7+
        \DB::statement('ALTER TABLE products ADD COLUMN slug_active VARCHAR(255) AS (IF(deleted_at IS NULL, slug, CONCAT(slug, \'-deleted-\', id))) STORED');
        \DB::statement('CREATE UNIQUE INDEX products_slug_unique ON products (slug_active)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement('DROP INDEX products_slug_unique ON products');
        \DB::statement('ALTER TABLE products DROP COLUMN slug_active');
        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};
