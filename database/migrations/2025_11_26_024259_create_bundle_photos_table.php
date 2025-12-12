<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_bundle_photos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bundle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')
                ->constrained('bundles')
                ->onDelete('cascade');

            $table->string('path');          // ruta dentro de storage/app/public/...
            $table->boolean('es_principal')->default(false);
            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_photos');
    }
};

