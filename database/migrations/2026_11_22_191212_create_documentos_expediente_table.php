<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_expediente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->enum('tipo_documento', [
                'CERTIFICADO_DEFUNCION',
                'OFICIO_FISCALIA_SML',
                'RESOLUCION_SEREMI',
                'CONTRATO_FIRMADO',
                'HOJA_MEDIDAS_CEMENTERIO',
                'OTRO'
            ]);
            $table->string('nombre_archivo', 180);
            $table->string('ruta_s3', 255);
            $table->boolean('validado')->default(false);
            $table->foreignId('subido_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_expediente');
    }
};