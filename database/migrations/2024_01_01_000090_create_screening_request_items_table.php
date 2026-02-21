<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ítems de una solicitud: cada fila representa una prueba dentro del paquete enviado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained();
            $table->unsignedSmallInteger('order')->default(0)->comment('Orden de presentación al paciente');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['screening_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_request_items');
    }
};
