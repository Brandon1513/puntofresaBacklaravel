<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory;

    protected $table = 'cost_centers';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
    ];
}
