<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Models\BundlePhoto;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'precio_personalizado',
        'descuento_porcentaje',
        'usar_precio_personalizado',
        'activo',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected $casts = [
        'precio_personalizado'     => 'decimal:2',
        'usar_precio_personalizado'=> 'boolean',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
        'activo'                   => 'boolean',
    ];

    // Relación: un bundle tiene muchas líneas (bundle_items)
    public function lineas()
    {
        return $this->hasMany(BundleItem::class);
    }

    // Relación cómoda muchos-a-muchos con Items (a través de bundle_items)
    public function items()
    {
        return $this->belongsToMany(Item::class, 'bundle_items')
                    ->withPivot(['cantidad', 'precio_unitario_cache'])
                    ->withTimestamps();
    }

    /**
     * Calcula el precio sugerido del paquete en base a las líneas
     * usando el precio_renta_dia del ítem (se puede ajustar).
     */
    public function getPrecioCalculadoAttribute()
    {
        $base = $this->lineas->sum(function (BundleItem $linea) {
            $precioBase = $linea->precio_unitario_cache
                ?? $linea->item->precio_renta_dia
                ?? 0;

            return $precioBase * $linea->cantidad;
        });

        if ($this->descuento_porcentaje > 0) {
            $base = $base * (1 - $this->descuento_porcentaje / 100);
        }

        return round($base, 2);
    }

    /**
     * Precio final a usar en la OE:
     * - si usar_precio_personalizado = true y hay valor => ese
     * - si no, usa el calculado
     */
    public function getPrecioFinalAttribute()
{
    // 1) Si usa precio personalizado de paquete, ese manda
    if ($this->usa_precio_personalizado && $this->precio_personalizado) {
        return $this->precio_personalizado;
    }

    // 2) Si no, aplicar descuento % sobre el precio calculado
    $base = $this->precio_calculado;

    if ($this->descuento_porcentaje) {
        return $base * (1 - $this->descuento_porcentaje / 100);
    }

    return $base;
}
    // Scope para solo activos
    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }
    public function scopeVigentes(Builder $q): Builder
    {
        $hoy = Carbon::today();

        return $q->where('activo', true)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('vigente_desde')
                  ->orWhere('vigente_desde', '<=', $hoy);
            })
            ->where(function ($q) use ($hoy) {
                $q->whereNull('vigente_hasta')
                  ->orWhere('vigente_hasta', '>=', $hoy);
            });
    }
    public function getAhorroAttribute()
    {
        $calc  = $this->precio_calculado;
        $final = $this->precio_final;

        $diff = $calc - $final;

        // Solo tiene sentido mostrar ahorro si es positivo
        return $diff > 0 ? round($diff, 2) : 0;
    }

    public function photos()
    {
        return $this->hasMany(BundlePhoto::class);
    }

    public function mainPhoto()
    {
        return $this->hasOne(BundlePhoto::class)->where('es_principal', 1);
    }
    
}
