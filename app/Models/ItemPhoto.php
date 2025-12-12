<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemPhoto extends Model
{
    use HasFactory;

     protected $table = 'item_photos';

    protected $fillable = [
        'item_id',
        'path',
        'es_principal',
        'orden',
    ];
    protected $casts = [
        'es_principal' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
