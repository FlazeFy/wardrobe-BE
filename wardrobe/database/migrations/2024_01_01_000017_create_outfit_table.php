<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('outfit_name', 36);
            $table->string('outfit_note', 255)->nullable();
            $table->boolean('is_auto');
            $table->boolean('is_favorite');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit');
    }
};
