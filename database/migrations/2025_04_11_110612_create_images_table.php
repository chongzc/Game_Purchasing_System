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
        Schema::create('images', function (Blueprint $table) {
            $table->id('img_id'); // Primary key
            $table->binary('img_file'); // Column to store the file (binary data)
            $table->string('img_filename'); // Name of the file (e.g., 'profile-picture.png')
            $table->string('img_filetype'); // Type of the file (e.g., 'jpg', 'png', etc.)
            $table->unsignedBigInteger('img_filesize')->nullable(); // Size of the file in bytes
            $table->timestamps(); // For created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
