
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('role', 30)->default('pelapor');

            $table->unsignedInteger('instansi_id')->nullable();
            $table->unsignedInteger('rumah_sakit_id')->nullable();

            $table->foreign('instansi_id')
                ->references('instansi_id')
                ->on('instansi')
                ->restrictOnDelete();

            $table->foreign('rumah_sakit_id')
                ->references('rumah_sakit_id')
                ->on('rumah_sakit')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
            $table->dropForeign(['rumah_sakit_id']);

            $table->dropColumn([
                'role',
                'instansi_id',
                'rumah_sakit_id',
            ]);
        });
    }
};
