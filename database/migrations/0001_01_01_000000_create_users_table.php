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
        Schema::create('users', function (Blueprint $table) {
            $table->id('u_id');
            $table->string('u_name');
            $table->string('u_email', 191)->unique();
            $table->string('u_password');
            $table->date('u_birthdate')->nullable();
            $table->enum('u_role', ['admin', 'developer', 'user'])->default('user');
            $table->string('u_profileImagePath')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
