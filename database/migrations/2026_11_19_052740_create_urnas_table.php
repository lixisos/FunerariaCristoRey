<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urnas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_sku', 30)->unique();
            $table->string('modelo', 120);
            $table->text('descripcion')->nullable();
            $table->enum('categoria_tamano', ['ESTANDAR', 'NINO_SEREMI', 'ESPECIAL_XL'])->default('ESTANDAR');
            $table->enum('acabado_tallado', ['LISA', 'PALOMA_BIBLIA', 'MARQUETEADA', 'PERSONALIZADA'])->default('LISA');
            $table->string('color', 50);
            $table->unsignedSmallInteger('largo_cm');
            $table->unsignedSmallInteger('ancho_cm');
            $table->unsignedSmallInteger('alto_cm');
            $table->unsignedInteger('precio_venta');
            $table->unsignedSmallInteger('stock_salon')->default(0);
            $table->unsignedSmallInteger('stock_taller')->default(0);
            $table->enum('proveedor_origen', ['SAN_JAVIER', 'SANTIAGO'])->default('SAN_JAVIER');
            $table->boolean('reservada_en_vida')->default(false);
            $table->string('rut_reserva_vida', 15)->nullable();
            $table->string('nombre_reserva_vida', 120)->nullable();
            $table->string('foto_s3_url', 255)->nullable();
            $table->enum('estado_urna', ['HABILITADO', 'INACTIVO'])->default('HABILITADO');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urnas');
    }
};