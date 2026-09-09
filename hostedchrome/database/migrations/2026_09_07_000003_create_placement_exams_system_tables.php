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
        // 1. PLACEMENT EXAMS / DRIVES TABLE
        Schema::create('placement_exams', function (Blueprint $table) {
            $table->id();
            $table->string('exam_code', 50)->unique();
            $table->string('title', 255);
            $table->string('company_name', 150);
            $table->string('company_logo_url', 255)->nullable();
            $table->string('job_role', 150);
            $table->string('package_lpa', 50)->default('6.5 LPA');
            $table->decimal('min_cgpa', 4, 2)->default(6.00);
            $table->text('eligibility_criteria')->nullable();
            $table->text('description')->nullable();
            $table->string('drive_type', 50)->default('ON_CAMPUS'); // ON_CAMPUS, POOL_CAMPUS, OFF_CAMPUS
            $table->string('exam_date', 50)->default('15 Oct 2026');
            $table->string('start_time', 20)->default('10:00 AM');
            $table->string('end_time', 20)->default('01:00 PM');
            $table->integer('duration_minutes')->default(90);
            $table->integer('total_marks')->default(100);
            $table->integer('total_questions')->default(0);
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'ACTIVE', 'COMPLETED', 'ARCHIVED'])->default('SCHEDULED');
            $table->boolean('face_verification_required')->default(true);
            $table->boolean('is_code_only')->default(true); // Hidden from normal catalog, unlocks only via 6-digit code
            $table->boolean('is_results_published')->default(false);
            $table->string('created_by_principal_id', 50)->nullable();
            $table->string('org_id', 100)->nullable();
            $table->string('college_name', 255)->nullable();
            $table->timestamps();

            $table->index('exam_code');
            $table->index('company_name');
            $table->index('created_by_principal_id');
            $table->index('org_id');
        });

        // 2. PLACEMENT EXAM TEACHER PERMISSIONS TABLE
        Schema::create('placement_exam_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('placement_exam_id');
            $table->string('teacher_id', 50); // FK to users.id
            $table->boolean('can_add_students')->default(true);
            $table->boolean('can_create_questions')->default(true);
            $table->string('assigned_by_id', 50)->nullable();
            $table->timestamps();

            $table->foreign('placement_exam_id')->references('id')->on('placement_exams')->onDelete('cascade');
            $table->index(['placement_exam_id', 'teacher_id']);
        });

        // 3. PLACEMENT EXAM CANDIDATES & SECRET 6-DIGIT CODES TABLE
        Schema::create('placement_exam_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('placement_exam_id');
            $table->string('exam_code', 50);
            $table->string('student_id', 50); // FK to users.student_id or users.id
            $table->string('full_name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('stream', 100)->nullable();
            $table->string('course', 100)->default('B.Tech');
            $table->decimal('cgpa', 4, 2)->default(7.50);
            $table->string('access_code', 10); // 6-digit unique secret code e.g. '849201'
            $table->string('scheduled_date', 50)->nullable();
            $table->string('scheduled_start_time', 20)->nullable();
            $table->string('scheduled_end_time', 20)->nullable();
            $table->boolean('is_rescheduled')->default(false);
            $table->text('rescheduled_reason')->nullable();
            $table->enum('attempt_status', ['PENDING', 'STARTED', 'COMPLETED', 'EXPIRED'])->default('PENDING');
            $table->decimal('score', 6, 2)->nullable();
            $table->string('enrolled_by_id', 50)->nullable();
            $table->timestamps();

            $table->foreign('placement_exam_id')->references('id')->on('placement_exams')->onDelete('cascade');
            $table->index(['exam_code', 'access_code']);
            $table->index(['placement_exam_id', 'student_id']);
            $table->index('access_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placement_exam_candidates');
        Schema::dropIfExists('placement_exam_teachers');
        Schema::dropIfExists('placement_exams');
    }
};
