<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('petty_cash_sessions', function (Blueprint $table) {
            // Texto opcional para guardar las notas de cierre
            $table->text('notas_cierre')
                ->nullable()
                ->after('diferencia'); // o after('cerrada_en') si prefieres
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_sessions', function (Blueprint $table) {
            $table->dropColumn('notas_cierre');
        });
    }
};
