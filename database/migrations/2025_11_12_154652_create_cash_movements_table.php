<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(){ Schema::create('cash_movements', function(Blueprint $t){
    $t->id();
    $t->foreignId('cash_session_id')->constrained()->cascadeOnDelete();
    $t->enum('tipo',['ingreso','egreso']);
    $t->foreignId('categoria_id')->nullable()->constrained('cash_categories')->nullOnDelete();
    $t->decimal('monto',12,2);
    $t->string('metodo',30)->nullable();      // efectivo, transferencia
    $t->string('referencia',100)->nullable(); // folio
    $t->text('notas')->nullable();
    $t->foreignId('user_id')->constrained('users');
    $t->dateTime('fecha');
    $t->timestamps();
  });}
  public function down(){ Schema::dropIfExists('cash_movements'); }
};
