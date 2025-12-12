<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bundle_id')
                  ->constrained('bundles')
                  ->cascadeOnDelete();

            $table->foreignId('item_id')
                  ->constrained('items')
                  ->restrictOnDelete();

            // Cantidad de ese ítem dentro del paquete
            $table->unsignedInteger('cantidad');

            // Precio unitario "congelado" opcional
            // (por si luego cambian los precios del catálogo
            //  y quieres conservar cuánto valía cuando definiste el bundle)
            $table->decimal('precio_unitario_cache', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};

