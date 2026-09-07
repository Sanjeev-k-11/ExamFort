<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 50)->default('ACTIVE')->after('role');
            }
            if (!Schema::hasColumn('users', 'org_id')) {
                $table->string('org_id', 100)->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 150)->nullable()->after('org_id');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 150)->nullable()->after('designation');
            }
            if (!Schema::hasColumn('users', 'max_students_allowed')) {
                $table->integer('max_students_allowed')->default(5000)->after('department');
            }
            if (!Schema::hasColumn('users', 'max_exams_allowed')) {
                $table->integer('max_exams_allowed')->default(150)->after('max_students_allowed');
            }
            if (!Schema::hasColumn('users', 'gemini_api_key')) {
                $table->text('gemini_api_key')->nullable()->after('max_exams_allowed');
            }
            if (!Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        // No down needed
    }
};
