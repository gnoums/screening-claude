<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pacientes registrados por la psicóloga.
 * No tienen cuenta de usuario; acceden vía token público.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('Psicóloga propietaria')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email', 150)->nullable();
            $table->string('phone', 25)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('sex', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->text('notes')->nullable()->comment('Notas internas de la psicóloga');

            $table->softDeletes();
            $table->timestamps();

            // Índices de búsqueda
            $table->index('user_id');
            $table->index(['user_id', 'email']);
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
