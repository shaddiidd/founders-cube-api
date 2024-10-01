<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();;
            $table->string('url');
            $table->string('industry')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('password')->nullable();
            $table->string('subscription')->nullable();
            $table->boolean('verified')->default(0);
            $table->boolean('active')->default(0);
            $table->string('user_type')->default('user');
            $table->timestamp('email_verified_at')->nullable();
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
