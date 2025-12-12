<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    use HasFactory;

    protected $table = 'event_payments';

    protected $fillable = [
        'event_order_id',
        'user_id',
        'petty_cash_session_id',
        'metodo',
        'monto',
        'referencia',
        'pagado_en',
    ];

    protected $casts = [
        'monto'     => 'decimal:2',
        'pagado_en' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(EventOrder::class, 'event_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pettyCashSession()
    {
        return $this->belongsTo(PettyCashSession::class, 'petty_cash_session_id');
    }
}
