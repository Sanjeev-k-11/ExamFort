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
        if (!Schema::hasTable('course_teacher_assignments')) {
            Schema::create('course_teacher_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('course_id', 50);
                $table->string('teacher_id', 50);
                $table->string('assigned_by_principal_id', 50)->nullable();
                $table->string('role', 50)->default('Lead Instructor'); // Lead Instructor, Evaluator, Proctor
                $table->text('instructions')->nullable();
                $table->timestamp('assigned_at')->useCurrent();

                $table->unique(['course_id', 'teacher_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_teacher_assignments');
    }
};
