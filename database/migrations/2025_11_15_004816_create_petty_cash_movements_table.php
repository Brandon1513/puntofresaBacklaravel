<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('petty_cash_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('petty_cash_session_id')
                  ->constrained('petty_cash_sessions')
                  ->onDelete('cascade');

            // ingreso o egreso
            $table->enum('tipo', ['ingreso', 'egreso']);

            $table->decimal('monto', 12, 2);

            // Opcional: ligarlo a un gasto
            $table->foreignId('expense_id')
                  ->nullable()
                  ->constrained('expenses')
                  ->nullOnDelete();

            // Categoría de gasto opcional
            $table->foreignId('expense_category_id')
                  ->nullable()
                  ->constrained('expense_categories')
                  ->nullOnDelete();

            $table->string('concepto', 180)->nullable();
            $table->text('notas')->nullable();

            // Quién capturó el movimiento (normalmente el vendedor)
            $table->foreignId('created_by')
                  ->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_movements');
    }
};
