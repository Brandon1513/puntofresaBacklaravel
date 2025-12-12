<?php

// app/Models/BundlePhoto.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BundlePhoto extends Model
{
    use HasFactory;

    protected $table = 'bundle_photos';

    protected $fillable = [
        'bundle_id',
        'path',
        'es_principal',
        'orden',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
}
