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
    Schema::create('hospital_referral_requests', function (Blueprint $table) {
        $table->id();

        $table->foreignId('hospital_referral_id')
            ->constrained('hospital_referrals')
            ->cascadeOnDelete();

        $table->unsignedInteger('rumah_sakit_id');

        $table->foreign('rumah_sakit_id')
            ->references('rumah_sakit_id')
            ->on('rumah_sakit')
            ->restrictOnDelete();

        $table->unsignedInteger('attempt_order');

        $table->string('status', 30)->default('menunggu');

        $table->timestamp('requested_at');
        $table->timestamp('expires_at');
        $table->timestamp('responded_at')->nullable();

        $table->text('rejection_reason')->nullable();

        $table->timestamps();

        $table->unique([
            'hospital_referral_id',
            'rumah_sakit_id',
        ]);

        $table->unique([
            'hospital_referral_id',
            'attempt_order',
        ]);
    });
}

public function down(): void
{
    Schema::dropIfExists('hospital_referral_requests');
}
};
