<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('factura', function (Blueprint $table) {
        // ID tipo string (ej: FACT-001)
        $table->string('idFacturas')->primary();

        $table->date('fechaGeneracion');
        $table->double('montoTotal');

        // FK a usuarios (cliente)
        $table->unsignedBigInteger('FK_usuario');
        $table->foreign('FK_usuario')
                ->references('idUsuario')
                ->on('users')
                ->onDelete('cascade');

        // FK a estado_cita (según tu modelo)
        $table->unsignedBigInteger('FK_estadoCita');
        $table->foreign('FK_estadoCita')
                ->references('idEstado')
                ->on('estado_cita');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('factura');
}
};