<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios_previsionales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['IPS', 'AFP', 'FFAA_ORDEN', 'SINDICATO', 'PARTICULAR']);
            $table->unsignedInteger('monto_tope_clp')->default(0);
            $table->decimal('monto_tope_uf', 5, 2)->default(0);
            $table->boolean('requiere_resolucion')->default(true);
            $table->enum('estado_convenio', ['HABILITADO', 'INACTIVO'])->default('HABILITADO');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios_previsionales');
    }
};