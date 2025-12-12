<?php

namespace App\Http\Controllers;

use App\Models\EventOrder;

class EventOrderPrintController extends Controller
{
    public function show(EventOrder $eventOrder)
    {
        $eventOrder->load([
            'cliente',
            'lineas.item',
            'lineas.bundle',
            'pagos',
        ]);

        return view('event_orders.print', [
            'order' => $eventOrder,
        ]);
    }
}
