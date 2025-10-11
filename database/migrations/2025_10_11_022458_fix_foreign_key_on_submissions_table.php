<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Drop the wrong foreign key if it exists
            DB::statement('ALTER TABLE submissions DROP FOREIGN KEY submissions_track_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Re-add it only if you ever revert
            $table->foreign('conference_id')->references('id')->on('tracks')->onDelete('cascade');
        });
    }
};
