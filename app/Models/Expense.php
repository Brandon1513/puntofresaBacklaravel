<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model {
  protected $fillable=['proveedor','expense_category_id','cost_center_id','monto','fecha','metodo_pago','referencia','notas','status','created_by','approved_by'];
  protected $casts=['fecha'=>'date'];
  public function category(){ return $this->belongsTo(ExpenseCategory::class,'expense_category_id'); }
  public function costCenter(){ return $this->belongsTo(CostCenter::class,'cost_center_id'); }
  public function attachments(){ return $this->hasMany(ExpenseAttachment::class); }
  public function creator(){ return $this->belongsTo(User::class,'created_by'); }
  public function approver(){ return $this->belongsTo(User::class,'approved_by'); }
  // scopes de filtro
  public function scopeRango($q,$d1,$d2){ if($d1) $q->where('fecha','>=',$d1); if($d2) $q->where('fecha','<=',$d2); }
  public function scopeEstado($q,$s){ if($s) $q->where('status',$s); }

  
}
