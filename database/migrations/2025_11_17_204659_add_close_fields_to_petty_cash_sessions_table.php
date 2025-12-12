<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_sessions', function (Blueprint $table) {
            // 🚫 NO volver a crear diferencia / saldo_teorico_cierre / saldo_contado_cierre
            // solo agregamos closed_by_id

            $table->foreignId('closed_by_id')
                ->nullable()
                ->after('opened_by_id')
                ->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_sessions', function (Blueprint $table) {
            $table->dropForeign(['closed_by_id']);
            $table->dropColumn('closed_by_id');
        });
    }
};
