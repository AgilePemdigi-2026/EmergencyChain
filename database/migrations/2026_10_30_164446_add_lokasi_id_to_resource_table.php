
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource', function (Blueprint $table) {
            // Menambahkan foreign key lokasi pangkalan
            $table->unsignedInteger('lokasi_id')->nullable();

            $table->foreign('lokasi_id')
                ->references('lokasi_id')
                ->on('lokasi')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resource', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn('lokasi_id');
        });
    }
};
