<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const BASE = "'pending','confirmed','processing','shipped','delivered','cancelled','refunded'";

    public function up(): void
    {
        // Le code proposait déjà « Livreur en route », mais la colonne le refusait (erreur à l'enregistrement)
        DB::statement("ALTER TABLE orders MODIFY status ENUM(".self::BASE.",'delivery_in_progress') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'delivery_in_progress')->update(['status' => 'processing']);
        DB::statement('ALTER TABLE orders MODIFY status ENUM('.self::BASE.") NOT NULL DEFAULT 'pending'");
    }
};
