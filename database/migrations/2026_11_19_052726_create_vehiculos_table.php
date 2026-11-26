<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('patente', 10)->unique();
            $table->string('marca_modelo', 100);
            $table->enum('tipo_vehiculo', ['CARROZA_PANORAMICA', 'FURGON_TRASLADO', 'VEHICULO_ACOMPANAMIENTO']);
            $table->unsignedSmallInteger('anio')->nullable();
            $table->unsignedInteger('kilometraje')->default(0);
            $table->enum('estado_vehiculo', ['DISPONIBLE', 'EN_SERVICIO', 'MANTENCION', 'INACTIVO'])->default('DISPONIBLE');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};