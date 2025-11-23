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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cambio_documentos');
    }
};
