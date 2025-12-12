<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'activo',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'unidad_id');
    }
}
