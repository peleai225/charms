<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('abandoned_at')->nullable()->after('updated_at');
            $table->timestamp('reminder_sent_at')->nullable()->after('abandoned_at');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['abandoned_at', 'reminder_sent_at']);
        });
    }
};
