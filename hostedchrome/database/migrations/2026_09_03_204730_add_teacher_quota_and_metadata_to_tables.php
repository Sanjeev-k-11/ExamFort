<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'max_students_allowed')) {
                $table->integer('max_students_allowed')->default(100)->after('current_streak_days');
            }
            if (!Schema::hasColumn('users', 'max_exams_allowed')) {
                $table->integer('max_exams_allowed')->default(10)->after('max_students_allowed');
            }
            if (!Schema::hasColumn('users', 'created_by_teacher_id')) {
                $table->string('created_by_teacher_id', 50)->nullable()->after('max_exams_allowed');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 100)->default('Assistant Professor')->after('created_by_teacher_id');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 100)->default('Computer Science & Engineering')->after('designation');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('ACTIVE')->after('department');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'created_by_teacher_id')) {
                $table->string('created_by_teacher_id', 50)->nullable()->after('is_results_published');
            }
            if (!Schema::hasColumn('exams', 'college_name')) {
                $table->string('college_name', 255)->nullable()->after('created_by_teacher_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['max_students_allowed', 'max_exams_allowed', 'created_by_teacher_id', 'designation', 'department', 'status']);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['created_by_teacher_id', 'college_name']);
        });
    }
};
