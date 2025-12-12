<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_precio_unitario_paquete_to_bundle_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bundle_items', function (Blueprint $table) {
            $table->decimal('precio_unitario_paquete', 12, 2)
                  ->nullable()
                  ->after('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('bundle_items', function (Blueprint $table) {
            $table->dropColumn('precio_unitario_paquete');
        });
    }
};
