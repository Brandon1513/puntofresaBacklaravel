<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use Illuminate\Http\Request;

class BundleApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Bundle::query()
            ->where('activo', true)
            ->orderBy('nombre');

        // si ya tienes scopes ->activos()->vigentes(), puedes usarlos:
        // $query = Bundle::activos()->vigentes();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        $bundles = $query->limit(50)->get();

        // devolvemos solo lo necesario
        return $bundles->map(function (Bundle $bundle) {
            return [
                'id'           => $bundle->id,
                'nombre'       => $bundle->nombre,
                'descripcion'  => $bundle->descripcion,
                // asumiendo que tienes el accessor getPrecioFinalAttribute()
                'precio_final' => $bundle->precio_final,
            ];
        });
    }
}
