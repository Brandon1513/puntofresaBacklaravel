<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    protected $table = 'cash_sessions';

    protected $fillable = [
        'cash_box_id',
        'opened_by',
        'closed_by',
        'saldo_inicial',
        'saldo_teorico_cierre',
        'saldo_real_cierre',
        'diferencia',
        'opened_at',
        'closed_at',
        'status',
    ];

    protected $casts = [
        'saldo_inicial'        => 'decimal:2',
        'saldo_teorico_cierre' => 'decimal:2',
        'saldo_real_cierre'    => 'decimal:2',
        'diferencia'           => 'decimal:2',
        'opened_at'            => 'datetime',
        'closed_at'            => 'datetime',
    ];

    public function box(): BelongsTo
    {
        return $this->belongsTo(CashBox::class, 'cash_box_id');
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'cash_session_id');
    }

    // 👉 scope para obtener la caja abierta del usuario
    public function scopeOpenForUser($query, int $userId)
    {
        return $query
            ->where('opened_by', $userId)
            ->where('status', 'abierta')
            ->whereNull('closed_at');
    }
}
