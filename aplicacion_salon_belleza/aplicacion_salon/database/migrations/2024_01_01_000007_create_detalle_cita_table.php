<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('detalle_cita', function (Blueprint $table) {
        $table->id('idDetalle');

        // FK a cita
        $table->unsignedBigInteger('FK_cita');
        $table->foreign('FK_cita')
                ->references('idCita')
                ->on('cita')
                ->onDelete('cascade');

        // FK a servicios
        $table->unsignedBigInteger('FK_servicio');
        $table->foreign('FK_servicio')
                ->references('idServicio')
                ->on('servicio')
                ->onDelete('cascade');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('detalle_cita');
}
};