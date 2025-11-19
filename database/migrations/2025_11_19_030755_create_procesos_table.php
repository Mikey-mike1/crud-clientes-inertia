<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();

            // Relación con Cliente
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            
            // Campos de texto
            $table->string('tipo');
            $table->text('descripcion');

            // Campo para subir archivos (PDF, DOCX) - Nullable por si no suben nada al inicio
            $table->string('archivos')->nullable();

            // Estado con valor por defecto
            $table->string('estado')->default('Pendiente'); // Pendiente, Finalizado, Entregado, En Revision

            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_final')->nullable(); // Nullable porque puede no haber terminado
            
            // Relación con Usuario (Editor asignado)
            $table->foreignId('editor_id')->constrained('users'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};