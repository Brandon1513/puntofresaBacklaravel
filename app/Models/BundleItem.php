<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BundleItem extends Model
{
    use HasFactory;

    protected $table = 'bundle_items';

    protected $fillable = [
        'bundle_id',
        'item_id',
        'cantidad',
        'precio_unitario_paquete',
        'precio_unitario_cache',
    ];

    protected $casts = [
        'precio_unitario_cache' => 'decimal:2',
        'precio_unitario_paquete'  => 'decimal:2',
        'cantidad'              => 'integer',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
     public function getPrecioUnitarioAplicadoAttribute()
    {
        // Si hay precio especial de paquete, úsalo; si no, el del ítem
        return $this->precio_unitario_paquete ?? $this->item->precio_renta_dia ?? 0;
    }

    public function getImporteAttribute()
    {
        return $this->precio_unitario_aplicado * $this->cantidad;
    }
}
