<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_track', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('track_lat', 255);
            $table->string('track_long', 255);
            $table->string('track_source', 16);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_track');
    }
};
