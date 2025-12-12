<?php

// app/Policies/ExpensePolicy.php
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
        // Puede ver si tiene permiso y:
        // - es creador, o
        // - pertenece a finanzas|admin|superadmin
        return $user->can('gastos.ver') && (
            $expense->created_by === $user->id ||
            $user->hasAnyRole(['finanzas','administrador','superadmin'])
        );
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
        // Solo finanzas/admin/superadmin y si no está ya aprobado
        return $user->can('gastos.aprobar')
            && in_array($user->getRoleNames()->first(), ['finanzas','administrador','superadmin'])
            && $expense->status !== 'aprobado';
    }
}
