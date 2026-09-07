<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'created_by_teacher_id')) {
                $table->string('created_by_teacher_id', 100)->nullable()->after('created_by_principal_id');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'created_by_teacher_id')) {
                $table->string('created_by_teacher_id', 100)->nullable()->after('is_results_published');
            }
            if (!Schema::hasColumn('exams', 'college_name')) {
                $table->string('college_name', 200)->nullable()->after('created_by_teacher_id');
            }
        });
    }

    public function down(): void
    {
    }
};
