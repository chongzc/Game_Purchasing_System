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
        Schema::create('game_lib', function (Blueprint $table) {
            $table->id('gl_id'); 
            $table->string('gl_name'); 
            $table->unsignedBigInteger('gl_gameId'); 
            $table->unsignedBigInteger('gl_userId'); 
            $table->enum('gl_status', ['owned', 'installed', 'removed'])->default('owned'); // Status of the game in the library
            $table->timestamps(); 

            // Foreign keys
            $table->foreign('gl_userId')->references('u_id')->on('users')->onDelete('cascade');
            $table->foreign('gl_gameId')->references('g_id')->on('games')->onDelete('cascade');

            // Prevent multiple entries for the same user and game
            $table->unique(['gl_userId', 'gl_gameId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_lib');
    }
};
