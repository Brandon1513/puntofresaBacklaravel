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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();

            // Tipo de movimiento
            $table->enum('tipo', [
                'entrada',     // ajuste manual de entrada
                'compra',      // alta por compra
                'merma',       // pérdida / desperdicio
                'dano',        // daño
                'correccion',  // corrección de inventario
            ]);

            $table->integer('cantidad'); // positivo o negativo (ej. merma -2)

            // Para dejar rastro del stock
            $table->integer('stock_antes')->nullable();
            $table->integer('stock_despues')->nullable();

            // Motivo y evidencia
            $table->string('motivo')->nullable();
            $table->text('comentario')->nullable();
            $table->string('evidencia_path')->nullable(); // foto / PDF, etc.

            // Quién hizo el ajuste
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
