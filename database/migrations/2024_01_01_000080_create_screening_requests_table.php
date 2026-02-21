<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitud de tamizaje: agrupa una o varias pruebas enviadas a un paciente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('Psicóloga que envía')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->enum('status', [
                'draft',       // creada pero no enviada
                'sent',        // email enviado
                'partially_completed', // al menos una prueba completada
                'completed',   // todas las pruebas completadas
                'expired',     // token caducó sin completar
                'cancelled',
            ])->default('draft');

            $table->string('recipient_email', 150)->comment('Email al que se envió (puede diferir del paciente)');
            $table->text('message_to_patient')->nullable()->comment('Mensaje personalizado en el email');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('credits_charged')->default(0)->comment('Créditos descontados');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['patient_id', 'status']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_requests');
    }
};
