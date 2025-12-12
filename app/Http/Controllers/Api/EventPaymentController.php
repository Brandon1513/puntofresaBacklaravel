<?php

// app/Http/Controllers/Api/EventPaymentController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventOrder;
use App\Models\EventPayment;
use App\Models\PettyCashSession;
use App\Models\PettyCashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventPaymentController extends Controller
{
    /**
     * Registra un pago (anticipo o saldo) para una orden de evento.
     * También crea el movimiento correspondiente en petty_cash_movements.
     */
    public function store(Request $request, EventOrder $eventOrder)
{
    $user = $request->user();

    $caja = $this->getCajaAbiertaForUser($user);
    if (!$caja) {
        return response()->json([
            'ok'      => false,
            'code'    => 'CAJA_CERRADA',
            'message' => 'No puedes registrar pagos porque no tienes caja chica abierta.',
        ], 409);
    }

    $data = $request->validate([
        'metodo'     => 'required|in:efectivo,transferencia,tarjeta',
        'monto'      => 'required|numeric|min:0.01',
        'referencia' => 'nullable|string|max:255',
    ]);

    $eventOrder->refresh();
    $saldoActual = $eventOrder->saldo_pendiente
        ?? max(0, ($eventOrder->total ?? 0) - ($eventOrder->pagado_total ?? 0));

    if ($saldoActual <= 0) {
        return response()->json([
            'ok'      => false,
            'code'    => 'SIN_SALDO',
            'message' => 'Esta orden ya está pagada.',
        ], 422);
    }

    $monto = min($data['monto'], $saldoActual);
    $pago  = null;

    DB::transaction(function () use ($user, $caja, $eventOrder, $data, $monto, &$pago) {

        $ref = $data['referencia'] ?? null;

        // 4.1 Registrar pago de la orden
        $pago = EventPayment::create([
            'event_order_id'        => $eventOrder->id,
            'user_id'               => $user->id,
            'petty_cash_session_id' => $caja->id,
            'metodo'                => $data['metodo'],
            'monto'                 => $monto,
            'referencia'            => $ref,
            'pagado_en'             => now(),
        ]);

        // 4.2 Movimiento en caja chica (INGRESO)
        PettyCashMovement::create([
            'petty_cash_session_id' => $caja->id,
            'tipo'                  => 'ingreso',
            'monto'                 => $monto,
            'expense_id'            => null,
            'expense_category_id'   => null,
            'concepto'              => 'Pago orden ' . $eventOrder->folio,
            'notas'                 => 'Método: ' . $data['metodo']
                . ($ref ? ' · Ref: ' . $ref : ''),
            'created_by'            => $user->id,
        ]);

        $eventOrder->recalcularPagos();
    });

    $eventOrder->refresh();

    $pagoArray = $pago ? $pago->toArray() : null;
    if ($pagoArray) {
        $pagoArray['cash_session_id'] = $pagoArray['petty_cash_session_id'] ?? null;
    }

    return response()->json([
        'ok'      => true,
        'message' => 'Pago registrado correctamente.',
        'data'    => [
            'order_id'        => $eventOrder->id,
            'folio'           => $eventOrder->folio,
            'pagado_total'    => $eventOrder->pagado_total,
            'saldo_pendiente' => $eventOrder->saldo_pendiente,
            'pago'            => $pagoArray,
        ],
    ]);
}


    /**
     * Devuelve la caja chica abierta del usuario (petty_cash_sessions).
     * Usamos el mismo scope que en EventOrderController.
     */
    protected function getCajaAbiertaForUser($user): ?PettyCashSession
    {
        return PettyCashSession::openForUser($user->id)->first();
    }
}
