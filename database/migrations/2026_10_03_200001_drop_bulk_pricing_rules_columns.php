<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('bulk_pricing_rules');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('bulk_pricing_rules');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('bulk_pricing_rules')->nullable()->after('compare_price');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->json('bulk_pricing_rules')->nullable()->after('is_featured');
        });
    }
};
