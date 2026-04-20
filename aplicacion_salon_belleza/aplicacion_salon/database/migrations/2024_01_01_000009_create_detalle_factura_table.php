<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('detalle_factura', function (Blueprint $table) {
        $table->id('idDetalle');

        // FK a factura (string)
        $table->string('FK_factura');
        $table->foreign('FK_factura')
                ->references('idFacturas')
                ->on('factura')
                ->onDelete('cascade');

        // FK a servicios
        $table->unsignedBigInteger('FK_servicio');
        $table->foreign('FK_servicio')
                ->references('idServicio')
                ->on('servicio')
                ->onDelete('cascade');

        // Cantidad de servicios
        $table->integer('cantidad')->default(1);

        // Precio al momento de la compra
        $table->double('precioUnitario');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('detalle_factura');
}
};