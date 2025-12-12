<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(){ Schema::create('cash_boxes', function(Blueprint $t){
    $t->id(); $t->string('nombre'); $t->string('ubicacion')->nullable(); $t->boolean('activo')->default(true); $t->timestamps();
  });}
  public function down(){ Schema::dropIfExists('cash_boxes'); }
};
