<?php

namespace App\Http\Controllers;

use App\Models\ItemCategoria;
use Illuminate\Http\Request;

class ItemCategoriaController extends Controller
{
    public function index()
    {
        $categorias = ItemCategoria::orderBy('nombre')
            ->paginate(15);

        return view('item_categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('item_categorias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:item_categorias,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo'      => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $data['activo'] ?? true;

        ItemCategoria::create($data);

        return redirect()
            ->route('item-categorias.index')
            ->with('ok', 'Categoría creada correctamente.');
    }

    public function edit(ItemCategoria $itemCategoria)
    {
        return view('item_categorias.edit', [
            'categoria' => $itemCategoria,
        ]);
    }

    public function update(Request $request, ItemCategoria $itemCategoria)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:item_categorias,nombre,' . $itemCategoria->id],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo'      => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $data['activo'] ?? false;

        $itemCategoria->update($data);

        return redirect()
            ->route('item-categorias.index')
            ->with('ok', 'Categoría actualizada correctamente.');
    }

    // Activar / inactivar en lugar de borrar
    public function toggle(ItemCategoria $itemCategoria)
    {
        $itemCategoria->activo = ! $itemCategoria->activo;
        $itemCategoria->save();

        return redirect()
            ->route('item-categorias.index')
            ->with('ok', 'Estatus de la categoría actualizado.');
    }
}
