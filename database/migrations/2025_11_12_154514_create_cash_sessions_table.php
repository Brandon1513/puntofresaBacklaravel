<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(){ Schema::create('cash_sessions', function(Blueprint $t){
    $t->id();
    $t->foreignId('cash_box_id')->constrained()->cascadeOnDelete();
    $t->foreignId('opened_by')->constrained('users');
    $t->foreignId('closed_by')->nullable()->constrained('users');
    $t->decimal('saldo_inicial',12,2)->default(0);
    $t->decimal('saldo_teorico_cierre',12,2)->default(0);
    $t->decimal('saldo_real_cierre',12,2)->default(0);
    $t->decimal('diferencia',12,2)->default(0);
    $t->timestamp('opened_at'); $t->timestamp('closed_at')->nullable();
    $t->enum('status',['abierta','cerrada'])->default('abierta');
    $t->timestamps();
    $t->index(['cash_box_id','status']);
  });}
  public function down(){ Schema::dropIfExists('cash_sessions'); }
};

