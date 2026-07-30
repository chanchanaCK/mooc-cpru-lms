<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Admin-managed qualification types (ประเภทคุณวุฒิ) used by programs. */
    public function up(): void
    {
        Schema::create('qualification_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();   // stored on programs.type
            $table->string('name');                 // ชื่อที่แสดง
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualification_types');
    }
};
