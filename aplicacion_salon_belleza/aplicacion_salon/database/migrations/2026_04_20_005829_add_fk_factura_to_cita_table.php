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
        Schema::table('cita', function (Blueprint $table) {
            $table->string('FK_factura')->nullable()->after('FK_estadoCita');
            $table->foreign('FK_factura')
                  ->references('idFacturas')
                  ->on('factura')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cita', function (Blueprint $table) {
            $table->dropForeign(['FK_factura']);
            $table->dropColumn('FK_factura');
        });
    }
};
