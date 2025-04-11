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
        Schema::create('wishlist', function (Blueprint $table) {
            $table->id('wl_id');
            $table->string('wl_name'); 
            $table->unsignedBigInteger('wl_gameId'); 
            $table->unsignedBigInteger('wl_userId'); 
            $table->timestamps(); 

            // Foreign keys
            $table->foreign('wl_userId')->references('u_id')->on('users')->onDelete('cascade');
            $table->foreign('wl_gameId')->references('g_id')->on('games')->onDelete('cascade');

            // Prevent multiple entries for the same user and game
            $table->unique(['wl_userId', 'wl_gameId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};
