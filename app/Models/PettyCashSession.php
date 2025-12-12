<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PettyCashMovement;

class PettyCashSession extends Model
{
    use HasFactory;

    protected $table = 'petty_cash_sessions';

    protected $fillable = [
        'fecha',
        'responsable_id',
        'opened_by_id',
        'closed_by_id',
        'saldo_inicial',
        'saldo_actual',
        'monto_arqueo',
        'saldo_teorico_cierre',
        'saldo_contado_cierre',
        'diferencia',
        'status',
        'abierta_en',
        'cerrada_en',
        'notas_cierre',
    ];

    protected $casts = [
        'fecha'                => 'date',
        'saldo_inicial'        => 'decimal:2',
        'saldo_actual'         => 'decimal:2',
        'monto_arqueo'         => 'decimal:2',
        'saldo_teorico_cierre' => 'decimal:2',
        'saldo_contado_cierre' => 'decimal:2',
        'diferencia'           => 'decimal:2',
        'abierta_en'           => 'datetime',
        'cerrada_en'           => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function movimientos()
    {
        return $this->hasMany(PettyCashMovement::class, 'petty_cash_session_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Sesiones abiertas para un usuario (como responsable o quien la abrió).
     */
    public function scopeOpenForUser($query, int $userId)
    {
        return $query
            ->where('status', 'abierta')
            ->where(function ($q) use ($userId) {
                $q->where('responsable_id', $userId)
                    ->orWhere('opened_by_id', $userId);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de totales
    |--------------------------------------------------------------------------
    */

    protected function ingresosQuery()
    {
        return $this->movimientos()->where('tipo', 'ingreso');
    }

    /**
     * Egresos que sí afectan saldo:
     * - Sin expense_id (ajustes manuales)
     * - O ligados a un Expense con status = 'aprobado'
     */
    protected function egresosQuery()
    {
        return $this->movimientos()
            ->where('tipo', 'egreso')
            ->where(function ($q) {
                $q->whereNull('expense_id')
                    ->orWhereHas('expense', function ($qq) {
                        $qq->where('status', 'aprobado');
                    });
            });
    }

    public function getTotalIngresosAttribute()
    {
        return (float) $this->ingresosQuery()->sum('monto');
    }

    public function getTotalEgresosAttribute()
    {
        return (float) $this->egresosQuery()->sum('monto');
    }

    /**
     * Saldo actual teórico =
     * saldo inicial + ingresos - egresos (solo aprobados).
     */
    public function getSaldoActualAttribute()
    {
        $saldoInicial = $this->attributes['saldo_inicial'] ?? 0;

        return (float) $saldoInicial
            + $this->total_ingresos
            - $this->total_egresos;
    }
}
