<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_sessions', function (Blueprint $table) {
            $table->decimal('saldo_actual', 12, 2)
                ->default(0)
                ->after('saldo_inicial');
        });

        // Para las sesiones ya existentes: igualamos saldo_actual = saldo_inicial
        DB::table('petty_cash_sessions')
            ->update(['saldo_actual' => DB::raw('saldo_inicial')]);
    }

    public function down(): void
    {
        Schema::table('petty_cash_sessions', function (Blueprint $table) {
            $table->dropColumn('saldo_actual');
        });
    }
};
