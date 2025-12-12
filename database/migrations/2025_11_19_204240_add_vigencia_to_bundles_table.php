<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_vigencia_to_bundles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->date('vigente_desde')->nullable()->after('activo');
            $table->date('vigente_hasta')->nullable()->after('vigente_desde');
        });
    }

    public function down(): void
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn(['vigente_desde', 'vigente_hasta']);
        });
    }
};
