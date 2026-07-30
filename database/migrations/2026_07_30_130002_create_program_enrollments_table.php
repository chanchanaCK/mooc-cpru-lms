<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** A learner's enrolment in a program + cached accumulated credits. */
    public function up(): void
    {
        Schema::create('program_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'completed', 'withdrawn'])->default('in_progress');
            $table->decimal('credits_earned', 5, 1)->default(0);       // cache
            $table->timestamp('completed_at')->nullable();
            $table->string('certificate_number')->nullable()->unique(); // เลขคุณวุฒิเมื่อจบ
            $table->timestamps();

            $table->unique(['user_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_enrollments');
    }
};
