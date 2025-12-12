<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');        // Ej: "Pieza", "Juego", "Metro"
            $table->string('abreviatura');   // Ej: "pz", "jg", "m"
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
