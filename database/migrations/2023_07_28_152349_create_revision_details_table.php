<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revision_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('revision_id');
            $table->string('title');
            $table->string('content');
            $table->string('lang')->default('en');
            $table->foreign('revision_id')->references('id')->on('revisions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_details');
    }
};
