<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rumah_sakit', function (Blueprint $table) {
            $table->increments('rumah_sakit_id');
            $table->string('nama_rs', 100);
            $table->string('alamat', 100);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 10, 8);
            $table->integer('kapasitas');
            $table->integer('kapasitas_tersedia');
            $table->string('status', 30);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rumah_sakit');
    }
};
