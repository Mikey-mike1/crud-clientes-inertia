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
        // Usamos Schema::table para modificar la tabla existente si es necesario, 
        // pero aquí definimos la estructura final sin 'archivos'.
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();

            // Clave foránea para el Cliente (cliente_id)
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            
            $table->string('tipo');
            $table->text('descripcion');

            // Columna 'archivos' ELIMINADA. Ahora se utiliza la tabla 'documentos'.
            
            $table->string('estado')->default('Pendiente'); 
            
            $table->date('fecha_inicio');
            $table->date('fecha_final')->nullable(); 
            
            // Clave foránea para el Editor/Usuario asignado (editor_id)
            $table->foreignId('editor_id')->constrained('users'); 
            
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};