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
        Schema::create('user_lib', function (Blueprint $table) {
            $table->id('ul_id'); 
            $table->string('ul_name'); 
            $table->unsignedBigInteger('ul_gameId'); 
            $table->unsignedBigInteger('ul_userId'); 
            $table->enum('ul_status', ['owned', 'installed', 'removed'])->default('owned'); // Status of the game in the library
            $table->timestamps(); 

            // Foreign keys
            $table->foreign('ul_userId')->references('u_id')->on('users')->onDelete('cascade');
            $table->foreign('ul_gameId')->references('g_id')->on('games')->onDelete('cascade');

            // Prevent multiple entries for the same user and game
            $table->unique(['ul_userId', 'ul_gameId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lib');
    }
};
