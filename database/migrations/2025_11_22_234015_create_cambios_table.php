<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cambios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proceso_id')
                ->constrained('procesos')
                ->onDelete('cascade');

            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Pendiente', 'Finalizado', 'Entregado', 'En Revision', 'En Ejecucion'])->default('Pendiente');

            $table->date('fecha');
            $table->foreignId('editor_id')->constrained('users');

            $table->timestamps();
        });

        Schema::create('cambio_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cambio_id')
                ->constrained('cambios')
                ->onDelete('cascade');

            $table->string('ruta');
            $table->string('nombre_original');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambio_documentos');
        Schema::dropIfExists('cambios');
    }
};
