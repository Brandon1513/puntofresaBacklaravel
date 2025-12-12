<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(){ Schema::create('expense_categories', function(Blueprint $t){
    $t->id(); $t->string('nombre'); $t->boolean('activo')->default(true); $t->timestamps();
  });}
  public function down(){ Schema::dropIfExists('expense_categories'); }
};
