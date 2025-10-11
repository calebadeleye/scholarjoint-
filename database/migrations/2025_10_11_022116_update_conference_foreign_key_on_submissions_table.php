<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Drop the old constraint if it still exists
            $table->dropForeign(['conference_id']); 

            // Now add the correct foreign key to the conferences table
            $table->foreign('conference_id')
                  ->references('id')
                  ->on('conferences')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['conference_id']);
            $table->foreign('conference_id')
                  ->references('id')
                  ->on('tracks')
                  ->onDelete('cascade');
        });
    }
};
