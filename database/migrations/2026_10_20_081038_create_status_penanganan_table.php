<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_penanganan', function (Blueprint $table) {
            $table->increments('status_id');
            $table->unsignedInteger('case_id');

            $table->string('status', 30);
            $table->timestamp('waktu_update');
            $table->text('keterangan');

            $table->foreign('case_id')
                ->references('case_id')
                ->on('emergency_case')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_penanganan');
    }
};
