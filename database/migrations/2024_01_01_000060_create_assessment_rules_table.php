<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reglas de interpretación por rango de puntaje.
 * Ejemplo PHQ-9: 0-4 → Mínima, 5-9 → Leve, 10-14 → Moderada…
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->integer('min_score')->comment('Puntaje mínimo (inclusive)');
            $table->integer('max_score')->comment('Puntaje máximo (inclusive)');
            $table->string('severity_label', 60)->comment('Etiqueta: Mínima, Leve, Moderada, Severa');
            $table->text('interpretation_text')->comment('Explicación clínica del rango');
            $table->string('color_code', 10)->nullable()->comment('Hex para UI: #4CAF50');
            $table->timestamps();

            $table->index(['assessment_id', 'min_score', 'max_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_rules');
    }
};
