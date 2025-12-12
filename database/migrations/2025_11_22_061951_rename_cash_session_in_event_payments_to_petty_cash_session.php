<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_payments', function (Blueprint $table) {
            // 1) quitar el foreign key actual
            $table->dropForeign('event_payments_cash_session_id_foreign');

            // 2) renombrar la columna
            $table->renameColumn('cash_session_id', 'petty_cash_session_id');
        });

        Schema::table('event_payments', function (Blueprint $table) {
            // 3) agregar el nuevo foreign key hacia petty_cash_sessions
            $table->foreign('petty_cash_session_id')
                ->references('id')
                ->on('petty_cash_sessions')
                ->nullOnDelete(); // si borras la sesión, deja null
        });
    }

    public function down(): void
    {
        Schema::table('event_payments', function (Blueprint $table) {
            // revertir: quitar fk nuevo
            $table->dropForeign('event_payments_petty_cash_session_id_foreign');

            // renombrar de vuelta
            $table->renameColumn('petty_cash_session_id', 'cash_session_id');
        });

        Schema::table('event_payments', function (Blueprint $table) {
            // volver a apuntar a cash_sessions
            $table->foreign('cash_session_id')
                ->references('id')
                ->on('cash_sessions')
                ->nullOnDelete();
        });
    }
};
