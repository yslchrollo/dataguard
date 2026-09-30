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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_uuid')->unique(); // e.g. ALT-2026-ABCD
            $table->foreignId('transfer_id')->constrained('transfers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('alert_type'); // Sensitive Data Exfiltration, Unauthorized Destination, Policy Violation, Suspicious File, Anomalous Transfer
            $table->string('severity')->default('high'); // low, medium, high, critical
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('open'); // open, under_review, resolved, dismissed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('investigation_notes')->nullable();
            $table->string('action_taken')->nullable(); // Block Maintained, Exception Granted, User Retrained, Policy Updated
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
