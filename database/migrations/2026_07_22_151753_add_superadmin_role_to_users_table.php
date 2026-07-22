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
        // Modifier l'enum pour inclure 'superadmin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'staff', 'customer', 'superadmin') DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'enum d'origine
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'staff', 'customer') DEFAULT 'customer'");
    }
};
