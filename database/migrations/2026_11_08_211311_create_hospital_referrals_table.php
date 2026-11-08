<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('hospital_referrals', function (Blueprint $table) {
        $table->id();

        $table->unsignedInteger('case_id');

$table->foreign('case_id')
    ->references('case_id')
    ->on('emergency_case')
    ->restrictOnDelete();

        $table->string('status', 30)->default('mencari');

        $table->unsignedInteger('accepted_hospital_id')->nullable();

        $table->foreign('accepted_hospital_id')
            ->references('rumah_sakit_id')
            ->on('rumah_sakit')
            ->restrictOnDelete();

        $table->string('failure_reason')->nullable();

        $table->timestamps();

        $table->unique('case_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('hospital_referrals');
}
};
