<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only('search', 'estado');

        $query = Cliente::query();

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('telefono', 'like', "%{$s}%")
                  ->orWhere('rfc', 'like', "%{$s}%");
            });
        }

        if (($filters['estado'] ?? '') === 'activos') {
            $query->where('activo', true);
        } elseif (($filters['estado'] ?? '') === 'inactivos') {
            $query->where('activo', false);
        }

        $clientes = $query
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'filters'));
    }

    public function create()
    {
        $cliente = new Cliente();

        return view('clientes.create', compact('cliente'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', 'unique:clientes,email'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'tipo'     => ['nullable', 'string', 'max:100'],
            'rfc'      => ['nullable', 'string', 'max:20', 'unique:clientes,rfc'],
            'notas'    => ['nullable', 'string'],
            'activo'   => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $request->has('activo');

        Cliente::create($data);

        return redirect()
            ->route('clientes.index')
            ->with('ok', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre'   => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', 'unique:clientes,email,' . $cliente->id],
            'telefono' => ['nullable', 'string', 'max:50'],
            'tipo'     => ['nullable', 'string', 'max:100'],
            'rfc'      => ['nullable', 'string', 'max:20', 'unique:clientes,rfc,' . $cliente->id],
            'notas'    => ['nullable', 'string'],
            'activo'   => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $request->has('activo');

        $cliente->update($data);

        return redirect()
            ->route('clientes.index')
            ->with('ok', 'Cliente actualizado correctamente.');
    }

    public function toggle(Cliente $cliente)
    {
        $cliente->activo = ! $cliente->activo;
        $cliente->save();

        return back()->with('ok', 'Estado del cliente actualizado.');
    }
}
