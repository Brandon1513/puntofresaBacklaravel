<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\CostCenter;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Notifications\ExpenseSubmittedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // ✅
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use AuthorizesRequests; // ✅ habilita $this->authorize()

    
    public function index(Request $r)
    {
        $this->authorize('viewAny', Expense::class);

        $q = Expense::with(['category','costCenter','creator'])
            ->when($r->status, function ($q) use ($r) {
                // valores: borrador|aprobado|rechazado
                $q->where('status', $r->status);
            })
            ->when(($r->d1 && $r->d2), function ($q) use ($r) {
                $q->whereBetween('fecha', [$r->d1, $r->d2]);
            })
            ->orderByDesc('id');

        // Ventas ve solo lo suyo
        if (auth()->user()->hasRole('ventas')) {
            $q->where('created_by', auth()->id());
        }

        $expenses = $q->paginate(15)->withQueryString();

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $this->authorize('create', Expense::class);

        return view('expenses.create', [
            'cats' => ExpenseCategory::where('activo', 1)->orderBy('nombre')->get(),
            'ccs'  => CostCenter::where('activo', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $r)
{
    $data = $r->validate([
        'proveedor'           => 'nullable|string|max:120',
        'expense_category_id' => 'nullable|exists:expense_categories,id',
        'cost_center_id'      => 'nullable|exists:cost_centers,id',
        'monto'               => 'required|numeric|min:0',
        'fecha'               => 'required|date',
        'metodo_pago'         => 'nullable|string|max:30',
        'referencia'          => 'nullable|string|max:100',
        'notas'               => 'nullable|string',
        'files.*'             => 'file|max:5120',
    ]);

    $data['created_by'] = auth()->id();
    $expense = Expense::create($data);

    // Adjuntos (ya lo tenías)
    if ($r->hasFile('files')) {
        $disk = config('filesystems.disks.local') ? 'local' : 'public';
        foreach ($r->file('files') as $f) {
            $path = \Storage::disk($disk)->put("gastos/{$expense->id}", $f);
            $expense->attachments()->create([
                'path'          => $path,
                'original_name' => $f->getClientOriginalName(),
                'mime'          => $f->getClientMimeType(),
                'size'          => $f->getSize(),
            ]);
        }
    }

    // 🔔 Notificar a finanzas / admin / superadmin
    $notifiables = User::role(['superadmin', 'administrador', 'finanzas'])
        ->where('activo', 1)
        ->whereNotNull('email')
        ->get();

    foreach ($notifiables as $user) {
        $user->notify(new ExpenseSubmittedNotification($expense));
    }

    return to_route('expenses.index')->with('ok', 'Gasto registrado y enviado a revisión.');
}


    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);

        $expense->load('attachments');

        return view('expenses.edit', [
            'expense' => $expense,
            'cats'    => ExpenseCategory::where('activo', 1)->orderBy('nombre')->get(),
            'ccs'     => CostCenter::where('activo', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $r, Expense $expense)
    {
        $this->authorize('update', $expense);

        $data = $r->validate([
            'proveedor'            => 'nullable|string|max:120',
            'expense_category_id'  => 'nullable|exists:expense_categories,id',
            'cost_center_id'       => 'nullable|exists:cost_centers,id',
            'monto'                => 'required|numeric|min:0',
            'fecha'                => 'required|date',
            'metodo_pago'          => 'nullable|string|max:30',
            'referencia'           => 'nullable|string|max:100',
            'notas'                => 'nullable|string',
            'files.*'              => 'file|max:5120',
        ]);

        $expense->update($data);

        // Nuevos adjuntos
        if ($r->hasFile('files')) {
            foreach ($r->file('files') as $f) {
                $path = $f->store("gastos/{$expense->id}", 'public');

                $expense->attachments()->create([
                    'path'          => $path,
                    'original_name' => $f->getClientOriginalName(),
                    'mime'          => $f->getClientMimeType(),
                    'size'          => $f->getSize(),
                ]);
            }
        }


        return back()->with('ok', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        foreach ($expense->attachments as $a) {
            if ($a->path) {
                Storage::disk('public')->delete($a->path);
            }
        }

        $expense->delete();

        return back()->with('ok', 'Gasto eliminado.');
    }

    public function approve(Expense $expense)
    {
        $this->authorize('approve', $expense);

        $expense->update([
            'status'      => 'aprobado',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Gasto aprobado.');
    }

    public function reject(Expense $expense)
    {
        $this->authorize('approve', $expense);

        $expense->update([
            'status'      => 'rechazado',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Gasto rechazado.');
    }
public function show(Expense $expense)
{
    $this->authorize('view', $expense);

    // Carga relaciones que ya usas en index
    $expense->load(['category', 'costCenter', 'creator', 'attachments']);

    return view('expenses.show', compact('expense'));
}


}
