<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a per-psychologist sequential folio number to screening requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screening_requests', function (Blueprint $table) {
            $table->unsignedInteger('user_request_number')
                ->nullable()
                ->after('user_id')
                ->comment('Sequential folio per psychologist (1, 2, 3…)');
        });
    }

    public function down(): void
    {
        Schema::table('screening_requests', function (Blueprint $table) {
            $table->dropColumn('user_request_number');
        });
    }
};
