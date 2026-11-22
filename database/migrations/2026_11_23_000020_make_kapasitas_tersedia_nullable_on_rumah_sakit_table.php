<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->integer('kapasitas_tersedia')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('rumah_sakit')
            ->whereNull('kapasitas_tersedia')
            ->update(['kapasitas_tersedia' => 0]);

        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->integer('kapasitas_tersedia')
                ->nullable(false)
                ->change();
        });
    }
};
