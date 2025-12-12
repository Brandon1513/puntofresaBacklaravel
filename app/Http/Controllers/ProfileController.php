<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gastos.ver');
    }

    public function view(User $user, Expense $expense): bool
    {
        // puede ver si tiene permiso general o es el creador
        return $user->can('gastos.ver') || $user->id === $expense->created_by;
    }

    public function create(User $user): bool
    {
        return $user->can('gastos.crear');
    }

    public function update(User $user, Expense $expense): bool
    {
        // Si NO está en borrador…
        if ($expense->status !== 'borrador') {
            // solo roles "fuertes" pueden editar (por si hay correcciones)
            return $user->hasRole(['superadmin','administrador','finanzas']);
        }

        // En borrador: creador o roles fuertes
        return $user->id === $expense->created_by
            || $user->hasRole(['superadmin','administrador','finanzas']);
    }

    public function delete(User $user, Expense $expense): bool
    {
        // Si ya está validado, nadie de ventas puede borrarlo.
        if ($expense->status !== 'borrador') {
            // si quieres, puedes permitir solo superadmin/administrador
            return $user->hasRole(['superadmin','administrador']);
        }

        // En borrador: creador o roles fuertes
        return $user->id === $expense->created_by
            || $user->hasRole(['superadmin','administrador','finanzas']);
    }

    public function approve(User $user, Expense $expense): bool
    {
        // solo finanzas/admin/superadmin pueden aprobar
        return $user->hasRole(['superadmin','administrador','finanzas'])
            && $expense->status === 'borrador';
    }
}
