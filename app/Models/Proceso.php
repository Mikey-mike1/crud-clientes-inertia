<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proceso extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     * * Nota: 'archivos' ha sido ELIMINADO de esta lista 
     * y de la migración de la base de datos.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cliente_id',
        'tipo',
        'descripcion',
        'estado',
        'fecha_inicio',
        'fecha_final',
        'editor_id',
    ];

    /**
     * Relación: Un proceso pertenece a un Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación: Un proceso es asignado a un Editor (User)
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    /**
     * Relación: Un proceso puede tener muchos Documentos (Uno a Muchos)
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}