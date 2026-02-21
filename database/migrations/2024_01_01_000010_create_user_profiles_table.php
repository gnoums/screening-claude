<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perfil extendido de la psicóloga (datos profesionales, cédula, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('professional_license')->nullable()->comment('Cédula profesional');
            $table->string('phone', 20)->nullable();
            $table->string('specialty', 100)->nullable()->comment('Especialidad clínica');
            $table->string('institution')->nullable()->comment('Institución u organización');
            $table->string('timezone', 60)->default('America/Mexico_City');
            $table->string('logo_path')->nullable()->comment('Ruta al logo para PDF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
