<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PettyCashSession;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;

class PettyCashMovement extends Model
{
    use HasFactory;

    protected $table = 'petty_cash_movements';

    protected $fillable = [
        'petty_cash_session_id',
        'tipo',                   // 'ingreso' | 'egreso'
        'monto',
        'expense_id',
        'expense_category_id',
        'concepto',
        'notas',
        'created_by',
        'receipt_path',
        'receipt_original_name',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function session()
    {
        return $this->belongsTo(PettyCashSession::class, 'petty_cash_session_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getReceiptUrlAttribute()
    {
        return $this->receipt_path
            ? route('petty-cash-movements.receipt', $this)
            : null;
    }
}
