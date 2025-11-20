<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            
            // Clave foránea: Un documento pertenece a un Proceso.
            // Esto asegura la integridad referencial y permite la eliminación en cascada.
            $table->foreignId('proceso_id')
                  ->constrained('procesos') // Relaciona con la tabla 'procesos'
                  ->onDelete('cascade');    // Si el proceso se elimina, los documentos asociados se eliminan.
            
            // Ruta del archivo en el sistema de almacenamiento (ej. storage/app/public/procesos/1/archivo.pdf)
            $table->string('ruta');
            
            // Nombre original del archivo, útil para descargar o mostrar.
            $table->string('nombre_original'); 

            $table->timestamps();

            // Índice para optimizar búsquedas basadas en el proceso.
            $table->index('proceso_id');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};