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
    Schema::create('items', function (Blueprint $table) {
        $table->id();

        // Identificación
        $table->string('sku')->unique();
        $table->string('nombre');
        $table->foreignId('categoria_id')->nullable()
            ->constrained('item_categorias') // o 'categorias' si ya la tienes
            ->nullOnDelete();
        $table->foreignId('unidad_id')->nullable()
            ->constrained('unidades') // o 'unidades_medida' según tu proyecto
            ->nullOnDelete();

        // QR general del ítem (para catálogo, ficha, etc.)
        $table->string('qr_code')->nullable()->unique();

        // Precios de renta
        $table->decimal('precio_renta_dia', 12, 2)->default(0);
        $table->decimal('precio_renta_fin', 12, 2)->default(0); // fin de semana / evento

        // Costos
        $table->decimal('costo_promedio', 12, 2)->default(0);
        $table->decimal('costo_reposicion', 12, 2)->default(0);

        // Inventario básico
        $table->integer('stock_fisico')->default(0);
        $table->integer('stock_minimo')->default(0);
        $table->string('ubicacion')->nullable(); // anaquel / bodega / pasillo

        // Estado
        $table->boolean('activo')->default(true);

        // Tags simples (ej: "sala, moderno, gris")
        $table->string('tags')->nullable();

        // Texto libre opcional
        $table->text('descripcion')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
