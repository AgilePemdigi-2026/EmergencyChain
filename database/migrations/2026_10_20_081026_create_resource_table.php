<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource', function (Blueprint $table) {
            $table->increments('resource_id');
            $table->unsignedInteger('instansi_id');

            $table->string('nama_resource', 100);
            $table->string('jenis_resource', 50);
            $table->string('lokasi', 255);
            $table->string('status_ketersediaan', 30);

            $table->foreign('instansi_id')
                ->references('instansi_id')
                ->on('instansi')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource');
    }
};
