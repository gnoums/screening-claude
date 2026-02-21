<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Respuesta completa de un paciente a una prueba específica.
 * Incluye snapshot del resultado calculado para evitar recalcular.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_request_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained();

            // Resultados calculados por ScoringService
            $table->integer('total_score')->nullable();
            $table->string('severity_label', 60)->nullable();
            $table->text('interpretation_text')->nullable();

            // Snapshot del nombre de la prueba para integridad histórica
            $table->string('assessment_name_snapshot', 150)->nullable();
            $table->string('assessment_version_snapshot', 20)->nullable();

            // Metadatos de la sesión del paciente
            $table->string('respondent_ip', 45)->nullable();
            $table->string('respondent_user_agent')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('screening_request_item_id');
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_responses');
    }
};
