<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Attributs (ex: Couleur, Taille, Matière)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: "Couleur", "Taille"
            $table->string('slug')->unique();
            $table->string('type')->default('select'); // select, color, text, number
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Valeurs d'attributs (ex: Rouge, Bleu, S, M, L)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // ex: "Rouge", "S"
            $table->string('slug');
            $table->string('color_code')->nullable(); // Pour les couleurs (#FF0000)
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['attribute_id', 'slug']);
            $table->index('attribute_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};

