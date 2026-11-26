<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urnas', function (Blueprint $table) {
            $table->json('galeria_fotos')->nullable()->after('foto_s3_url');
        });
    }

    public function down(): void
    {
        Schema::table('urnas', function (Blueprint $table) {
            $table->dropColumn('galeria_fotos');
        });
    }
};