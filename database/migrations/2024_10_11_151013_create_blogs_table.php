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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('title');
            $table->text('content');
            $table->string('slug');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_url')->nullable();
            $table->string('image_thumbnail_url')->nullable();
            $table->json('categories')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
