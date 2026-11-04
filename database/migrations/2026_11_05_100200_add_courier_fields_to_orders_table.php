<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('courier_id')->nullable()->constrained('couriers')->nullOnDelete();
            $table->dateTime('dispatched_at')->nullable();
            // null = zone de livraison inconnue, frais à compléter au point
            $table->decimal('courier_fee', 10, 2)->nullable();
            $table->unsignedSmallInteger('delivery_attempts')->default(0);
            $table->string('last_delivery_issue', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('courier_id');
            $table->dropColumn(['dispatched_at', 'courier_fee', 'delivery_attempts', 'last_delivery_issue']);
        });
    }
};
