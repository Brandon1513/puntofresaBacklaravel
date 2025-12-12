<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_order_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_order_id')
                ->constrained('event_orders')
                ->cascadeOnDelete();

            // tipo de línea
            $table->enum('tipo', ['item', 'bundle', 'extra']);

            // cuando tipo = item
            $table->foreignId('item_id')
                ->nullable()
                ->constrained('items')
                ->nullOnDelete();

            // cuando tipo = bundle
            $table->foreignId('bundle_id')
                ->nullable()
                ->constrained('bundles')
                ->nullOnDelete();

            // cuando tipo = extra (entrega, instalación, etc.)
            $table->string('descripcion')->nullable();

            $table->integer('cantidad')->default(1);

            // precio unitario usado en esta OE
            $table->decimal('precio_unitario', 12, 2)->default(0);

            // % impuesto (ej. 16.00)
            $table->decimal('impuesto_porcentaje', 5, 2)->default(0);

            $table->timestamps();

            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_order_lines');
    }
};
