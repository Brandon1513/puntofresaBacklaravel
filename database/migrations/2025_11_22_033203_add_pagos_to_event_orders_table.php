<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_orders', function (Blueprint $table) {
            $table->decimal('pagado_total', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('event_orders', function (Blueprint $table) {
            $table->dropColumn(['pagado_total', 'saldo_pendiente']);
        });
    }
};
