<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(){ Schema::create('expenses', function(Blueprint $t){
    $t->id();
    $t->string('proveedor')->nullable();
    $t->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
    $t->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
    $t->decimal('monto',12,2);
    $t->date('fecha');
    $t->string('metodo_pago',30)->nullable();     // efectivo, transferencia, etc.
    $t->string('referencia',100)->nullable();     // folio, uuid, etc.
    $t->text('notas')->nullable();
    $t->enum('status',['borrador','aprobado','rechazado'])->default('borrador');
    $t->foreignId('created_by')->constrained('users');
    $t->foreignId('approved_by')->nullable()->constrained('users');
    $t->timestamps();
    $t->index(['fecha','status']);
  });}
  public function down(){ Schema::dropIfExists('expenses'); }
};
