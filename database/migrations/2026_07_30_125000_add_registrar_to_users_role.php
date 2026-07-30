<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the `registrar` (นายทะเบียน) role to the users.role enum so the
     * credit-bank registrar area has a dedicated role.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'registrar') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin') NOT NULL DEFAULT 'student'");
    }
};
