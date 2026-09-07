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
        if (!Schema::hasTable('student_subject_teachers')) {
            Schema::create('student_subject_teachers', function (Blueprint $table) {
                $table->id();
                $table->string('student_id', 50); // maps to users.id (Candidate)
                $table->string('teacher_id', 50); // maps to users.id (Faculty)
                $table->string('course_id', 50)->nullable(); // maps to courses.course_id
                $table->string('subject_name', 150);
                $table->string('assigned_by_principal_id', 50)->nullable();
                $table->string('academic_term', 100)->default('Current Semester');
                $table->timestamp('assigned_at')->useCurrent();

                $table->unique(['student_id', 'subject_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject_teachers');
    }
};
