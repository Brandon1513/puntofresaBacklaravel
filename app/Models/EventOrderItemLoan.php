<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventOrderItemLoan extends Model
{
    use HasFactory;

    protected $table = 'event_order_item_loans';

    protected $fillable = [
        'event_order_id',
        'item_id',
        'cantidad_prestada',
        'cantidad_devuelta',
        'created_by',
    ];

    protected $casts = [
        'cantidad_prestada' => 'integer',
        'cantidad_devuelta' => 'integer',
    ];

    // 🔗 Orden de evento
    public function order()
    {
        return $this->belongsTo(EventOrder::class, 'event_order_id');
    }

    // 🔗 Ítem prestado
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // 🔗 Usuario que registró el préstamo
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
