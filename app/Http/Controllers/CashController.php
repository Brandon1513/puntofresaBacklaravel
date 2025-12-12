<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\CashSession;
use App\Models\CashMovement;
use Illuminate\Http\Request;

class CashController extends Controller
{
  public function index(){
    $box = CashBox::firstOrCreate(['nombre'=>'Caja Principal']);
    $session = CashSession::where('cash_box_id',$box->id)->where('status','abierta')->latest()->first();
    $movs = $session ? CashMovement::where('cash_session_id',$session->id)->orderByDesc('id')->get() : collect();
    $saldo = $session
      ? $session->saldo_inicial
        + CashMovement::where('cash_session_id',$session->id)->where('tipo','ingreso')->sum('monto')
        - CashMovement::where('cash_session_id',$session->id)->where('tipo','egreso')->sum('monto')
      : 0;

    return view('cash.index', compact('box','session','movs','saldo'));
  }

  public function open(Request $r){
    $data = $r->validate([
      'cash_box_id'=>'required|exists:cash_boxes,id',
      'saldo_inicial'=>'required|numeric|min:0'
    ]);
    abort_if(CashSession::where('cash_box_id',$data['cash_box_id'])->where('status','abierta')->exists(), 422, 'Ya hay una sesión abierta.');
    $s = CashSession::create([
      'cash_box_id'=>$data['cash_box_id'],
      'opened_by'=>auth()->id(),
      'saldo_inicial'=>$data['saldo_inicial'],
      'opened_at'=>now(),
      'status'=>'abierta',
    ]);
    return back()->with('ok','Caja abierta.');
  }

  public function movement(Request $r){
    $data = $r->validate([
      'cash_session_id'=>'required|exists:cash_sessions,id',
      'tipo'=>'required|in:ingreso,egreso',
      'categoria_id'=>'nullable|exists:cash_categories,id',
      'monto'=>'required|numeric|min:0.01',
      'metodo'=>'nullable|string|max:30',
      'referencia'=>'nullable|string|max:100',
      'notas'=>'nullable|string',
      'fecha'=>'required|date'
    ]);
    $data['user_id']=auth()->id();
    CashMovement::create($data);
    return back()->with('ok','Movimiento registrado.');
  }

  public function close(Request $r, CashSession $session){
    abort_if($session->status !== 'abierta', 422, 'La sesión ya está cerrada.');
    $ing = CashMovement::where('cash_session_id',$session->id)->where('tipo','ingreso')->sum('monto');
    $egr = CashMovement::where('cash_session_id',$session->id)->where('tipo','egreso')->sum('monto');
    $teorico = $session->saldo_inicial + $ing - $egr;

    $data = $r->validate(['saldo_real_cierre'=>'required|numeric|min:0']);
    $session->update([
      'saldo_teorico_cierre'=>$teorico,
      'saldo_real_cierre'=>$data['saldo_real_cierre'],
      'diferencia'=> $data['saldo_real_cierre'] - $teorico,
      'closed_by'=>auth()->id(),
      'closed_at'=>now(),
      'status'=>'cerrada',
    ]);
    return back()->with('ok','Caja cerrada.');
  }
}

