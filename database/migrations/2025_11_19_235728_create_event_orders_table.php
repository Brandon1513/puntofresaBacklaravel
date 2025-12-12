

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_orders', function (Blueprint $table) {
            $table->id();

            // Folio interno
            $table->string('folio')->unique()->nullable();

            // Relación con cliente
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();

            // Snapshot de datos del cliente en el momento de la OE
            $table->string('cliente_nombre');
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono')->nullable();

            // Contacto específico del evento
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_telefono')->nullable();

            // Lugar del evento
            $table->string('lugar')->nullable();      // salón, jardín, etc.
            $table->string('direccion')->nullable();  // dirección completa

            // Fechas clave
            $table->dateTime('fecha_entrega')->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_recoleccion')->nullable();

            $table->text('notas')->nullable();

            // Estatus del flujo
            $table->string('estatus', 30)->default('borrador');
            // borrador | cotizacion | confirmada | preparacion | salida | regreso | cierre

            // Totales
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();

            $table->index('estatus');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_orders');
    }
};
