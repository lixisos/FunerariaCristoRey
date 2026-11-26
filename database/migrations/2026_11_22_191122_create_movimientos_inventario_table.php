<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('urna_id')->constrained('urnas')->cascadeOnDelete();
            $table->enum('tipo_movimiento', [
                'INGRESO_PROVEEDOR',
                'TRASPASO_TALLER_SALON',
                'REBAJA_VENTA',
                'AJUSTE_INVENTARIO'
            ]);
            $table->unsignedSmallInteger('cantidad');
            $table->string('origen', 60)->nullable();
            $table->string('destino', 60)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};