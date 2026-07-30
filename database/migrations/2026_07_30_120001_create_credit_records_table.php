<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The credit bank ledger (คลังหน่วยกิต): one row per credit-earning event.
     * Values are snapshotted so a course later changing its credits never
     * rewrites history.
     */
    public function up(): void
    {
        Schema::create('credit_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // null = external transfer/RPL
            $table->enum('source', ['course', 'transfer', 'manual'])->default('course');
            $table->string('title');                             // snapshot of course / external course name
            $table->string('code', 32)->nullable();              // snapshot of course_code
            $table->decimal('credits', 4, 1)->default(0);
            $table->string('grade', 4)->nullable();              // S/U หรือ A..F
            $table->unsignedTinyInteger('score_percent')->nullable();
            $table->enum('status', ['earned', 'pending', 'revoked', 'expired'])->default('earned');
            $table->timestamp('earned_at')->nullable();
            $table->timestamp('expires_at')->nullable();         // หน่วยกิตมีอายุ (แสดงผล)
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'course_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_records');
    }
};
