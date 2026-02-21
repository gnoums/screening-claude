<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Libro mayor de créditos. Cada fila es un movimiento (positivo o negativo).
 * El saldo se calcula sumando amount de todos los registros del usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credits_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount')->comment('Positivo: recarga. Negativo: consumo de crédito');
            $table->string('type', 40)->comment('purchase | usage | adjustment | refund');
            $table->string('description')->nullable()->comment('Referencia legible');
            $table->nullableMorphs('reference', 'credits_ledger_reference'); // polymorphic reference
            $table->unsignedBigInteger('balance_after')->default(0)->comment('Saldo después del movimiento');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credits_ledger');
    }
};
