<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de exportaciones de PDF generadas por la psicóloga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_request_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->nullable()->comment('Ruta relativa al disco de almacenamiento');
            $table->string('format', 10)->default('pdf');
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['user_id', 'screening_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
