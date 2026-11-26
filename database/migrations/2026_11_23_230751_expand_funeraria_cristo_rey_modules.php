<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fallecidos', function (Blueprint $table) {
            if (! Schema::hasColumn('fallecidos', 'escolaridad')) {
                $table->string('escolaridad', 80)->nullable();
            }
            if (! Schema::hasColumn('fallecidos', 'profesion')) {
                $table->string('profesion', 100)->nullable();
            }
            if (! Schema::hasColumn('fallecidos', 'estado_civil')) {
                $table->string('estado_civil', 40)->nullable();
            }
        });

        Schema::table('urnas', function (Blueprint $table) {
            if (! Schema::hasColumn('urnas', 'precio_compra')) {
                $table->integer('precio_compra')->default(0);
            }
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            if (! Schema::hasColumn('vehiculos', 'tipo_vehiculo')) {
                $table->string('tipo_vehiculo', 40)->default('CARROZA');
            }
        });

        Schema::table('expedientes', function (Blueprint $table) {
            if (! Schema::hasColumn('expedientes', 'ayudante_id')) {
                $table->foreignId('ayudante_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('expedientes', 'fecha_instalacion')) {
                $table->timestamp('fecha_instalacion')->nullable();
            }
            if (! Schema::hasColumn('expedientes', 'fecha_misa')) {
                $table->timestamp('fecha_misa')->nullable();
            }
            if (! Schema::hasColumn('expedientes', 'lugar_misa')) {
                $table->string('lugar_misa', 180)->nullable();
            }
            if (! Schema::hasColumn('expedientes', 'fecha_retiro_carroza')) {
                $table->timestamp('fecha_retiro_carroza')->nullable();
            }
            if (! Schema::hasColumn('expedientes', 'fecha_vencimiento_pago')) {
                $table->date('fecha_vencimiento_pago')->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tipo_ausencia')) {
                $table->string('tipo_ausencia', 30)->default('ACTIVO');
            }
            if (! Schema::hasColumn('users', 'ausencia_hasta')) {
                $table->date('ausencia_hasta')->nullable();
            }
        });

        Schema::table('bitacora_auditoria', function (Blueprint $table) {
            if (! Schema::hasColumn('bitacora_auditoria', 'modulo')) {
                $table->string('modulo', 60)->nullable();
            }
            if (! Schema::hasColumn('bitacora_auditoria', 'valores_anteriores_json')) {
                $table->jsonb('valores_anteriores_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Migración incremental segura
    }
};