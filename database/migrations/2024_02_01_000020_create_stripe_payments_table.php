<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de pagos Stripe para auditoría y reconciliación.
 * La fuente de verdad de créditos sigue siendo credits_ledger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_session_id', 200)->unique()->comment('checkout.session ID de Stripe');
            $table->string('stripe_payment_intent', 200)->nullable()->index();
            $table->string('package_key', 60)->comment('Clave del paquete: credits_10, credits_50…');
            $table->unsignedInteger('credits_amount')->comment('Créditos a acreditar');
            $table->unsignedInteger('amount_paid_cents')->comment('Monto pagado en centavos');
            $table->string('currency', 3)->default('MXN');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('stripe_payload')->nullable()->comment('Evento completo del webhook para auditoría');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_payments');
    }
};
