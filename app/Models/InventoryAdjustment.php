<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustment extends Model
{
    use HasFactory;
    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'item_id',
        'user_id',
        'tipo',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'motivo',
        'comentario',
        'evidencia_path',
        'created_by',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
