<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidad', function (Blueprint $table) {
            $table->id('idDisponibilidad');

            // FK a personal
            $table->unsignedBigInteger('FK_personal');
            $table->foreign('FK_personal')
                  ->references('idPersonal')
                  ->on('personal')
                  ->onDelete('cascade');

            // Día de la semana (Lunes, Martes, etc.)
            $table->string('diaSemana', 20);

            // Horario
            $table->time('horaInicio');
            $table->time('horaFin');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidad');
    }
};