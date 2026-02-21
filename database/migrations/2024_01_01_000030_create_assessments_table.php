<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de pruebas de tamizaje disponibles (PHQ-9, GAD-7, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique()->comment('Identificador legible: phq-9, gad-7');
            $table->string('name', 150)->comment('Nombre completo de la prueba');
            $table->string('short_name', 30)->nullable()->comment('Abreviatura: PHQ-9');
            $table->text('description')->nullable();
            $table->string('version', 20)->nullable()->comment('Versión publicada de la escala');
            $table->string('author', 150)->nullable()->comment('Autor(es) de la prueba original');
            $table->boolean('is_active')->default(true);
            $table->integer('estimated_minutes')->default(5)->comment('Tiempo estimado de aplicación');
            $table->integer('credits_cost')->default(1)->comment('Créditos que cuesta enviar esta prueba');
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
