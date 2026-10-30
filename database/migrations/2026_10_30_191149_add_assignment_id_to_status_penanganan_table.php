<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_penanganan', function (Blueprint $table) {
            $table->unsignedInteger('assignment_id')->nullable();

            $table->foreign('assignment_id')
                ->references('assignment_id')
                ->on('assignment')
                ->restrictOnDelete();

            $table->index('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('status_penanganan', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropIndex(['assignment_id']);
            $table->dropColumn('assignment_id');
        });
    }
};
