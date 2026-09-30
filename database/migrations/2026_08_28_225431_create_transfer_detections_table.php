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
        Schema::create('transfer_detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('sensitive_data_categories')->nullOnDelete();
            $table->string('detection_technique'); // sensitive_data, unauthorized_destination, policy_violation, suspicious_file, unusual_transfer
            $table->string('rule_name');
            $table->string('matched_pattern')->nullable();
            $table->string('matched_sample')->nullable();
            $table->string('severity')->default('high'); // low, medium, high, critical
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_detections');
    }
};
