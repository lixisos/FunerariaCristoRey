<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 15)->unique();
            $table->string('nombre_completo', 150);
            $table->string('telefono', 25);
            $table->string('email', 120)->nullable();
            $table->string('direccion', 255);
            $table->string('comuna', 80)->default('Temuco');
            $table->enum('estado_cliente', ['HABILITADO', 'INACTIVO'])->default('HABILITADO');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};