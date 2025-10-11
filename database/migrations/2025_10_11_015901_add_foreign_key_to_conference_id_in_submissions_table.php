<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Add the foreign key if it doesn't already exist
            if (Schema::hasColumn('submissions', 'conference_id')) {
                $table->foreign('conference_id')
                      ->references('id')
                      ->on('conferences')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Drop the foreign key constraint if it exists
            $table->dropForeign(['conference_id']);
        });
    }
};
