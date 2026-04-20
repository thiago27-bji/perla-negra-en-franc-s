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
        Schema::create('servicio', function (Blueprint $table) {
                $table->id('idServicio');

                    $table->string('nombresServicio', 100);
                    $table->string('descripcion', 255)->nullable();

                    $table->integer('duracionMinuto');
                    $table->double('precio');

                    // Ruta de la imagen
                    $table->string('imagen', 255)->nullable();

                    $table->timestamps();
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio');
    }
};
