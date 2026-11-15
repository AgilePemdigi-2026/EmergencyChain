<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_treatment_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('informasi_id');

            $table->string('status', 50);
            $table->text('keterangan')->nullable();

            $table->unsignedInteger('diubah_oleh')->nullable();

            $table->timestamps();

            $table->foreign('informasi_id')
                ->references('informasi_id')
                ->on('informasi_korban')
                ->restrictOnDelete();

            $table->foreign('diubah_oleh')
                ->references('pengguna_id')
                ->on('pengguna')
                ->nullOnDelete();

            $table->index(['informasi_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_treatment_histories');
    }
};
