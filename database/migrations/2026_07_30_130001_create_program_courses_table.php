<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Which courses count toward a program, and whether required or elective. */
    public function up(): void
    {
        Schema::create('program_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->enum('requirement', ['required', 'elective'])->default('required');
            $table->unsignedInteger('sort_order')->default(0);
            $table->decimal('credits_override', 4, 1)->nullable(); // credits within this program, if different
            $table->timestamps();

            $table->unique(['program_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_courses');
    }
};
