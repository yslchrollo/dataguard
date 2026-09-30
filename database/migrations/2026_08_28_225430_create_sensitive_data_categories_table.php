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
        Schema::create('sensitive_data_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // PII, FINANCIAL, CREDENTIALS, CONFIDENTIAL, SOURCE_CODE
            $table->text('description')->nullable();
            $table->string('risk_level')->default('high'); // low, medium, high, critical
            $table->json('patterns')->nullable(); // JSON list of keyword strings and regex expressions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensitive_data_categories');
    }
};
