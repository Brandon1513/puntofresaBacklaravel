<?php

namespace App\Http\Controllers;

use App\Models\PettyCashSession;
use App\Models\PettyCashMovement;
use Illuminate\Http\Request;


class PettyCashMovementController extends Controller
{
   public function store(Request $request, PettyCashSession $session)
{
    // 1) No permitir movimientos si la caja está cerrada
    if ($session->status !== 'abierta') {
        return back()->withErrors([
            'session' => 'La caja ya está cerrada, no se pueden registrar movimientos.',
        ]);
    }

    // 2) Validación
    $data = $request->validate([
        'tipo'                => ['required', 'in:ingreso,egreso'],
        'monto'               => ['required', 'numeric', 'min:0.01'],
        'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
        'concepto'            => ['nullable', 'string', 'max:255'],
        'notas'               => ['nullable', 'string'],
        'comprobante'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
    ]);

    // 3) Validación extra: categoría obligatoria para egreso
    if ($data['tipo'] === 'egreso' && empty($data['expense_category_id'])) {
        return back()
            ->withErrors(['expense_category_id' => 'La categoría es obligatoria para egresos.'])
            ->withInput();
    }

    // Si es ingreso, nunca guardamos categoría
    if ($data['tipo'] === 'ingreso') {
        $data['expense_category_id'] = null;
    }

    // 4) Manejo de comprobante (solo egresos)
    $receiptPath = null;
    $receiptName = null;

    if ($data['tipo'] === 'egreso' && $request->hasFile('comprobante')) {
        $file = $request->file('comprobante');

        // Se guarda en storage/app/private/petty_cash_receipts
        $receiptPath = $file->store('petty_cash_receipts', 'private');
        $receiptName = $file->getClientOriginalName();
    }

    // 5) Crear movimiento
    PettyCashMovement::create([
        'petty_cash_session_id'   => $session->id,
        'tipo'                    => $data['tipo'],
        'monto'                   => $data['monto'],
        'expense_id'              => null, // luego lo ligamos a Expense si aplica
        'expense_category_id'     => $data['expense_category_id'] ?? null,
        'concepto'                => $data['concepto'] ?? null,
        'notas'                   => $data['notas'] ?? null,
        'created_by'              => auth()->id(),
        'receipt_path'            => $receiptPath,
        'receipt_original_name'   => $receiptName,
    ]);

    // OJO: ya NO actualizamos $session->saldo_actual aquí
    // porque tu modelo calcula el saldo con getSaldoActualAttribute()

    // 6) Regresar al detalle de la sesión
    return redirect()
        ->route('petty-cash-sessions.show', $session)
        ->with('ok', 'Movimiento registrado correctamente.');
}


public function showReceipt(PettyCashMovement $movement)
{
    if (! $movement->receipt_path) {
        abort(404);
    }

    $path = storage_path('app/private/'.$movement->receipt_path);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
}

}
