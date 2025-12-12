<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    protected $table = 'cash_movements';

    protected $fillable = [
        'cash_session_id',
        'tipo',          // ingreso / egreso
        'categoria_id',
        'monto',
        'metodo',        // efectivo, transferencia, tarjeta...
        'referencia',
        'notas',
        'user_id',
        'fecha',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'categoria_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
