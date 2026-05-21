<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('card','paypal','bank_transfer','check','cash','cinetpay','lygos','moneyfusion','mobile_money','cod','other')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('card','paypal','bank_transfer','check','cash','cinetpay','lygos','mobile_money','cod','other')");
    }
};
