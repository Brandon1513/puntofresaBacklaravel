<?php

namespace App\Http\Controllers;

use App\Models\PettyCashSession;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;

class PettyCashSessionController extends Controller
{
    /**
     * Listar sesiones de caja chica
     */
    public function index(Request $request)
    {
        $q = PettyCashSession::with(['responsable', 'openedBy']);

        // Filtro por estatus si se envía en la URL
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        // Filtro por responsable (vendedor)
        if ($request->filled('responsable_id')) {
            $q->where('responsable_id', $request->responsable_id);
        }

        $sessions = $q
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Para combo de vendedores en filtros
        $vendedores = User::role('ventas')
            ->orderBy('name')
            ->get();

        return view('petty_cash_sessions.index', compact('sessions', 'vendedores'));
    }

    /**
     * Formulario para abrir una nueva caja
     * (admin/finanzas eligen vendedor + saldo inicial)
     */
    public function create()
    {
        // Vendedores disponibles
        $vendedores = User::role('ventas')
            ->orderBy('name')
            ->get();

        return view('petty_cash_sessions.create', compact('vendedores'));
    }

    /**
     * Guardar apertura de caja
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'          => 'required|date',
            'responsable_id' => 'required|exists:users,id',
            'saldo_inicial'  => 'required|numeric|min:0',
        ]);

        // Validar que ese vendedor no tenga YA una caja abierta ese día
        $existeAbierta = PettyCashSession::where('responsable_id', $data['responsable_id'])
            ->whereDate('fecha', $data['fecha'])
            ->where('status', 'abierta')
            ->exists();

        if ($existeAbierta) {
            return back()
                ->withInput()
                ->withErrors(['responsable_id' => 'Este vendedor ya tiene una caja abierta para esa fecha.']);
        }

        PettyCashSession::create([
            'fecha'                 => $data['fecha'],
            'responsable_id'        => $data['responsable_id'],
            'opened_by_id'          => auth()->id(),
            'saldo_inicial'         => $data['saldo_inicial'],
            'status'                => 'abierta',
            'abierta_en'            => now(),
            'saldo_teorico_cierre'  => null,
            'saldo_contado_cierre'  => null,
            'diferencia'            => null,
        ]);

        return redirect()
            ->route('petty-cash-sessions.index')
            ->with('ok', 'Caja chica abierta correctamente.');
    }

  public function show(PettyCashSession $pettyCashSession)
{
    // Cargamos relaciones necesarias
    $pettyCashSession->load([
        'responsable',
        'openedBy',
        'movimientos.creator',
        'movimientos.category',
    ]);

    // Categorías de gasto para egresos
    $expenseCategories = ExpenseCategory::where('activo', 1)
        ->orderBy('nombre')
        ->get();

    return view('petty_cash_sessions.show', [
        'session'           => $pettyCashSession,
        'expenseCategories' => $expenseCategories,
    ]);
}
public function closeForm(PettyCashSession $pettyCashSession)
    {
        if ($pettyCashSession->status !== 'abierta') {
            return redirect()
                ->route('petty-cash-sessions.show', $pettyCashSession)
                ->withErrors(['session' => 'Esta caja ya está cerrada.']);
        }

        $pettyCashSession->load(['responsable', 'openedBy']);

        return view('petty_cash_sessions.close', [
            'session' => $pettyCashSession,
        ]);
    }

    public function close(Request $request, PettyCashSession $pettyCashSession)
    {
        if ($pettyCashSession->status !== 'abierta') {
            return redirect()
                ->route('petty-cash-sessions.show', $pettyCashSession)
                ->withErrors(['session' => 'Esta caja ya está cerrada.']);
        }

        $data = $request->validate([
            'monto_arqueo' => ['required', 'numeric', 'min:0'],
            'notas_cierre' => ['nullable', 'string'],
        ]);

        $esperado   = $pettyCashSession->saldo_actual;           // teórico
        $diferencia = $data['monto_arqueo'] - $esperado;

        $pettyCashSession->monto_arqueo         = $data['monto_arqueo'];
        $pettyCashSession->diferencia           = $diferencia;
        $pettyCashSession->status               = 'cerrada';
        $pettyCashSession->closed_by_id         = auth()->id();  // <- nombre correcto
        $pettyCashSession->cerrada_en           = now();         // <- nombre correcto
        $pettyCashSession->notas_cierre         = $data['notas_cierre'] ?? null; // solo si tienes la columna

        $pettyCashSession->save();

        return redirect()
            ->route('petty-cash-sessions.show', $pettyCashSession)
            ->with('ok', 'Caja chica cerrada y conciliada correctamente.');
    }

}
