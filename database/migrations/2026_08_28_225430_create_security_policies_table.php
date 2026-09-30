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
        Schema::create('security_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_code')->unique(); // e.g. POL-DLP-001
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('policy_type'); // sensitive_data_rule, destination_check, file_type_restriction, file_size_limit, rate_limit
            $table->string('action_on_violation')->default('block'); // block, flag
            $table->string('severity')->default('high'); // low, medium, high, critical
            $table->json('rule_config')->nullable(); // rules/thresholds/extensions/patterns
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_policies');
    }
};
