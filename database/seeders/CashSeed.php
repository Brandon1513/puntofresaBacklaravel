<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(){
  \App\Models\CashBox::firstOrCreate(['nombre'=>'Caja Principal']);
  \App\Models\CashCategory::insert([
    ['nombre'=>'Fondo inicial','tipo'=>'ingreso'],
    ['nombre'=>'Deposito banco','tipo'=>'egreso'],
    ['nombre'=>'Gasto menor','tipo'=>'egreso'],
  ]);
}

}
