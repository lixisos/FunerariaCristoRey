<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fallecidos', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 15)->nullable();
            $table->string('nombre_completo', 150);
            $table->date('fecha_nacimiento')->nullable();
            $table->dateTime('fecha_fallecimiento');
            $table->string('lugar_fallecimiento', 180);
            $table->string('comuna_defuncion', 80)->default('Temuco');
            $table->string('causa_muerte_preliminar', 200)->nullable();
            $table->boolean('intervencion_sml')->default(false);
            $table->string('numero_oficio_fiscalia', 60)->nullable();
            $table->enum('estado_fallecido', ['HABILITADO', 'INACTIVO'])->default('HABILITADO');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fallecidos');
    }
};