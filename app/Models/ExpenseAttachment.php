<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseAttachment extends Model {
  protected $fillable=['expense_id','path','original_name','mime','size'];
  public function expense(){ return $this->belongsTo(Expense::class); }
}
