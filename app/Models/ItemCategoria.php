<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategoria extends Model
{
    use HasFactory;

    protected $table = 'item_categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'categoria_id');
    }
}
