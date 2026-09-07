<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTeacherAssignment extends Model
{
    use HasFactory;

    protected $table = 'course_teacher_assignments';
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'teacher_id',
        'assigned_by_principal_id',
        'role',
        'instructions',
        'can_add_lessons',
        'can_edit_modules',
        'can_upload_pdf',
        'can_manage_mcqs',
        'can_manage_coding',
        'assigned_at',
    ];

    protected $casts = [
        'can_add_lessons' => 'boolean',
        'can_edit_modules' => 'boolean',
        'can_upload_pdf' => 'boolean',
        'can_manage_mcqs' => 'boolean',
        'can_manage_coding' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    public function principal()
    {
        return $this->belongsTo(User::class, 'assigned_by_principal_id', 'id');
    }
}
