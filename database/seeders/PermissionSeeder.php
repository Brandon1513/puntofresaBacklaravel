<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'gastos.ver',
            'gastos.crear',
            'gastos.editar',
            'gastos.eliminar',
            'gastos.aprobar',
            'caja.ver','caja.abrir','caja.movimientos','caja.cerrar',
        ];
        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        $super  = Role::firstOrCreate(['name' => 'superadmin']);
        $admin  = Role::firstOrCreate(['name' => 'administrador']);
        $fin    = Role::firstOrCreate(['name' => 'finanzas']);
        $ventas = Role::firstOrCreate(['name' => 'ventas']);

        $managerPerms = [
            'gastos.ver','gastos.crear','gastos.editar','gastos.eliminar','gastos.aprobar',
            'caja.ver','caja.abrir','caja.movimientos','caja.cerrar',
        ];

        $super->syncPermissions(Permission::all());
        $admin->syncPermissions($managerPerms);
        $fin->syncPermissions($managerPerms);

        $ventas->syncPermissions([
            'gastos.ver','gastos.crear','gastos.editar','gastos.eliminar',
            'caja.ver','caja.movimientos',
        ]);
    }
}
