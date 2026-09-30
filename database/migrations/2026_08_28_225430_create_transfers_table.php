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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_uuid')->unique(); // e.g. TRF-20260828-ABCD
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('original_file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->default('document'); // document, code, archive, binary, image, text
            $table->string('file_extension')->nullable();
            $table->bigInteger('file_size_bytes')->default(0);
            $table->string('destination'); // e.g. external-dump@gmail.com or internal.hr@company.com
            $table->string('destination_type')->default('external_unapproved'); // internal, external_approved, external_unapproved
            $table->string('purpose');
            $table->text('description')->nullable();
            $table->text('extracted_text_sample')->nullable();
            $table->string('decision')->default('allowed'); // allowed, blocked, flagged
            $table->integer('risk_score')->default(0); // 0 - 100
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->text('decision_reason')->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
