<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal des points et versements : le solde d'un livreur est la somme de balance_change
        Schema::create('courier_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('couriers');
            $table->enum('type', ['point', 'payment', 'adjustment']);
            $table->decimal('amount_collected', 12, 2)->default(0);
            $table->decimal('fees', 12, 2)->default(0);
            $table->decimal('amount_received', 12, 2)->default(0);
            $table->decimal('balance_change', 12, 2);
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('courier_movement_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_movement_id')->constrained('courier_movements')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders');
            $table->enum('outcome', ['delivered', 'failed']);
            $table->string('issue', 30)->nullable();
            $table->decimal('amount_collected', 12, 2);
            $table->decimal('fee', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_movement_orders');
        Schema::dropIfExists('courier_movements');
    }
};
