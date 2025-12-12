<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Item::query()
            ->where('activo', true)
            ->orderBy('nombre');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $items = $query->limit(50)->get([
            'id',
            'sku',
            'nombre',
            'precio_renta_dia',
            'precio_renta_fin',
            'stock_fisico',
        ]);

        return $items;
    }
}
