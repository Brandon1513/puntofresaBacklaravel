<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseLookupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(){
  \App\Models\ExpenseCategory::insert([
    ['nombre'=>'Combustible'],['nombre'=>'Mantenimiento'],['nombre'=>'Papelería'],
  ]);
  \App\Models\CostCenter::insert([
    ['nombre'=>'Operación'],['nombre'=>'Ventas'],['nombre'=>'Administración'],
  ]);
}

}
