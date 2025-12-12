<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Item;
use App\Models\Bundle;

class EventOrderLine extends Model
{
    use HasFactory;

    protected $table = 'event_order_lines';

    protected $fillable = [
        'event_order_id',
        'tipo',
        'item_id',
        'bundle_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'impuesto_porcentaje',
    ];

    protected $casts = [
        'cantidad'            => 'integer',
        'precio_unitario'     => 'decimal:2',
        'impuesto_porcentaje' => 'decimal:2',
    ];

    public function orden()
    {
        return $this->belongsTo(EventOrder::class, 'event_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    // --- Helpers de importes ---

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->cantidad * $this->precio_unitario);
    }

    public function getImpuestoMontoAttribute(): float
    {
        return round($this->subtotal * ($this->impuesto_porcentaje / 100), 2);
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->impuesto_monto;
    }
}
