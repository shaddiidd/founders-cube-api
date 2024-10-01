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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('url');
            $table->string('company_business_number');
            $table->string('years_of_experience');
            $table->string('business_outline');
            $table->string('educational_background');
            $table->string('professional_affiliations');
            $table->string('strengths');
            $table->string('reasons_to_join');
            $table->string('referred_by')->nullable();
            $table->string('additional_comment');
            $table->boolean('accepted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
