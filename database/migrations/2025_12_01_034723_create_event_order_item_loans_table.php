<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_order_item_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_order_id')->constrained('event_orders');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedInteger('cantidad_prestada');
            $table->unsignedInteger('cantidad_devuelta')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_order_item_loans');
    }
};
