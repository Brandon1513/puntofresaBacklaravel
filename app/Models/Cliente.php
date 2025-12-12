<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'tipo',
        'rfc',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function ordenes()
    {
        return $this->hasMany(EventOrder::class, 'cliente_id');
    }

    /** Scope rápido para activos */
    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }
}
