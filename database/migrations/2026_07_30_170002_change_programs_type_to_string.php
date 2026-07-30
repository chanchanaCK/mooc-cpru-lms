<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Programs.type was a fixed ENUM; make it a plain string so admins can
     * define new qualification types (stored as the type's slug).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE programs MODIFY type VARCHAR(40) NOT NULL DEFAULT 'certificate'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE programs MODIFY type ENUM('certificate', 'micro', 'diploma', 'degree') NOT NULL DEFAULT 'certificate'");
    }
};
