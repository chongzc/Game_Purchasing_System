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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('r_id');
            $table->unsignedBigInteger('r_userId');
            $table->unsignedBigInteger('r_gameId');
            $table->text('r_reviewText')->nullable();
            $table->integer('r_rating'); // Numeric rating (e.g., 1-5)
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('r_userId')->references('u_id')->on('users')->onDelete('cascade');
            $table->foreign('r_gameId')->references('g_id')->on('games')->onDelete('cascade');
            
            // Prevent multiple reviews by the same user for the same game
            $table->unique(['r_userId', 'r_gameId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
