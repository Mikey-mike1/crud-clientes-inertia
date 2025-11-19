<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proceso extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'tipo',
        'descripcion',
        'archivos',      // Ruta del archivo
        'estado',
        'fecha_inicio',
        'fecha_final',
        'editor_id',     // ID del usuario asignado
    ];

    // Relación: Un proceso pertenece a un Cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación: Un proceso es asignado a un Editor (User)
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}