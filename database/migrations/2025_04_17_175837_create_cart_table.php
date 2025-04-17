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
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('c_userId');
            $table->unsignedBigInteger('c_gameId');
            $table->timestamps();
            $table->decimal('c_price', 10, 2);
            $table->integer('c_discount');
            $table->foreign('c_userId')->references('u_id')->on('users')->onDelete('cascade');
            $table->foreign('c_gameId')->references('g_id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};
