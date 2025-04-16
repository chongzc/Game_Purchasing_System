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
        Schema::create('games', function (Blueprint $table) {
            $table->id('g_id');
            $table->string('g_title');
            $table->text('g_description')->nullable();
            $table->decimal('g_price', 10, 2);
            $table->decimal('g_discount', 5, 2)->default(0.00);
            $table->unsignedBigInteger('g_developerId');
            $table->enum('g_status', ['verified', 'reported', 'pending', 'removed'])->default('pending');
            $table->integer('g_downloadCount')->default(0);
            $table->string('g_mainImage')->nullable();
            $table->string('g_exImg1')->nullable();
            $table->string('g_exImg2')->nullable();
            $table->string('g_exImg3')->nullable();
            $table->decimal('g_overallRate', 3, 2)->default(0.00);
            $table->string('g_language')->nullable();
            $table->string('g_category')->nullable();
            $table->timestamps();
            
            // Update foreign key to reference users table instead of developers
            $table->foreign('g_developerId')->references('u_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
