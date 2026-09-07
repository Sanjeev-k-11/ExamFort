<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'CANDIDATE'");
            DB::statement("ALTER TABLE `users` MODIFY `student_id` VARCHAR(100) NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50), ALTER COLUMN role SET DEFAULT 'CANDIDATE'");
            DB::statement("ALTER TABLE users ALTER COLUMN student_id TYPE VARCHAR(100), ALTER COLUMN student_id DROP NOT NULL");
        }
    }

    public function down(): void
    {
    }
};
