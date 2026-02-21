<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preguntas/ítems de cada prueba de tamizaje.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0)->comment('Orden de presentación');
            $table->text('question_text')->comment('Texto del ítem tal como se muestra al paciente');
            $table->string('question_code', 30)->nullable()->comment('Código interno: q1, q2, PHQ1…');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['assessment_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
