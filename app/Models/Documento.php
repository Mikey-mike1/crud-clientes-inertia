<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'proceso_id', // Clave foránea al proceso
        'ruta', // Ruta del archivo en Storage
        'nombre_original',  // Nombre con el que el usuario subió el archivo
    ];

    /**
     * Relación: Un documento pertenece a un Proceso (Relación Inversa)
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }
}