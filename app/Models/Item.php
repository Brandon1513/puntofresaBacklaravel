<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ItemCategoria;
use App\Models\Unidad;
use App\Models\ItemPhoto;
use App\Models\InventoryAdjustment;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'sku',
        'nombre',
        'categoria_id',
        'unidad_id',
        'qr_code',
        'precio_renta_dia',
        'precio_renta_fin',
        'costo_promedio',
        'costo_reposicion',
        'stock_fisico',
        'stock_minimo',
        'ubicacion',
        'activo',
        'tags',
        'descripcion',
    ];

    protected $casts = [
        'precio_renta_dia'   => 'decimal:2',
        'precio_renta_fin'   => 'decimal:2',
        'costo_promedio'     => 'decimal:2',
        'costo_reposicion'   => 'decimal:2',
        'stock_fisico'       => 'integer',
        'stock_minimo'       => 'integer',
        'activo'             => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(ItemCategoria::class, 'categoria_id');
    }

    // Alias en inglés para que ->with('category') funcione
    public function category()
    {
        return $this->categoria();
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    // Puedes dejar "fotos" si ya lo usas en otros lados
    public function fotos()
    {
        return $this->hasMany(ItemPhoto::class);
    }

    // 🔹 Alias en inglés para que funcione con ->load('photos') y $item->photos
    public function photos()
    {
        return $this->hasMany(ItemPhoto::class);
    }

    public function mainPhoto()
    {
        return $this->hasOne(ItemPhoto::class)->where('es_principal', 1);
    }

    public function ajustes()
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function scopeActivos($q)
    {
        return $q->where('activo', 1);
    }
    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'bundle_items')
                    ->withPivot(['cantidad', 'precio_unitario_cache'])
                    ->withTimestamps();
    }

}
