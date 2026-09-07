<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users Table
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('student_id', 50)->unique();
                $table->string('full_name', 150);
                $table->string('email', 150)->unique();
                $table->string('phone', 30)->nullable();
                $table->string('dob', 50)->nullable()->default('12 Jan 2003');
                $table->string('location', 100)->nullable()->default('Bihar, India');
                $table->string('college_name', 255)->nullable()->default('National Institute of Technology (NIT Patna)');
                $table->string('course', 100)->nullable()->default('B.Tech');
                $table->string('stream', 100)->nullable()->default('Computer Science & Engineering');
                $table->string('batch_years', 50)->nullable()->default('2022 - 2026');
                $table->text('bio')->nullable();
                $table->text('goal')->nullable();
                $table->text('achievements')->nullable();
                $table->text('interests')->nullable();
                $table->integer('profile_completion_pct')->default(85);
                $table->integer('exams_enrolled')->default(0);
                $table->integer('exams_completed')->default(0);
                $table->integer('upcoming_exams_count')->default(0);
                $table->integer('average_score')->default(0);
                $table->integer('best_score')->default(0);
                $table->integer('current_streak_days')->default(0);
                $table->string('password', 255);
                $table->string('role', 50)->default('CANDIDATE');
                $table->string('access_code', 50)->default('123456');
                $table->text('avatar_url')->nullable();
                $table->string('status', 20)->default('ACTIVE');
                $table->string('org_id', 50)->nullable();
                $table->string('created_by_principal_id', 50)->nullable();
                $table->string('created_by_teacher_id', 50)->nullable();
                $table->string('designation', 100)->nullable();
                $table->string('department', 100)->nullable();
                $table->integer('max_students_allowed')->default(500);
                $table->integer('max_exams_allowed')->default(50);
                $table->boolean('can_create_exams')->default(true);
                $table->boolean('can_set_questions')->default(true);
                $table->boolean('can_manage_lessons')->default(true);
                $table->boolean('can_manage_courses')->default(false);
                $table->boolean('can_enroll_students')->default(true);
                $table->boolean('can_view_results')->default(true);
                $table->boolean('can_create_teachers')->default(false);
                $table->string('gemini_api_key', 255)->nullable();
                $table->timestamps();
            });
        }

        // 2. Courses Table
        if (!Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('course_id', 50)->unique();
                $table->string('title', 255);
                $table->text('description');
                $table->string('icon', 50)->nullable()->default('💻');
                $table->string('color', 50)->nullable()->default('#4f46e5');
                $table->integer('lessons_count')->default(0);
                $table->string('duration_text', 50)->nullable()->default('6h 20m');
                $table->string('level', 50)->default('Intermediate');
                $table->integer('progress_percent')->default(0);
                $table->integer('completed_lessons')->default(0);
                $table->string('language', 50)->default('English');
                $table->string('certificate', 50)->default('Yes');
                $table->string('last_updated', 50)->nullable()->default('May 2026');
                $table->timestamps();
            });
        }

        // 3. Course Lessons Table
        if (!Schema::hasTable('course_lessons')) {
            Schema::create('course_lessons', function (Blueprint $table) {
                $table->id();
                $table->string('course_id', 50)->index();
                $table->integer('module_num');
                $table->string('module_title', 255);
                $table->string('lesson_num', 50);
                $table->string('lesson_title', 255);
                $table->string('duration_text', 50);
                $table->boolean('is_completed')->default(false);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 4. Course Lesson Content Table
        if (!Schema::hasTable('course_lesson_content')) {
            Schema::create('course_lesson_content', function (Blueprint $table) {
                $table->id();
                $table->string('course_id', 50);
                $table->string('lesson_num', 50);
                $table->text('concept_summary');
                $table->text('detailed_notes');
                $table->text('code_example')->nullable();
                $table->text('key_takeaways')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['course_id', 'lesson_num']);
            });
        }

        // 5. Course Topic MCQs Table
        if (!Schema::hasTable('course_topic_mcqs')) {
            Schema::create('course_topic_mcqs', function (Blueprint $table) {
                $table->id();
                $table->string('course_id', 50);
                $table->string('lesson_num', 50);
                $table->integer('question_number');
                $table->text('question_text');
                $table->json('options_json');
                $table->string('correct_key', 10);
                $table->text('explanation')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['course_id', 'lesson_num', 'question_number']);
            });
        }

        // 6. Course Topic Coding Table
        if (!Schema::hasTable('course_topic_coding')) {
            Schema::create('course_topic_coding', function (Blueprint $table) {
                $table->id();
                $table->string('course_id', 50);
                $table->string('lesson_num', 50);
                $table->integer('problem_number')->default(1);
                $table->string('title', 255);
                $table->text('problem_statement');
                $table->string('difficulty', 50)->default('Easy');
                $table->text('constraints_text')->nullable();
                $table->text('sample_input')->nullable();
                $table->text('sample_output')->nullable();
                $table->text('starter_code_cpp')->nullable();
                $table->text('starter_code_py')->nullable();
                $table->text('starter_code_java')->nullable();
                $table->text('starter_code_js')->nullable();
                $table->json('test_cases_json')->nullable();
                $table->unique(['course_id', 'lesson_num', 'problem_number']);
            });
        }

        // 7. Course Practice Submissions Table
        if (!Schema::hasTable('course_practice_submissions')) {
            Schema::create('course_practice_submissions', function (Blueprint $table) {
                $table->id();
                $table->string('candidate_id', 50);
                $table->string('course_id', 50);
                $table->string('lesson_num', 50);
                $table->string('practice_type', 20);
                $table->integer('score')->default(0);
                $table->integer('total_score')->default(0);
                $table->boolean('passed')->default(false);
                $table->json('details_json')->nullable();
                $table->timestamp('submitted_at')->useCurrent();
                $table->index(['candidate_id', 'course_id', 'lesson_num']);
            });
        }

        // 8. Exams Table
        if (!Schema::hasTable('exams')) {
            Schema::create('exams', function (Blueprint $table) {
                $table->id();
                $table->string('exam_code', 50)->unique();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('category', 100)->default('Aptitude & Coding');
                $table->integer('duration_minutes')->default(120);
                $table->integer('total_marks')->default(120);
                $table->integer('total_questions')->default(13);
                $table->string('exam_date', 50)->nullable()->default('31 Dec 2026');
                $table->string('exam_time', 50)->nullable()->default('10:00 AM - 12:00 PM');
                $table->string('status', 20)->default('ACTIVE');
                $table->boolean('is_results_published')->default(false);
                $table->boolean('face_verification_required')->default(true);
                $table->string('college_name', 255)->nullable()->default('National Institute of Technology (NIT Patna)');
                $table->string('org_id', 50)->nullable()->default('ORG_NITP');
                $table->string('created_by_teacher_id', 50)->nullable();
                $table->string('created_by_principal_id', 50)->nullable();
                $table->timestamps();
            });
        }

        // 9. Exam Instructions Table
        if (!Schema::hasTable('exam_instructions')) {
            Schema::create('exam_instructions', function (Blueprint $table) {
                $table->id();
                $table->string('exam_code', 50)->index();
                $table->text('instruction_text');
                $table->integer('display_order')->default(1);
            });
        }

        // 10. Questions Table
        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->string('exam_code', 50)->index();
                $table->integer('placement_exam_id')->nullable()->index();
                $table->integer('question_number');
                $table->string('type', 20)->default('MCQ');
                $table->string('title', 255);
                $table->text('question_text');
                $table->json('options')->nullable();
                $table->string('correct_answer', 255)->nullable();
                $table->string('entry_function', 100)->nullable()->default('solve');
                $table->text('coding_starter_code')->nullable();
                $table->text('reference_solution')->nullable();
                $table->json('random_input_schema')->nullable();
                $table->json('public_test_cases')->nullable();
                $table->json('hidden_test_cases')->nullable();
                $table->decimal('public_weightage_marks', 5, 2)->default(10.00);
                $table->decimal('hidden_weightage_marks', 5, 2)->default(40.00);
                $table->decimal('max_marks', 5, 2)->default(50.00);
                $table->json('rubric_json')->nullable();
                $table->text('explanation')->nullable();
                $table->timestamps();
            });
        }

        // 11. Submissions Table
        if (!Schema::hasTable('submissions')) {
            Schema::create('submissions', function (Blueprint $table) {
                $table->id();
                $table->string('candidate_id', 50);
                $table->string('exam_code', 50);
                $table->text('answers_json');
                $table->decimal('mcq_score', 5, 2)->default(0.00);
                $table->decimal('coding_public_score', 5, 2)->default(0.00);
                $table->decimal('coding_hidden_score', 5, 2)->default(0.00);
                $table->decimal('essay_score', 5, 2)->default(0.00);
                $table->decimal('total_score', 5, 2)->default(0.00);
                $table->json('evaluation_report')->nullable();
                $table->timestamp('submission_timestamp')->useCurrent();
                $table->index(['candidate_id', 'exam_code']);
            });
        }

        // 12. Violations Table
        if (!Schema::hasTable('violations')) {
            Schema::create('violations', function (Blueprint $table) {
                $table->id();
                $table->string('candidate_id', 50);
                $table->string('exam_code', 50)->default('NAT-2026-EXAM');
                $table->string('violation_type', 100);
                $table->text('details');
                $table->timestamp('timestamp')->useCurrent();
            });
        }

        // 13. Candidate Drafts Table
        if (!Schema::hasTable('candidate_drafts')) {
            Schema::create('candidate_drafts', function (Blueprint $table) {
                $table->id();
                $table->string('candidate_id', 50);
                $table->string('exam_code', 50);
                $table->text('answers_json');
                $table->timestamp('last_saved_at')->useCurrent();
                $table->unique(['candidate_id', 'exam_code']);
            });
        }

        // 14. Student Activities Table
        if (!Schema::hasTable('student_activities')) {
            Schema::create('student_activities', function (Blueprint $table) {
                $table->id();
                $table->string('student_id', 50)->index();
                $table->string('title', 255)->nullable()->default('');
                $table->string('activity_title', 255)->nullable()->default('');
                $table->text('description')->nullable();
                $table->string('activity_type', 50)->default('EXAM');
                $table->string('type', 50)->default('EXAM');
                $table->string('score_info', 100)->nullable()->default('');
                $table->integer('score')->default(0);
                $table->string('status', 50)->default('Completed');
                $table->string('badge', 50)->default('✓');
                $table->string('time_text', 50)->default('Recent');
                $table->string('icon', 50)->default('✓');
                $table->timestamp('timestamp')->useCurrent();
                $table->timestamps();
            });
        }

        // 15. Sessions & Cache Tables (Laravel Standard)
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_lessons');
        Schema::dropIfExists('course_lesson_content');
        Schema::dropIfExists('course_topic_mcqs');
        Schema::dropIfExists('course_topic_coding');
        Schema::dropIfExists('course_practice_submissions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_instructions');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('violations');
        Schema::dropIfExists('candidate_drafts');
        Schema::dropIfExists('student_activities');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
    }
};
