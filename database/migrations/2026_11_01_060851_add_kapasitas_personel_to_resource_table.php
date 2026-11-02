<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource', function (Blueprint $table) {
            $table->unsignedInteger('kapasitas_personel')
                ->nullable()
                ->after('jenis_resource');
        });
    }

    public function down(): void
    {
        Schema::table('resource', function (Blueprint $table) {
            $table->dropColumn('kapasitas_personel');
        });
    }
};