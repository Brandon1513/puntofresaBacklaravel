<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Cliente::query()->orderBy('nombre');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->limit(50)->get([
            'id',
            'nombre',
            'email',
            'telefono',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
        ]);

        $cliente = Cliente::create($data);

        return response()->json($cliente, 201);
    }
}
