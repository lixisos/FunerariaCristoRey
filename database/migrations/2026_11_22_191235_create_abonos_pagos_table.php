<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonos_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('folio_comprobante', 30)->unique();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->unsignedInteger('monto_abonado');
            $table->enum('metodo_pago', [
                'EFECTIVO',
                'TRANSFERENCIA',
                'TARJETA_DEBITO',
                'TARJETA_CREDITO',
                'CONVENIO_DIRECTO'
            ]);
            $table->dateTime('fecha_pago');
            $table->string('observacion', 255)->nullable();
            $table->enum('estado_pago', ['VALIDO', 'ANULADO_INACTIVO'])->default('VALIDO');
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonos_pagos');
    }
};