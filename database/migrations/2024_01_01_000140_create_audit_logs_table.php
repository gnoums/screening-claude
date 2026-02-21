<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de auditoría: accesos públicos, acciones críticas, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('auditable', 'audit_logs_auditable'); // entidad relacionada
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 80)->comment('token.accessed | token.completed | token.invalid | pdf.generated…');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable()->comment('Datos adicionales del evento');
            $table->timestamp('occurred_at')->useCurrent();

            // No timestamps() para tabla append-only
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['event', 'occurred_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
