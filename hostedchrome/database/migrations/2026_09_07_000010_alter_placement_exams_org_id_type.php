<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('placement_exams') && Schema::hasColumn('placement_exams', 'org_id')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE placement_exams ALTER COLUMN org_id TYPE VARCHAR(100) USING org_id::varchar");
            } elseif ($driver === 'mysql') {
                DB::statement("ALTER TABLE `placement_exams` MODIFY `org_id` VARCHAR(100) NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
