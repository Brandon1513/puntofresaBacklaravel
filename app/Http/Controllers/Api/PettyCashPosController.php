<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PettyCashSession;
use App\Models\PettyCashMovement;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\CostCenter;
use App\Models\User;
use App\Notifications\ExpenseSubmittedNotification;
use Illuminate\Http\Request;

class PettyCashPosController extends Controller
{
    /**
     * Devuelve la sesión de caja abierta para el usuario autenticado.
     */
    public function currentSession(Request $request)
{
    $user = $request->user();

    $session = PettyCashSession::openForUser($user->id)
        ->latest('id')
        ->first();

    if (!$session) {
        return response()->json([
            'has_session' => false,
            'message'     => 'No tienes una caja chica abierta. Solicita la apertura a administración.',
        ], 404);
    }

    return response()->json([
        'has_session' => true,
        'session'     => [
            'id'             => $session->id,
            'fecha'          => optional($session->fecha)->format('Y-m-d'),
            'responsable_id' => $session->responsable_id,
            'status'         => $session->status,
            'saldo_inicial'  => (float) $session->saldo_inicial,
            'saldo_actual'   => (float) $session->saldo_actual,      // ya con nueva lógica
            'total_ingresos' => (float) $session->total_ingresos,    // 👈 nuevo
            'total_gastos'   => (float) $session->total_egresos,     // 👈 nuevo (egresos aprobados)
        ],
    ]);
}

    /**
     * Lista de movimientos de la sesión abierta (últimos 50).
     */
    public function indexMovements(Request $request)
    {
        $user = $request->user();

        $session = PettyCashSession::openForUser($user->id)
            ->latest('id')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'No tienes una caja chica abierta.',
            ], 404);
        }

        $movs = $session->movimientos()
            ->with('category', 'expense')
            ->latest('id')
            ->take(50)
            ->get()
            ->map(function (PettyCashMovement $m) {
                return [
                    'id'          => $m->id,
                    'tipo'        => $m->tipo,          // ingreso | egreso
                    'monto'       => (float) $m->monto,
                    'concepto'    => $m->concepto,
                    'notas'       => $m->notas,
                    'categoria'   => $m->category ? $m->category->nombre : null,
                    'status'      => $m->expense?->status,
                    'created_at'  => $m->created_at?->toDateTimeString(),
                    'created_by'  => $m->created_by,
                    'receipt_url' => $m->receipt_url,
                ];
            });

        return response()->json([
            'session_id'   => $session->id,
            'saldo_actual' => (float) $session->saldo_actual,
            'movements'    => $movs,
        ]);
    }

    /**
     * Registrar un nuevo movimiento desde el POS.
     */
    public function storeMovement(Request $request)
    {
        $user = $request->user();

        $session = PettyCashSession::openForUser($user->id)
            ->latest('id')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'No tienes una caja chica abierta.',
            ], 422);
        }

        // 🔹 Lo que viene del POS
        $validated = $request->validate([
            'monto'               => 'required|numeric|min:0.01',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'cost_center_id'      => 'nullable|exists:cost_centers,id',
            'concepto'            => 'required|string|max:180', // proveedor / descripción corta
            'notas'               => 'nullable|string',
            'receipt'             => 'nullable|file|max:5120',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1) Crear Expense para el módulo de admin
        |--------------------------------------------------------------------------
        */

        $expenseData = [
            'proveedor'           => $validated['concepto'],                   // concepto POS = proveedor
            'expense_category_id' => $validated['expense_category_id'] ?? null,
            'cost_center_id'      => $validated['cost_center_id'] ?? null,     // centro de costo del POS
            'monto'               => $validated['monto'],
            'fecha'               => now()->toDateString(),
            'metodo_pago'         => 'Caja chica',
            'referencia'          => 'Caja #'.$session->id,
            'notas'               => $validated['notas'] ?? null,              // notas del POS
            'created_by'          => $user->id,
            // status: usa el default de la BD (borrador)
        ];

        $expense = Expense::create($expenseData);

        /*
        |--------------------------------------------------------------------------
        | 2) Manejar comprobante (una sola vez) y ligarlo a caja + gasto
        |--------------------------------------------------------------------------
        */

        $receiptPath      = null;
        $receiptOriginal  = null;
        $receiptMime      = null;
        $receiptSize      = null;

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');

            // Guardamos el archivo en public, carpeta de caja chica
            $receiptPath = $file->store("petty_cash/{$session->id}", 'public');
            $receiptOriginal = $file->getClientOriginalName();
            $receiptMime     = $file->getClientMimeType();
            $receiptSize     = $file->getSize();

            // Creamos también el attachment para el Expense,
            // así aparecerá en la vista de detalle de Laravel.
            $expense->attachments()->create([
                'path'          => $receiptPath,
                'original_name' => $receiptOriginal,
                'mime'          => $receiptMime,
                'size'          => $receiptSize,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Crear movimiento de caja chica
        |--------------------------------------------------------------------------
        */

        $movement = PettyCashMovement::create([
            'petty_cash_session_id' => $session->id,
            'tipo'                  => 'egreso',
            'monto'                 => $validated['monto'],
            'expense_id'            => $expense->id,                           // ligamos gasto
            'expense_category_id'   => $validated['expense_category_id'] ?? null,
            'concepto'              => $validated['concepto'],
            'notas'                 => $validated['notas'] ?? null,
            'created_by'            => $user->id,
            'receipt_path'          => $receiptPath,
            'receipt_original_name' => $receiptOriginal,
        ]);

        // Actualizar saldo_actual guardado (opcional)
        $session->update([
            'saldo_actual' => $session->saldo_actual,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4) Notificar a superadmin / admin / finanzas
        |--------------------------------------------------------------------------
        */

        $notifiables = User::role(['superadmin', 'administrador', 'finanzas'])
            ->where('activo', 1)
            ->whereNotNull('email')
            ->get();

        foreach ($notifiables as $u) {
            $u->notify(new ExpenseSubmittedNotification($expense));
        }

        /*
        |--------------------------------------------------------------------------
        | 5) Respuesta al POS
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'ok'          => true,
            'message'     => 'Gasto de caja chica registrado.',
            'movement'    => [
                'id'         => $movement->id,
                'tipo'       => $movement->tipo,
                'monto'      => (float) $movement->monto,
                'concepto'   => $movement->concepto,
                'notas'      => $movement->notas,
                'categoria'  => $movement->category?->nombre,
                'created_at' => $movement->created_at?->toDateTimeString(),
            ],
            'saldo_actual' => (float) $session->saldo_actual,
        ], 201);
    }

    /**
     * Categorías de gasto activas (para llenar el select en React POS).
     */
    public function categories()
    {
        $cats = ExpenseCategory::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($cats);
    }

    /**
     * Centros de costo activos (para el POS).
     */
    public function costCenters()
    {
        $ccs = CostCenter::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($ccs);
    }
}
