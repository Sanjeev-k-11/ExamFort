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
        // 1. Organizations Table
        if (!Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('name', 255);
                $table->string('code', 50)->unique();
                $table->string('type', 50)->default('College'); // University, College, School, Board
                $table->string('logo_url', 500)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('website', 255)->nullable();
                $table->text('address')->nullable();
                $table->integer('max_teachers_allowed')->default(50);
                $table->integer('max_students_allowed')->default(5000);
                $table->integer('max_exams_allowed')->default(100);
                $table->string('status', 20)->default('ACTIVE'); // ACTIVE, SUSPENDED
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 2. Contact Inquiries Table (For Public Contact Page)
        if (!Schema::hasTable('contact_inquiries')) {
            Schema::create('contact_inquiries', function (Blueprint $table) {
                $table->id();
                $table->string('full_name', 150);
                $table->string('email', 150);
                $table->string('phone', 50)->nullable();
                $table->string('organization_name', 255)->nullable();
                $table->string('subject', 255);
                $table->text('message');
                $table->string('status', 20)->default('PENDING'); // PENDING, RESOLVED
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Add org_id & principal_id to users and exams
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'org_id')) {
                $table->string('org_id', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'created_by_principal_id')) {
                $table->string('created_by_principal_id', 50)->nullable()->after('created_by_teacher_id');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'org_id')) {
                $table->string('org_id', 50)->nullable()->after('exam_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('contact_inquiries');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['org_id', 'created_by_principal_id']);
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('org_id');
        });
    }
};
