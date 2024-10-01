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
        Schema::create('verification_entries', function (Blueprint $table) {
            $table->id();
            $table->string('verification_token');
            $table->string('email');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vertification_entries');
    }
};
