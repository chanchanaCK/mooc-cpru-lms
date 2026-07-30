<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Programs (หลักสูตรสะสมหน่วยกิต) — a stackable qualification: learners
     * accumulate credits from its courses until they meet `required_credits`.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('type', ['certificate', 'micro', 'diploma', 'degree'])->default('certificate');
            $table->unsignedTinyInteger('nqf_level')->nullable();      // ระดับคุณวุฒิ (กรอบ TQF)
            $table->decimal('required_credits', 5, 1)->default(0);
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
