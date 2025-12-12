<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $table = 'expense_categories';

    // Campos que se pueden guardar con create() / update()
    protected $fillable = [
        'nombre',
        'activo',
    ];

    // Tus columnas created_at / updated_at están, pero no las usas:
    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
    ];
}
