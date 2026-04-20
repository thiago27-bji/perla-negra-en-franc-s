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
        Schema::create('personal_servicio', function (Blueprint $table) {
            $table->id();

            // 🔗 Relaciones
            $table->unsignedBigInteger('FK_personal');
            $table->unsignedBigInteger('FK_servicio');

            $table->timestamps();

            // 🔥 FOREIGN KEYS
            $table->foreign('FK_personal')
                ->references('idPersonal')
                ->on('personal')
                ->onDelete('cascade');

            $table->foreign('FK_servicio')
                ->references('idServicio')
                ->on('servicio')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_servicio');
    }
};