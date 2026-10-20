<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment', function (Blueprint $table) {
            $table->increments('assignment_id');
            $table->unsignedInteger('resource_id');
            $table->unsignedInteger('case_id');

            $table->timestamp('waktu_assign');
            $table->string('status_assign', 30);
            $table->timestamp('waktu_konfirmasi')->nullable();

            $table->foreign('resource_id')
                ->references('resource_id')
                ->on('resource')
                ->restrictOnDelete();

            $table->foreign('case_id')
                ->references('case_id')
                ->on('emergency_case')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment');
    }
};
