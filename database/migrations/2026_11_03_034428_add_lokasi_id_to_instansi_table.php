<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instansi', function (Blueprint $table) {
            $table->unsignedInteger('lokasi_id')->nullable();

            $table->foreign('lokasi_id')
                ->references('lokasi_id')
                ->on('lokasi')
                ->restrictOnDelete();
        });

        // Hubungkan setiap Instansi dengan lokasi Resource-nya.
        DB::statement("
            UPDATE instansi AS i
            SET lokasi_id = (
                SELECT r.lokasi_id
                FROM resource AS r
                WHERE r.instansi_id = i.instansi_id
                  AND r.lokasi_id IS NOT NULL
                ORDER BY r.resource_id
                LIMIT 1
            )
        ");

        // Pastikan seluruh Instansi sudah memiliki lokasi.
        if (DB::table('instansi')->whereNull('lokasi_id')->exists()) {
            throw new RuntimeException(
                'Terdapat Instansi yang belum memiliki lokasi pangkalan.'
            );
        }

        Schema::table('instansi', function (Blueprint $table) {
            $table->unsignedInteger('lokasi_id')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('instansi', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn('lokasi_id');
        });
    }
};
