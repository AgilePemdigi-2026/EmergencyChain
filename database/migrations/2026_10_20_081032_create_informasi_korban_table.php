<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informasi_korban', function (Blueprint $table) {
            $table->increments('informasi_id');
            $table->unsignedInteger('rumah_sakit_id');
            $table->unsignedInteger('case_id');

            $table->string('kondisi_korban', 100);
            $table->timestamp('waktu_rujukan')->nullable();
            $table->string('status_penerimaan', 30);

            $table->foreign('rumah_sakit_id')
                ->references('rumah_sakit_id')
                ->on('rumah_sakit')
                ->restrictOnDelete();

            $table->foreign('case_id')
                ->references('case_id')
                ->on('emergency_case')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informasi_korban');
    }
};
