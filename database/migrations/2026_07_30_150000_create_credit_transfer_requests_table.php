<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Credit transfer / RPL requests (คำขอเทียบโอนหน่วยกิต) — a learner asks to
     * bank credits earned elsewhere; a registrar approves and it becomes a
     * credit_records row.
     */
    public function up(): void
    {
        Schema::create('credit_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete(); // target program (optional)
            $table->enum('source_type', ['institution', 'experience', 'other'])->default('institution');
            $table->string('source_name');       // ชื่อสถาบัน/ที่มา
            $table->string('course_name');        // ชื่อวิชา/ความสามารถ
            $table->decimal('credits_requested', 4, 1);
            $table->text('evidence_note')->nullable();
            $table->string('evidence_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->decimal('credits_awarded', 4, 1)->nullable();
            $table->string('grade', 4)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('credit_record_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transfer_requests');
    }
};
