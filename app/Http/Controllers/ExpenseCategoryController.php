<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $cats = ExpenseCategory::orderBy('nombre')->paginate(20);

        return view('expense_categories.index', compact('cats'));
    }

    public function create()
    {
        return view('expense_categories.create');
    }

public function store(Request $request)
{
    $data = $request->validate([
        'nombre' => 'required|string|max:120',
        'activo' => 'nullable', // podemos dejarlo así, sin boolean
    ]);

    $data['activo'] = $request->has('activo') ? 1 : 0;

    ExpenseCategory::create($data);

    return redirect()
        ->route('expense-categories.index')
        ->with('ok', 'Categoría creada correctamente.');
}



    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', [
            'cat' => $expenseCategory,
        ]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        $expenseCategory->update($data);

        return redirect()
            ->route('expense-categories.index')
            ->with('ok', 'Categoría actualizada.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return redirect()
            ->route('expense-categories.index')
            ->with('ok', 'Categoría eliminada.');
    }
}
