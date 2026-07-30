<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Credit-bank fields on courses. Courses are opt-in credit-bearing — the
     * marketplace-only courses keep credit_bearing = false and behave as before.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('course_code', 32)->nullable()->after('slug');           // รหัสวิชา เช่น CS101
            $table->boolean('credit_bearing')->default(false)->after('status');       // นับหน่วยกิตได้ไหม
            $table->decimal('credits', 4, 1)->default(0)->after('credit_bearing');     // จำนวนหน่วยกิต
            $table->unsignedInteger('learning_hours')->default(0)->after('credits');   // ชั่วโมงเรียนรู้
            $table->enum('grading_method', ['pass_fail', 'graded'])->default('pass_fail')->after('learning_hours');
            $table->unsignedTinyInteger('pass_threshold')->default(70)->after('grading_method'); // %เกณฑ์ผ่าน
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'course_code', 'credit_bearing', 'credits',
                'learning_hours', 'grading_method', 'pass_threshold',
            ]);
        });
    }
};
