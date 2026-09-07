<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'icon',
        'color',
        'lessons_count',
        'duration_text',
        'level',
        'progress_percent',
        'completed_lessons',
        'language',
        'certificate',
        'last_updated',
    ];

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class, 'course_id', 'course_id')->orderBy('module_num')->orderBy('lesson_num');
    }

    public function lessonContents()
    {
        return $this->hasMany(CourseLessonContent::class, 'course_id', 'course_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(CourseTeacherAssignment::class, 'course_id', 'course_id');
    }

    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'course_teacher_assignments', 'course_id', 'teacher_id', 'course_id', 'id');
    }
}
