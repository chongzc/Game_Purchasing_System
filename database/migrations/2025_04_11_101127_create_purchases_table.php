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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id('p_id');
            $table->string('p_gameName');
            $table->unsignedBigInteger('p_userId');
            $table->unsignedBigInteger('p_gameId');
            $table->decimal('p_purchasePrice', 8, 2);
            $table->timestamp('p_purchaseDate')->useCurrent();
            $table->string('p_receiptNumber',191)->unique();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('p_userId')->references('u_id')->on('users')->onDelete('cascade');
            $table->foreign('p_gameId')->references('g_id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
