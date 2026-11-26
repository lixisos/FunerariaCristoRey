<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE bitacora_auditoria DROP CONSTRAINT IF EXISTS bitacora_auditoria_accion_check');
            DB::statement('ALTER TABLE bitacora_auditoria ALTER COLUMN accion TYPE VARCHAR(50)');
        } else {
            Schema::table('bitacora_auditoria', function (Blueprint $table): void {
                $table->string('accion', 50)->change();
            });
        }

        Schema::table('expedientes', function (Blueprint $table): void {
            $table->index('estado_expediente', 'idx_expedientes_estado');
            $table->index('created_at', 'idx_expedientes_created_at');
            $table->index('saldo_pendiente', 'idx_expedientes_saldo_pendiente');
            $table->index('limite_legal_seremi_at', 'idx_expedientes_limite_seremi');
        });

        Schema::table('abonos_pagos', function (Blueprint $table): void {
            $table->index(['estado_pago', 'fecha_pago'], 'idx_abonos_estado_fecha');
        });

        Schema::table('bitacora_auditoria', function (Blueprint $table): void {
            $table->index('created_at', 'idx_bitacora_created_at');
            $table->index(['tabla_afectada', 'accion'], 'idx_bitacora_tabla_accion');
        });

        Schema::table('urnas', function (Blueprint $table): void {
            $table->index('estado_urna', 'idx_urnas_estado');
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table): void {
            $table->dropIndex('idx_expedientes_estado');
            $table->dropIndex('idx_expedientes_created_at');
            $table->dropIndex('idx_expedientes_saldo_pendiente');
            $table->dropIndex('idx_expedientes_limite_seremi');
        });

        Schema::table('abonos_pagos', function (Blueprint $table): void {
            $table->dropIndex('idx_abonos_estado_fecha');
        });

        Schema::table('bitacora_auditoria', function (Blueprint $table): void {
            $table->dropIndex('idx_bitacora_created_at');
            $table->dropIndex('idx_bitacora_tabla_accion');
        });

        Schema::table('urnas', function (Blueprint $table): void {
            $table->dropIndex('idx_urnas_estado');
        });
    }
};
