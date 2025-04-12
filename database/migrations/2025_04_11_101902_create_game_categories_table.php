<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_categories', function (Blueprint $table) {
            $table->id('gc_id');
            $table->string('gc_gameName');
            $table->unsignedBigInteger('gc_gameId');
            $table->string('gc_category');
            $table->timestamps();

            $table->foreign('gc_gameId')->references('g_id')->on('games')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_categories');
    }
};
