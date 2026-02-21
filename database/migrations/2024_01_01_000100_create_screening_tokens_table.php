<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens de acceso para pacientes.
 * NUNCA se guarda el token plano; solo el hash SHA-256.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_request_id')->constrained()->cascadeOnDelete();

            // Hash SHA-256 del token. El token plano solo viaja en la URL.
            $table->string('token_hash', 64)->unique()->comment('SHA-256 del token plano');

            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable()->comment('Null = no usado aún');
            $table->boolean('is_invalidated')->default(false)->comment('Invalidación manual o por expiración');

            // Metadatos de auditoría del primer uso
            $table->string('last_ip', 45)->nullable();
            $table->string('last_user_agent')->nullable();

            $table->timestamps();

            $table->index('expires_at');
            $table->index(['token_hash', 'expires_at', 'is_invalidated']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_tokens');
    }
};
