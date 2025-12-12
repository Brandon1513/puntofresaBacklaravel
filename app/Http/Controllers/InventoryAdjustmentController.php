<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryAdjustmentController extends Controller
{
    // Bitácora por ítem
    public function index(Item $item)
    {
        $ajustes = $item->ajustes()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('items.ajustes.index', compact('item', 'ajustes'));
    }

    // Formulario de nuevo ajuste
    public function create(Item $item)
    {
        return view('items.ajustes.create', compact('item'));
    }

    // Guardar ajuste
    public function store(Request $request, Item $item)
    {
        $data = $request->validate([
            'tipo'      => 'required|in:entrada,compra,merma,dano,correccion',
            'cantidad'  => 'required|integer|min:1',
            'motivo'    => 'nullable|string|max:1000',
            'evidencia' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $ajuste = new InventoryAdjustment();
        $ajuste->item_id = $item->id;
        $ajuste->user_id = Auth::id();
        $ajuste->tipo    = $data['tipo'];
        $ajuste->cantidad = $data['cantidad'];
        $ajuste->motivo   = $data['motivo'] ?? null;

        // Evidencia (opcional)
        if ($request->hasFile('evidencia')) {
            $path = $request->file('evidencia')->store('ajustes', 'public');
            $ajuste->evidencia_path = $path;
        }

        $ajuste->save();

        /*
         * 🔴 AQUÍ va tu lógica de actualización de stock_fisico
         * Si aún NO tienes nada, puedes usar algo así:
         */

        $delta = 0;
        switch ($data['tipo']) {
            case 'entrada':
            case 'compra':
                $delta = $data['cantidad'];      // suma
                break;
            case 'merma':
            case 'dano':
                $delta = -$data['cantidad'];     // resta
                break;
            case 'correccion':
                // Opción simple: tratamos corrección como "ajuste manual" positivo (puedes cambiar esto)
                $delta = $data['cantidad'];
                break;
        }

        if ($delta !== 0) {
            $item->stock_fisico = max(0, ($item->stock_fisico ?? 0) + $delta);
            $item->save();
        }

        return redirect()
            ->route('items.ajustes.index', $item)
            ->with('ok', 'Ajuste registrado correctamente.');
    }
}
