<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CambioDocumento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cambio_id',
        'ruta',
        'nombre_original',
    ];

    public function cambio()
    {
        return $this->belongsTo(Cambio::class);
    }
}
