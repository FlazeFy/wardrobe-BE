<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_weather', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->double('weather_temp');
            $table->double('weather_humid');
            $table->string('weather_city', 75);
            $table->string('weather_condition', 16);
            $table->string('weather_hit_from', 36);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('weather_condition')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
            $table->foreign('weather_hit_from')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_weather');
    }
};
