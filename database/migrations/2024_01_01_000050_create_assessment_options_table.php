<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opciones de respuesta para cada pregunta.
 * Cada opción lleva un score_value que se suma al total.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0)->comment('Orden de presentación');
            $table->string('option_text', 255)->comment('Etiqueta visible: "Nunca", "Varios días"…');
            $table->integer('score_value')->default(0)->comment('Puntaje que aporta esta opción');
            $table->timestamps();

            $table->index(['assessment_question_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_options');
    }
};
