<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Cliente;
use App\Models\EventOrder;
use App\Models\Item;
use App\Models\PettyCashSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Models\BundleItem;
use App\Models\EventOrderItemLoan;

class EventOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');       // borrador, cotizacion, confirmada, etc.
        $from   = $request->get('from');         // fecha_inicio >=
        $to     = $request->get('to');           // fecha_inicio <=

        // Parámetros de paginado
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        // límites razonables
        if ($perPage < 5)  { $perPage = 5; }
        if ($perPage > 50) { $perPage = 50; }

        $query = EventOrder::query()
            ->with('cliente:id,nombre')          // asumiendo relación cliente()
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhere('cliente_nombre', 'like', "%{$search}%")
                  ->orWhere('cliente_email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('estatus', $status);
        }

        if ($from) {
            $query->whereDate('fecha_inicio', '>=', $from);
        }

        if ($to) {
            $query->whereDate('fecha_inicio', '<=', $to);
        }

        $paginator = $query->paginate(
            $perPage,
            [
                'id',
                'folio',
                'cliente_id',
                'cliente_nombre',
                'fecha_inicio',
                'fecha_entrega',
                'fecha_recoleccion',
                'estatus',
                'total',
                'pagado_total',
                'saldo_pendiente',
            ],
            'page',
            $page
        );

        // Colección de la página actual
        $collection = $paginator->getCollection();

        $sumTotalPage = (float) $collection->sum('total');
        $sumSaldoPage = (float) $collection->sum('saldo_pendiente');

        return response()->json([
            'data' => $collection->values(),   // órdenes
            'meta' => [
                'current_page'    => $paginator->currentPage(),
                'last_page'       => $paginator->lastPage(),
                'per_page'        => $paginator->perPage(),
                'total'           => $paginator->total(),
                'from'            => $paginator->firstItem(),
                'to'              => $paginator->lastItem(),
                'sum_total_page'  => $sumTotalPage,
                'sum_saldo_page'  => $sumSaldoPage,
            ],
        ]);
    }

    public function show(EventOrder $eventOrder)
    {
        $eventOrder->load([
            'cliente:id,nombre,email,telefono',
            'lineas.item:id,sku,nombre',
            'lineas.bundle:id,sku,nombre',
            'pagos' => function ($q) {
                $q->orderBy('pagado_en', 'asc');
            },
            'itemLoans.item:id,sku,nombre', // 👈 nuevo
        ]);

        return $eventOrder;
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // 🛑 0) Bloquear si el usuario no tiene caja chica abierta
        $caja = $this->getCajaAbiertaForUser($user->id);
        if (!$caja) {
            return response()->json([
                'ok'      => false,
                'code'    => 'CAJA_CERRADA',
                'message' => 'No puedes crear órdenes porque no tienes caja chica abierta.',
            ], 409);
        }

        // 1) Validación básica del payload
        $data = $request->validate([
            'cliente_id'        => 'nullable|exists:clientes,id',
            'cliente_nombre'    => 'nullable|string|max:255',
            'cliente_email'     => 'nullable|email|max:255',
            'cliente_telefono'  => 'nullable|string|max:50',

            'contacto_nombre'   => 'nullable|string|max:255',
            'contacto_telefono' => 'nullable|string|max:50',

            'lugar'             => 'nullable|string|max:255',
            'direccion'         => 'nullable|string|max:255',

            'fecha_entrega'     => 'required|date',
            'fecha_inicio'      => 'required|date',
            'fecha_recoleccion' => 'required|date|after_or_equal:fecha_inicio',

            'notas'             => 'nullable|string',

            'estatus'           => 'nullable|in:' .
                implode(',', array_keys(EventOrder::estatusDisponibles())),

            // Líneas
            'lineas'                       => 'required|array|min:1',
            'lineas.*.tipo'                => 'required|string|in:item,bundle,extra',
            'lineas.*.item_id'             => 'nullable|integer',
            'lineas.*.bundle_id'           => 'nullable|integer',
            'lineas.*.descripcion'         => 'nullable|string|max:255',
            'lineas.*.cantidad'            => 'required|integer|min:1',
            'lineas.*.precio_unitario'     => 'nullable|numeric|min:0',
            'lineas.*.impuesto_porcentaje' => 'nullable|numeric|min:0',
        ]);

        // 2) Transacción para asegurar consistencia
        $order = DB::transaction(function () use ($data) {

            // 2.1) Cliente (si viene cliente_id lo usamos, si no, usamos los campos planos)
            $cliente = null;
            if (!empty($data['cliente_id'])) {
                $cliente = Cliente::find($data['cliente_id']);
            }

            $folio = $this->generarFolio();

            /** @var EventOrder $order */
            $order = EventOrder::create([
                'folio'             => $folio,
                'cliente_id'        => $cliente?->id,
                'cliente_nombre'    => $cliente->nombre   ?? $data['cliente_nombre']   ?? null,
                'cliente_email'     => $cliente->email    ?? $data['cliente_email']    ?? null,
                'cliente_telefono'  => $cliente->telefono ?? $data['cliente_telefono'] ?? null,

                'contacto_nombre'   => $data['contacto_nombre']   ?? null,
                'contacto_telefono' => $data['contacto_telefono'] ?? null,

                'lugar'             => $data['lugar']      ?? null,
                'direccion'         => $data['direccion']  ?? null,

                'fecha_entrega'     => $data['fecha_entrega'],
                'fecha_inicio'      => $data['fecha_inicio'],
                'fecha_recoleccion' => $data['fecha_recoleccion'],

                'notas'             => $data['notas'] ?? null,
                'estatus'           => $data['estatus'] ?? EventOrder::STATUS_COTIZACION,

                'subtotal'          => 0,
                'impuestos'         => 0,
                'total'             => 0,
                'pagado_total'      => 0,
                'saldo_pendiente'   => 0,
            ]);

            // 2.2) Crear líneas
            foreach ($data['lineas'] as $line) {
                $tipo       = $line['tipo'];
                $cantidad   = (int) $line['cantidad'];
                $impuesto   = $line['impuesto_porcentaje'] ?? 0;
                $precioUnit = $line['precio_unitario'] ?? null;

                $item      = null;
                $bundle    = null;
                $descFinal = $line['descripcion'] ?? null;

                if ($tipo === 'item') {
                    $itemId = $line['item_id'] ?? null;
                    if (!$itemId) {
                        throw ValidationException::withMessages([
                            'lineas' => ['Cada línea tipo item debe traer item_id.'],
                        ]);
                    }
                    $item = Item::findOrFail($itemId);

                    if ($precioUnit === null) {
                        $precioUnit = $item->precio_renta_dia ?? 0;
                    }
                    $descFinal = $descFinal ?: $item->nombre;

                } elseif ($tipo === 'bundle') {
                    $bundleId = $line['bundle_id'] ?? null;
                    if (!$bundleId) {
                        throw ValidationException::withMessages([
                            'lineas' => ['Cada línea tipo bundle debe traer bundle_id.'],
                        ]);
                    }
                    $bundle = Bundle::findOrFail($bundleId);

                    if ($precioUnit === null) {
                        $precioUnit = $bundle->precio_final;
                    }
                    $descFinal = $descFinal ?: $bundle->nombre;

                } else { // extra
                    if ($precioUnit === null) {
                        throw ValidationException::withMessages([
                            'lineas' => ['Cada línea tipo extra debe traer precio_unitario.'],
                        ]);
                    }
                    if (!$descFinal) {
                        $descFinal = 'Extra';
                    }
                }

                $order->lineas()->create([
                    'tipo'                => $tipo,
                    'item_id'             => $item?->id,
                    'bundle_id'           => $bundle?->id,
                    'descripcion'         => $descFinal,
                    'cantidad'            => $cantidad,
                    'precio_unitario'     => $precioUnit,
                    'impuesto_porcentaje' => $impuesto,
                ]);
            }

            // 2.3) Recalcular totales con el helper del modelo
            $order->recalcularTotales();

            // Inicializar saldo pendiente = total (aún sin pagos)
            $order->pagado_total    = 0;
            $order->saldo_pendiente = $order->total ?? 0;
            $order->save();

            return $order;
        });
        

        // 3) Respuesta JSON para el POS
        return response()->json([
            'ok'      => true,
            'message' => 'Orden de evento creada correctamente.',
            'data'    => [
                'id'              => $order->id,
                'folio'           => $order->folio,
                'subtotal'        => $order->subtotal,
                'impuestos'       => $order->impuestos,
                'total'           => $order->total,
                'estatus'         => $order->estatus,
                'pagado_total'    => $order->pagado_total,
                'saldo_pendiente' => $order->saldo_pendiente,
            ],
        ], 201);
    }

    public function addLines(Request $request, EventOrder $eventOrder)
{
    // 1) Validar que la orden permita edición de líneas
    if ($eventOrder->isLockedForItems()) {
        return response()->json([
            'ok'      => false,
            'message' => 'No puedes modificar los ítems porque la orden está en estatus '
                        . $eventOrder->estatus . '.',
        ], 422);
    }

    // 2) Validar payload (igual que en store)
    $data = $request->validate([
        'lineas'                       => 'required|array|min:1',
        'lineas.*.tipo'                => 'required|string|in:item,bundle,extra',
        'lineas.*.item_id'             => 'nullable|integer',
        'lineas.*.bundle_id'           => 'nullable|integer',
        'lineas.*.descripcion'         => 'nullable|string|max:255',
        'lineas.*.cantidad'            => 'required|integer|min:1',
        'lineas.*.precio_unitario'     => 'nullable|numeric|min:0',
        'lineas.*.impuesto_porcentaje' => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($data, $eventOrder) {
        foreach ($data['lineas'] as $line) {
            $tipo       = $line['tipo'];
            $cantidad   = (int) $line['cantidad'];
            $impuesto   = $line['impuesto_porcentaje'] ?? 0;
            $precioUnit = $line['precio_unitario'] ?? null;

            $item      = null;
            $bundle    = null;
            $descFinal = $line['descripcion'] ?? null;

            if ($tipo === 'item') {
                $itemId = $line['item_id'] ?? null;
                if (!$itemId) {
                    throw ValidationException::withMessages([
                        'lineas' => ['Cada nueva línea tipo item debe traer item_id.'],
                    ]);
                }
                $item = Item::findOrFail($itemId);

                if ($precioUnit === null) {
                    $precioUnit = $item->precio_renta_dia ?? 0;
                }
                $descFinal = $descFinal ?: $item->nombre;

            } elseif ($tipo === 'bundle') {
                $bundleId = $line['bundle_id'] ?? null;
                if (!$bundleId) {
                    throw ValidationException::withMessages([
                        'lineas' => ['Cada nueva línea tipo bundle debe traer bundle_id.'],
                    ]);
                }
                $bundle = Bundle::findOrFail($bundleId);

                if ($precioUnit === null) {
                    $precioUnit = $bundle->precio_final;
                }
                $descFinal = $descFinal ?: $bundle->nombre;

            } else { // extra
                if ($precioUnit === null) {
                    throw ValidationException::withMessages([
                        'lineas' => ['Cada nueva línea tipo extra debe traer precio_unitario.'],
                    ]);
                }
                if (!$descFinal) {
                    $descFinal = 'Extra';
                }
            }

            $eventOrder->lineas()->create([
                'tipo'                => $tipo,
                'item_id'             => $item?->id,
                'bundle_id'           => $bundle?->id,
                'descripcion'         => $descFinal,
                'cantidad'            => $cantidad,
                'precio_unitario'     => $precioUnit,
                'impuesto_porcentaje' => $impuesto,
            ]);
        }

        // Recalcular totales y saldo
        $eventOrder->recalcularTotales();
        $eventOrder->recalcularPagos();
    });

    // Volvemos a cargar líneas para regresarlas al POS
    $eventOrder->load([
        'lineas.item:id,sku,nombre',
        'lineas.bundle:id,sku,nombre',
    ]);

    return response()->json([
        'ok'      => true,
        'message' => 'Líneas agregadas correctamente.',
        'data'    => [
            'lineas'          => $eventOrder->lineas,
            'subtotal'        => $eventOrder->subtotal,
            'impuestos'       => $eventOrder->impuestos,
            'total'           => $eventOrder->total,
            'pagado_total'    => $eventOrder->pagado_total,
            'saldo_pendiente' => $eventOrder->saldo_pendiente,
        ],
    ]);
}
public function updateLines(Request $request, EventOrder $eventOrder)
{
    // 1) Validar que la orden permita edición de líneas
    if ($eventOrder->isLockedForItems()) {
        return response()->json([
            'ok'      => false,
            'message' => 'No puedes modificar los ítems porque la orden está en estatus '
                        . $eventOrder->estatus . '.',
        ], 422);
    }

    // 2) Validar payload (igual que en addLines)
    $data = $request->validate([
        'lineas'                       => 'required|array|min:1',
        'lineas.*.tipo'                => 'required|string|in:item,bundle,extra',
        'lineas.*.item_id'             => 'nullable|integer',
        'lineas.*.bundle_id'           => 'nullable|integer',
        'lineas.*.descripcion'         => 'nullable|string|max:255',
        'lineas.*.cantidad'            => 'required|integer|min:1',
        'lineas.*.precio_unitario'     => 'nullable|numeric|min:0',
        'lineas.*.impuesto_porcentaje' => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($data, $eventOrder) {
        // 🔥 BORRAMOS TODAS LAS LÍNEAS ACTUALES
        $eventOrder->lineas()->delete();

        // Y volvemos a crearlas con lo que viene del POS
        foreach ($data['lineas'] as $line) {
            $tipo       = $line['tipo'];
            $cantidad   = (int) $line['cantidad'];
            $impuesto   = $line['impuesto_porcentaje'] ?? 0;
            $precioUnit = $line['precio_unitario'] ?? null;

            $item      = null;
            $bundle    = null;
            $descFinal = $line['descripcion'] ?? null;

            if ($tipo === 'item') {
                $itemId = $line['item_id'] ?? null;
                if (!$itemId) {
                    throw ValidationException::withMessages([
                        'lineas' => ['Cada línea tipo item debe traer item_id.'],
                    ]);
                }
                $item = Item::findOrFail($itemId);

                if ($precioUnit === null) {
                    $precioUnit = $item->precio_renta_dia ?? 0;
                }
                $descFinal = $descFinal ?: $item->nombre;

            } elseif ($tipo === 'bundle') {
                $bundleId = $line['bundle_id'] ?? null;
                if (!$bundleId) {
                    throw ValidationException::withMessages([
                        'lineas' => ['Cada línea tipo bundle debe traer bundle_id.'],
                    ]);
                }
                $bundle = Bundle::findOrFail($bundleId);

                if ($precioUnit === null) {
                    $precioUnit = $bundle->precio_final;
                }
                $descFinal = $descFinal ?: $bundle->nombre;

            } else { // extra
                if ($precioUnit === null) {
                    throw ValidationException::withMessages([
                        'lineas' => ['Cada línea tipo extra debe traer precio_unitario.'],
                    ]);
                }
                if (!$descFinal) {
                    $descFinal = 'Extra';
                }
            }

            $eventOrder->lineas()->create([
                'tipo'                => $tipo,
                'item_id'             => $item?->id,
                'bundle_id'           => $bundle?->id,
                'descripcion'         => $descFinal,
                'cantidad'            => $cantidad,
                'precio_unitario'     => $precioUnit,
                'impuesto_porcentaje' => $impuesto,
            ]);
        }

        // Recalcular totales y saldo
        $eventOrder->recalcularTotales();
        $eventOrder->recalcularPagos();
    });

    // Volvemos a cargar líneas para regresarlas al POS
    $eventOrder->load([
        'lineas.item:id,sku,nombre',
        'lineas.bundle:id,sku,nombre',
    ]);

    return response()->json([
        'ok'      => true,
        'message' => 'Líneas actualizadas correctamente.',
        'data'    => [
            'lineas'          => $eventOrder->lineas,
            'subtotal'        => $eventOrder->subtotal,
            'impuestos'       => $eventOrder->impuestos,
            'total'           => $eventOrder->total,
            'pagado_total'    => $eventOrder->pagado_total,
            'saldo_pendiente' => $eventOrder->saldo_pendiente,
        ],
    ]);
}



    /**
     * Genera un folio sencillo tipo OE-2025-0001
     */
    protected function generarFolio(): string
    {
        $year = now()->format('Y');

        $last = EventOrder::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last->folio, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'OE-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Devuelve la caja chica abierta del usuario (petty_cash_sessions).
     */
    protected function getCajaAbiertaForUser(int $userId): ?PettyCashSession
    {
        return PettyCashSession::openForUser($userId)->first();
    }

    public function updateStatus(Request $request, EventOrder $eventOrder)
    {
        $validated = $request->validate([
            'estatus' => [
                'required',
                Rule::in([
                    'borrador',
                    'cotizacion',
                    'confirmada',
                    'preparacion',
                    'salida',
                    'regreso',
                    'cierre',
                ]),
            ],
        ]);

        $old = $eventOrder->estatus;
        $new = $validated['estatus'];

        /**
         * 🔒 Candado POS:
         * Si la orden YA está en cierre, no se permite cambiar el estatus
         * desde este endpoint (POS). Cualquier cambio posterior deberá
         * hacerse desde la vista de administración con otro flujo.
         */
        if ($old === EventOrder::STATUS_CIERRE) {
            return response()->json([
                'ok'      => false,
                'message' => 'Esta orden ya está en Cierre y no puede modificarse desde el POS. '
                            . 'Solicita a administración cualquier cambio de estatus.',
            ], 422);
        }

        // Reglas de transición normales (POS)
        $allowed = [
            'borrador'    => ['cotizacion'],
            'cotizacion'  => ['borrador', 'confirmada'],
            'confirmada'  => ['preparacion'],
            'preparacion' => ['salida'],
            'salida'      => ['regreso'],
            'regreso'     => ['cierre'],
            'cierre'      => [], // aunque aquí ya no debería entrar por el candado
        ];

        if (isset($allowed[$old]) && ! in_array($new, $allowed[$old], true)) {
            return response()->json([
                'ok'      => false,
                'message' => "No se puede pasar de '{$old}' a '{$new}'.",
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $eventOrder, $old, $new) {
                $user = $request->user();

                // PREPARACION -> SALIDA = prestar stock
                if ($old === EventOrder::STATUS_PREPARACION && $new === EventOrder::STATUS_SALIDA) {
                    $this->prestarStockDesdeOrden($eventOrder, $user?->id);
                }

                // SALIDA -> REGRESO = devolver stock completo
                if ($old === EventOrder::STATUS_SALIDA && $new === EventOrder::STATUS_REGRESO) {
                    $this->registrarDevolucionCompleta($eventOrder, $user?->id);
                }

                // REGRESO -> CIERRE = generar cargos por faltantes
                if ($old === EventOrder::STATUS_REGRESO && $new === EventOrder::STATUS_CIERRE) {
                    $this->generarCargosPorFaltantes($eventOrder, $user?->id);
                }

                $eventOrder->estatus = $new;
                $eventOrder->save();
            });

        } catch (ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Estatus actualizado correctamente.',
            'data'    => [
                'id'      => $eventOrder->id,
                'estatus' => $eventOrder->estatus,
            ],
        ]);
    }

    // 🔽🔽🔽  Helpers de stock / préstamos / devoluciones / cargos  🔽🔽🔽

    protected function prestarStockDesdeOrden(EventOrder $order, ?int $userId = null): void
    {
        if ($order->itemLoans()->exists()) {
            throw ValidationException::withMessages([
                'estatus' => ['Esta orden ya tiene stock prestado. No puedes volver a marcar SALIDA.'],
            ]);
        }

        $order->loadMissing([
            'lineas.item',
            'lineas.bundle.lineas.item',
        ]);

        $itemsNecesarios = [];

        foreach ($order->lineas as $line) {
            if ($line->tipo === 'item' && $line->item_id && $line->item) {
                $itemsNecesarios[$line->item_id] = ($itemsNecesarios[$line->item_id] ?? 0)
                    + (int) $line->cantidad;
            } elseif ($line->tipo === 'bundle' && $line->bundle) {
                $bundle = $line->bundle->loadMissing('lineas.item');

                foreach ($bundle->lineas as $bLine) {
                    if (!$bLine->item_id || !$bLine->item) {
                        continue;
                    }

                    $total = (int) $line->cantidad * (int) $bLine->cantidad;

                    $itemsNecesarios[$bLine->item_id] = ($itemsNecesarios[$bLine->item_id] ?? 0)
                        + $total;
                }
            }
        }

        if (empty($itemsNecesarios)) {
            return;
        }

        $items = Item::whereIn('id', array_keys($itemsNecesarios))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $errores = [];

        foreach ($itemsNecesarios as $itemId => $cantidad) {
            /** @var Item|null $item */
            $item = $items->get($itemId);
            if (!$item) {
                continue;
            }

            if ($item->stock_fisico < $cantidad) {
                $errores[] = "{$item->sku} ({$item->nombre}) requiere {$cantidad} y solo hay {$item->stock_fisico}";
            }
        }

        if ($errores) {
            throw ValidationException::withMessages([
                'stock' => ['No hay stock suficiente para: ' . implode(' · ', $errores)],
            ]);
        }

        foreach ($itemsNecesarios as $itemId => $cantidad) {
            /** @var Item $item */
            $item = $items->get($itemId);
            if (!$item) {
                continue;
            }

            EventOrderItemLoan::create([
                'event_order_id'    => $order->id,
                'item_id'           => $itemId,
                'cantidad_prestada' => $cantidad,
                'cantidad_devuelta' => 0,
                'created_by'        => $userId,
            ]);

            $item->stock_fisico = $item->stock_fisico - $cantidad;
            $item->save();
        }
    }

    protected function registrarDevolucionCompleta(EventOrder $order, ?int $userId = null): void
    {
        $order->loadMissing([
            'itemLoans.item',
        ]);

        if ($order->itemLoans->isEmpty()) {
            return;
        }

        $itemIds = $order->itemLoans->pluck('item_id')->unique()->all();

        $items = Item::whereIn('id', $itemIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->itemLoans as $loan) {
            /** @var \App\Models\Item|null $item */
            $item = $items->get($loan->item_id);
            if (!$item) {
                continue;
            }

            $yaDevuelto = (int) $loan->cantidad_devuelta;
            $prestado   = (int) $loan->cantidad_prestada;

            if ($yaDevuelto >= $prestado) {
                continue;
            }

            $aDevolver = $prestado - $yaDevuelto;

            $item->stock_fisico = $item->stock_fisico + $aDevolver;
            $item->save();

            $loan->cantidad_devuelta = $prestado;
            $loan->save();
        }
    }

    protected function generarCargosPorFaltantes(EventOrder $order, ?int $userId = null): void
    {
        $order->loadMissing([
            'itemLoans.item',
            'lineas',
        ]);

        if ($order->itemLoans->isEmpty()) {
            return;
        }

        foreach ($order->itemLoans as $loan) {
            if (!$loan->item) {
                continue;
            }

            $prestada = (int) $loan->cantidad_prestada;
            $devuelta = (int) $loan->cantidad_devuelta;
            $faltante = $prestada - $devuelta;

            if ($faltante <= 0) {
                continue;
            }

            $item = $loan->item;

            $precioReposicion = $item->costo_reposicion ?? $item->precio_renta_dia ?? 0;

            $descripcion = sprintf(
                'Reposición: %s - %s (faltan %d)',
                $item->sku,
                $item->nombre,
                $faltante
            );

            $order->lineas()->create([
                'tipo'                => 'extra',
                'item_id'             => null,
                'bundle_id'           => null,
                'descripcion'         => $descripcion,
                'cantidad'            => $faltante,
                'precio_unitario'     => $precioReposicion,
                'impuesto_porcentaje' => 0,
            ]);
        }

        $order->recalcularTotales();
        $order->recalcularPagos();
    }

    public function updateLoans(Request $request, EventOrder $eventOrder)
    {
        $data = $request->validate([
            'loans'                     => 'required|array|min:1',
            'loans.*.id'                => 'required|integer|exists:event_order_item_loans,id',
            'loans.*.cantidad_devuelta' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($data, $eventOrder) {
            $loans = $eventOrder->itemLoans()
                ->whereIn('id', collect($data['loans'])->pluck('id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $itemIds = $loans->pluck('item_id')->unique()->all();

            $items = Item::whereIn('id', $itemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($data['loans'] as $row) {
                /** @var \App\Models\EventOrderItemLoan|null $loan */
                $loan = $loans->get($row['id']);
                if (!$loan) {
                    continue;
                }

                $nuevoDevuelto = (int) $row['cantidad_devuelta'];
                $prestado      = (int) $loan->cantidad_prestada;

                if ($nuevoDevuelto > $prestado) {
                    throw ValidationException::withMessages([
                        'loans' => ["La devolución no puede ser mayor a la cantidad prestada ({$prestado})."],
                    ]);
                }

                $actualDevuelto = (int) $loan->cantidad_devuelta;
                $delta          = $nuevoDevuelto - $actualDevuelto;

                $item = $items->get($loan->item_id);
                if ($item && $delta !== 0) {
                    $item->stock_fisico = $item->stock_fisico + $delta;
                    $item->save();
                }

                $loan->cantidad_devuelta = $nuevoDevuelto;
                $loan->save();
            }
        });

        $eventOrder->load('itemLoans.item');

        return response()->json([
            'ok'      => true,
            'message' => 'Devoluciones actualizadas correctamente.',
            'data'    => $eventOrder->itemLoans,
        ]);
    }
}
