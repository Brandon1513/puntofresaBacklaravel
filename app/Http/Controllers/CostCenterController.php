<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    public function index()
    {
        $ccs = CostCenter::orderBy('nombre')->paginate(20);

        return view('cost_centers.index', compact('ccs'));
    }

    public function create()
    {
        return view('cost_centers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'activo' => 'nullable', // igual que en categorías
        ]);

        // Checkbox: si viene marcado => 1, si no => 0
        $data['activo'] = $request->has('activo') ? 1 : 0;

        CostCenter::create($data);

        return redirect()
            ->route('cost-centers.index')
            ->with('ok', 'Centro de costo creado correctamente.');
    }

    public function edit(CostCenter $costCenter)
    {
        return view('cost_centers.edit', [
            'cc' => $costCenter,
        ]);
    }

    public function update(Request $request, CostCenter $costCenter)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'activo' => 'nullable',
        ]);

        $data['activo'] = $request->has('activo') ? 1 : 0;

        $costCenter->update($data);

        return redirect()
            ->route('cost-centers.index')
            ->with('ok', 'Centro de costo actualizado.');
    }

    public function destroy(CostCenter $costCenter)
    {
        $costCenter->delete();

        return redirect()
            ->route('cost-centers.index')
            ->with('ok', 'Centro de costo eliminado.');
    }
}
