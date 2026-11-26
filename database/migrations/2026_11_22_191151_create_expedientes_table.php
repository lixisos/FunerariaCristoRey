<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 25)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('fallecido_id')->constrained('fallecidos')->restrictOnDelete();
            $table->foreignId('urna_id')->constrained('urnas')->restrictOnDelete();
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->nullOnDelete();
            $table->foreignId('chofer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('convenio_id')->nullable()->constrained('convenios_previsionales')->nullOnDelete();
            $table->enum('tipo_servicio', [
                'VELATORIO_Y_SEPULTACION',
                'CREMACION',
                'TRASLADO_REGIONAL',
                'TRASLADO_NACIONAL'
            ])->default('VELATORIO_Y_SEPULTACION');
            $table->string('lugar_velatorio', 200);
            $table->dateTime('fecha_inicio_velatorio');
            $table->dateTime('fecha_cortejo');
            $table->string('cementerio_destino', 150);
            $table->jsonb('metadatos_ruta_json')->nullable();
            $table->dateTime('limite_legal_seremi_at')->nullable();
            $table->unsignedInteger('valor_total_servicio')->default(0);
            $table->unsignedInteger('monto_cobertura_convenio')->default(0);
            $table->unsignedInteger('saldo_pendiente')->default(0);
            $table->enum('estado_expediente', ['ABIERTO', 'EN_PROCESO', 'CERRADO', 'ANULADO_INACTIVO'])->default('ABIERTO');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};