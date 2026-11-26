<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rut', 15)->unique()->nullable()->after('id');
            $table->string('telefono', 20)->nullable()->after('email');
            $table->foreignId('rol_id')->nullable()->after('telefono')->constrained('roles')->nullOnDelete();
            $table->boolean('en_vacaciones')->default(false)->after('rol_id');
            $table->foreignId('delegado_a_id')->nullable()->after('en_vacaciones')->constrained('users')->nullOnDelete();
            $table->enum('estado_usuario', ['HABILITADO', 'INACTIVO'])->default('HABILITADO')->after('delegado_a_id');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropForeign(['delegado_a_id']);
            $table->dropColumn([
                'rut', 'telefono', 'rol_id', 'en_vacaciones',
                'delegado_a_id', 'estado_usuario', 'deleted_at'
            ]);
        });
    }
};