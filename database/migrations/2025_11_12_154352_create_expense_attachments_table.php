<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(){ Schema::create('expense_attachments', function(Blueprint $t){
    $t->id(); 
    $t->foreignId('expense_id')->constrained()->cascadeOnDelete();
    $t->string('path');           // s3 key
    $t->string('original_name');  // nombre original
    $t->string('mime',100)->nullable();
    $t->unsignedBigInteger('size')->default(0);
    $t->timestamps();
  });}
  public function down(){ Schema::dropIfExists('expense_attachments'); }
};

