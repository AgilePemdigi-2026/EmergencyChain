<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_case', function (Blueprint $table) {
            $table->increments('case_id');

            $table->unsignedInteger('lokasi_id');
            $table->unsignedInteger('pengguna_id');

            $table->string('jenis_kejadian', 100);
            $table->text('kondisi');
            $table->text('kebutuhan');
            $table->timestamp('waktu_laporan');
            $table->string('status_laporan', 30);

            $table->foreign('lokasi_id')
                ->references('lokasi_id')
                ->on('lokasi')
                ->restrictOnDelete();

            $table->foreign('pengguna_id')
                ->references('pengguna_id')
                ->on('pengguna')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_case');
    }
};