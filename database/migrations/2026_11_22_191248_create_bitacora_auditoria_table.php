<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_afectada', 60);
            $table->unsignedBigInteger('registro_id');
            $table->enum('accion', [
                'CREACION',
                'ACTUALIZACION',
                'CAMBIO_ESTADO_INACTIVO',
                'TRASPASO_STOCK',
                'EMISION_PDF'
            ]);
            $table->jsonb('valores_anteriores_json')->nullable();
            $table->jsonb('valores_nuevos_json')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_auditoria');
    }
};