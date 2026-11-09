
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emergency_case', function (Blueprint $table) {
            $table->string('hospital_referral_verification', 30)
                ->nullable();

            $table->unsignedInteger('hospital_referral_verified_by')
                ->nullable();

            $table->timestamp('hospital_referral_verified_at')
                ->nullable();

            $table->foreign('hospital_referral_verified_by')
                ->references('pengguna_id')
                ->on('pengguna')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('emergency_case', function (Blueprint $table) {
            $table->dropForeign(['hospital_referral_verified_by']);

            $table->dropColumn([
                'hospital_referral_verification',
                'hospital_referral_verified_by',
                'hospital_referral_verified_at',
            ]);
        });
    }
};
