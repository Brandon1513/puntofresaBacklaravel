<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() {
    foreach (['superadmin','administrador','finanzas','ventas'] as $r) {
        Role::firstOrCreate(['name'=>$r,'guard_name'=>'web']);
    }
    $admin = User::firstOrCreate(
        ['email'=>'admin@demo.com'],
        ['name'=>'Admin Demo','password'=>bcrypt('secret123')]
    );
    $admin->assignRole('superadmin');
}

}
