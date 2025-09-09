<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('question', 500);
            $table->string('answer', 500)->nullable();
            $table->boolean('is_show');

            // Props
            $table->dateTime('created_at', $precision = 0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question');
    }
};
