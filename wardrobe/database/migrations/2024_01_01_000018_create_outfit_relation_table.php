<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outfit_relation', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->string('outfit_id', 36);
            $table->string('clothes_id', 36);

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('outfit_id')->references('id')->on('outfit')->onDelete('cascade');
            $table->foreign('clothes_id')->references('id')->on('clothes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outfit_relation');
    }
};
