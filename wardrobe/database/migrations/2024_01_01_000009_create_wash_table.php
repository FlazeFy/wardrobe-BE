<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wash', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clothes_id', 36);
            $table->string('wash_note', 75)->nullable();
            $table->string('wash_type', 36);
            $table->longText('wash_checkpoint');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('finished_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clothes_id')->references('id')->on('clothes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wash');
    }
};
