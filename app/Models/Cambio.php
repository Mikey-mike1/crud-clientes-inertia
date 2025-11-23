<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cambio extends Model
{
    use HasFactory;

    protected $fillable = [
        'proceso_id',
        'titulo',
        'descripcion',
        'estado',
        'fecha',
        'editor_id',
    ];

    const ESTADOS = ['Pendiente', 'Finalizado', 'Entregado', 'En Revision', 'En Ejecucion'];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function documentos()
    {
        return $this->hasMany(CambioDocumento::class);
    }
}
