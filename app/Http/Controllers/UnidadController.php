<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function index()
    {
        $unidades = Unidad::orderBy('nombre')
            ->paginate(15);

        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('unidades.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:unidades,nombre'],
            'abreviatura' => ['required', 'string', 'max:10'],
            'activo'      => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $request->has('activo');

        Unidad::create($data);

        return redirect()
            ->route('unidades.index')
            ->with('ok', 'Unidad creada correctamente.');
    }


    public function edit(Unidad $unidad)
    {
        return view('unidades.edit', compact('unidad'));
    }

    public function update(Request $request, Unidad $unidad)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:unidades,nombre,' . $unidad->id],
            'abreviatura' => ['required', 'string', 'max:10'],
            'activo'      => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $request->has('activo');

        $unidad->update($data);

        return redirect()
            ->route('unidades.index')
            ->with('ok', 'Unidad actualizada correctamente.');
    }
    public function toggle(Unidad $unidad)
    {
        $unidad->activo = ! $unidad->activo;
        $unidad->save();

        return redirect()
            ->route('unidades.index')
            ->with('ok', 'Estatus de la unidad actualizado.');
    }
}
