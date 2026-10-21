<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('instansi_id');
            $table->string('nama_instansi', 100);
            $table->string('jenis_instansi', 100);
            $table->string('alamat', 100);
            $table->string('no_telp', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instansi');
    }
};
