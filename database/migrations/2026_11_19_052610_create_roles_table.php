<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('slug', 40)->unique();
            $table->text('descripcion')->nullable();
            $table->enum('estado_rol', ['HABILITADO', 'INACTIVO'])->default('HABILITADO');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};