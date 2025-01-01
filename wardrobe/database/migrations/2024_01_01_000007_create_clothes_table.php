<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clothes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clothes_name', 36);
            $table->string('clothes_desc', 255)->nullable();
            $table->string('clothes_merk', 75)->nullable();
            $table->string('clothes_size', 3);
            $table->string('clothes_gender', 6);
            $table->string('clothes_made_from', 36);
            $table->string('clothes_color', 36);
            $table->string('clothes_category', 36);
            $table->string('clothes_type', 36);
            $table->integer('clothes_price')->length(9)->nullable();
            $table->date('clothes_buy_at', $precision = 0)->nullable();
            $table->integer('clothes_qty')->length(3);
            $table->boolean('is_faded');
            $table->boolean('has_washed');
            $table->boolean('has_ironed');
            $table->boolean('is_favorite');
            $table->boolean('is_scheduled');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();
            $table->dateTime('deleted_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clothes_made_from')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
            $table->foreign('clothes_category')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
            $table->foreign('clothes_type')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clothes');
    }
};
