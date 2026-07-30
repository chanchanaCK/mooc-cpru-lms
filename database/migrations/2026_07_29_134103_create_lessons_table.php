<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            // course_id is denormalised so player queries don't need to join sections
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['video', 'article', 'quiz'])->default('video');

            // Video lessons
            $table->enum('video_provider', ['youtube', 'vimeo', 'bunny', 'file'])->nullable();
            $table->string('video_id')->nullable();          // provider id or file path
            $table->unsignedInteger('duration_seconds')->default(0);

            // Article lessons
            $table->longText('content')->nullable();

            $table->boolean('is_preview')->default(false);   // watchable before enrolling
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'sort_order']);
            $table->index(['section_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
