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
        // 1. Ensure users table columns
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'org_id')) {
                $table->string('org_id', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'max_students_allowed')) {
                $table->integer('max_students_allowed')->default(100)->after('current_streak_days');
            }
            if (!Schema::hasColumn('users', 'max_exams_allowed')) {
                $table->integer('max_exams_allowed')->default(10)->after('max_students_allowed');
            }
            if (!Schema::hasColumn('users', 'created_by_teacher_id')) {
                $table->string('created_by_teacher_id', 50)->nullable()->after('max_exams_allowed');
            }
            if (!Schema::hasColumn('users', 'created_by_principal_id')) {
                $table->string('created_by_principal_id', 50)->nullable()->after('created_by_teacher_id');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 100)->default('Assistant Professor')->after('created_by_principal_id');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 100)->default('Computer Science & Engineering')->after('designation');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('ACTIVE')->after('department');
            }
            if (!Schema::hasColumn('users', 'gemini_api_key')) {
                $table->string('gemini_api_key', 255)->nullable()->after('avatar_url');
            }
        });

        // 2. Ensure exams table columns
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!Schema::hasColumn('exams', 'org_id')) {
                    $table->string('org_id', 50)->nullable()->after('exam_code');
                }
                if (!Schema::hasColumn('exams', 'created_by_teacher_id')) {
                    $table->string('created_by_teacher_id', 50)->nullable()->after('is_results_published');
                }
                if (!Schema::hasColumn('exams', 'college_name')) {
                    $table->string('college_name', 255)->nullable()->after('created_by_teacher_id');
                }
            });
        }

        // 3. Ensure organizations table
        if (!Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('name', 255);
                $table->string('code', 50)->unique();
                $table->string('type', 50)->default('College');
                $table->string('logo_url', 500)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('website', 255)->nullable();
                $table->text('address')->nullable();
                $table->integer('max_teachers_allowed')->default(50);
                $table->integer('max_students_allowed')->default(5000);
                $table->integer('max_exams_allowed')->default(100);
                $table->string('gemini_api_key', 255)->nullable();
                $table->string('status', 20)->default('ACTIVE');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for safety
    }
};
