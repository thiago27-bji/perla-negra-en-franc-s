<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('cita', function (Blueprint $table) {
        $table->id('idCita');

        // FK a usuarios (cliente)
        $table->unsignedBigInteger('FK_usuario');
        $table->foreign('FK_usuario')
                ->references('idUsuario')
                ->on('users')
                ->onDelete('cascade');

        // FK a personal
        $table->unsignedBigInteger('FK_personal');
        $table->foreign('FK_personal')
                ->references('idPersonal')
                ->on('personal')
                ->onDelete('cascade');

        // Fecha de la cita
        $table->dateTime('fechaCita');

        // Estado de la cita
        $table->unsignedBigInteger('FK_estadoCita');
        $table->foreign('FK_estadoCita')
                ->references('idEstado')
                ->on('estado_cita');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('cita');
}
};