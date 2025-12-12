<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_order_id')
                  ->constrained('event_orders')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Si tu módulo de caja chica se llama diferente, sólo ajustas aquí
            $table->foreignId('cash_session_id')
                  ->nullable()
                  ->constrained('cash_sessions')
                  ->nullOnDelete();

            $table->string('metodo'); // efectivo, transferencia, tarjeta, etc.
            $table->decimal('monto', 10, 2);
            $table->string('referencia')->nullable(); // folio transferencia, últimos 4 de tarjeta, etc.
            $table->timestamp('pagado_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payments');
    }
};
