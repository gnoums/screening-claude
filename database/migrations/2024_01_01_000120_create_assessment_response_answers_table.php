<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Respuesta individual por pregunta dentro de una respuesta de prueba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained();
            $table->foreignId('assessment_option_id')->constrained();

            // Snapshots para integridad: si la prueba cambia, el historial no se altera
            $table->integer('score_value_snapshot')->default(0);
            $table->string('option_text_snapshot', 255)->nullable();

            $table->timestamps();

            $table->index(['assessment_response_id', 'assessment_question_id'], 'ara_response_question_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_response_answers');
    }
};
