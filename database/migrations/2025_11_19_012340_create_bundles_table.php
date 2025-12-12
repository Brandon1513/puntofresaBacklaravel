<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();

            // Identificación del paquete
            $table->string('sku', 50)->unique();
            $table->string('nombre', 200);

            // Opcional: descripción corta
            $table->text('descripcion')->nullable();

            // Precio propio del paquete (si lo defines manualmente)
            $table->decimal('precio_personalizado', 12, 2)->nullable();

            // % de descuento si el precio se calcula en base a los ítems
            // (por ejemplo 10% sobre la suma de precios de renta)
            $table->unsignedTinyInteger('descuento_porcentaje')->default(0);

            // Flag para saber si usamos precio propio o calculado
            $table->boolean('usar_precio_personalizado')->default(false);

            // Paquete activo/inactivo
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};

