<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('petty_cash_sessions', function (Blueprint $table) {
            $table->id();

            // Día de la caja
            $table->date('fecha');

            // Vendedor responsable de la caja
            $table->foreignId('responsable_id')
                  ->constrained('users');

            // Quién abrió la caja (admin/finanzas)
            $table->foreignId('opened_by_id')
                  ->constrained('users');

            // Saldos
            $table->decimal('saldo_inicial', 12, 2);
            $table->decimal('saldo_teorico_cierre', 12, 2)->nullable();
            $table->decimal('saldo_contado_cierre', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();

            // Estado
            $table->enum('status', ['abierta', 'cerrada'])->default('abierta');

            $table->timestamp('abierta_en')->nullable();
            $table->timestamp('cerrada_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_sessions');
    }
};
