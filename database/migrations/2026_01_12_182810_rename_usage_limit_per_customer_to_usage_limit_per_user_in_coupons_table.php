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
        if (Schema::hasColumn('coupons', 'usage_limit_per_customer') && !Schema::hasColumn('coupons', 'usage_limit_per_user')) {
            // MySQL ne supporte pas toujours renameColumn, utiliser ALTER TABLE directement
            DB::statement('ALTER TABLE `coupons` CHANGE `usage_limit_per_customer` `usage_limit_per_user` INT(11) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'usage_limit_per_user') && !Schema::hasColumn('coupons', 'usage_limit_per_customer')) {
            DB::statement('ALTER TABLE `coupons` CHANGE `usage_limit_per_user` `usage_limit_per_customer` INT(11) NULL');
        }
    }
};
