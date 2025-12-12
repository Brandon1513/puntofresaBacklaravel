<?php

namespace App\Http\Controllers;

use App\Models\EventOrder;
use App\Models\EventOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventOrderController extends Controller
{
    /**
     * Lista de órdenes de evento (backoffice).
     */
    public function index(Request $request)
    {
        $filters = $request->only('search', 'estatus');

        $query = EventOrder::query()
            ->with('cliente')
            ->withCount('lineas');

        // Buscar por folio / cliente
        if (!empty($filters['search'])) {
            $s = $filters['search'];

            $query->where(function ($q) use ($s) {
                $q->where('folio', 'like', "%{$s}%")
                  ->orWhere('cliente_nombre', 'like', "%{$s}%")
                  ->orWhere('cliente_email', 'like', "%{$s}%");
            });
        }

        // Filtrar por estatus
        if (!empty($filters['estatus'])) {
            $query->where('estatus', $filters['estatus']);
        }

        // Más recientes primero
        $orders = $query
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $estatusDisponibles = EventOrder::estatusDisponibles();

        return view('event_orders.index', compact(
            'orders',
            'filters',
            'estatusDisponibles'
        ));
    }

    /**
     * Detalle de una orden.
     */
    public function show(EventOrder $eventOrder)
    {
        // Cargamos cliente + líneas + ítem / bundle de cada línea
        $eventOrder->load([
            'cliente',
            'lineas.item',
            'lineas.bundle',
        ]);

        return view('event_orders.show', [
            'order' => $eventOrder,
        ]);
    }

    /**
     * Formulario de edición (solo administración).
     */
    public function edit(EventOrder $eventOrder)
    {
        // También cargamos líneas para el detalle editable
        $eventOrder->load([
            'cliente',
            'lineas.item',
            'lineas.bundle',
        ]);

        $estatusDisponibles = EventOrder::estatusDisponibles();

        return view('event_orders.edit', [
            'order'              => $eventOrder,
            'estatusDisponibles' => $estatusDisponibles,
        ]);
    }

    /**
     * Actualizar la orden desde backoffice.
     */
    public function update(Request $request, EventOrder $eventOrder)
    {
        // 1) Validar datos generales de la orden
        $validated = $request->validate([
            'cliente_nombre'    => ['required', 'string', 'max:255'],
            'cliente_email'     => ['nullable', 'email', 'max:255'],
            'cliente_telefono'  => ['nullable', 'string', 'max:50'],
            'lugar'             => ['nullable', 'string', 'max:255'],
            'direccion'         => ['nullable', 'string', 'max:500'],
            'fecha_inicio'      => ['required', 'date'],
            'fecha_entrega'     => ['nullable', 'date'],
            'fecha_recoleccion' => ['nullable', 'date'],
            'notas'             => ['nullable', 'string'],
            'estatus'           => [
                'required',
                Rule::in(array_keys(EventOrder::estatusDisponibles())),
            ],
        ]);

        // 2) Traemos líneas del request (puede venir vacío)
        $linesData = $request->input('lines', []);

        DB::transaction(function () use ($eventOrder, $validated, $linesData) {

            // 2.1) Actualizar datos generales
            $eventOrder->fill($validated);
            $eventOrder->save();

            // 2.2) Actualizar líneas (cantidad, descripción, precio, impuesto)
            foreach ($linesData as $lineId => $lineInput) {
                /** @var EventOrderLine|null $line */
                $line = $eventOrder->lineas()->whereKey($lineId)->first();

                if (! $line) {
                    continue; // por si llega algo raro
                }

                // Normalizamos valores
                $cantidad = isset($lineInput['cantidad'])
                    ? max(0, (int) $lineInput['cantidad'])
                    : $line->cantidad;

                $precioUnit = isset($lineInput['precio_unitario'])
                    ? max(0, (float) $lineInput['precio_unitario'])
                    : (float) $line->precio_unitario;

                $impuestoPct = isset($lineInput['impuesto_porcentaje'])
                    ? max(0, (float) $lineInput['impuesto_porcentaje'])
                    : (float) $line->impuesto_porcentaje;

                $descripcion = $lineInput['descripcion'] ?? $line->descripcion;

                $line->cantidad            = $cantidad;
                $line->precio_unitario     = $precioUnit;
                $line->impuesto_porcentaje = $impuestoPct;
                $line->descripcion         = $descripcion;

                // Recalcular totales de la línea (si tienes columnas para esto)
                $subtotal       = $precioUnit * $cantidad;
                $impuestoMonto  = $subtotal * ($impuestoPct / 100);
                $totalLinea     = $subtotal + $impuestoMonto;

                $line->subtotal       = $subtotal;
                $line->impuesto_monto = $impuestoMonto;
                $line->total          = $totalLinea;

                $line->save();
            }

            // 2.3) Recalcular totales de la orden (usa todas las líneas actuales)
            $eventOrder->recalcularTotales();

            // (Opcional, si quieres que pagado / saldo se actualicen por si cambió el total)
            $eventOrder->recalcularPagos();
        });

        return redirect()
            ->route('event-orders.show', $eventOrder)
            ->with('status', 'Orden actualizada correctamente.');
    }
}
