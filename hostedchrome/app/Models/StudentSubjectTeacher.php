<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubjectTeacher extends Model
{
    use HasFactory;

    protected $table = 'student_subject_teachers';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'course_id',
        'subject_name',
        'assigned_by_principal_id',
        'academic_term',
        'assigned_at',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    public function principal()
    {
        return $this->belongsTo(User::class, 'assigned_by_principal_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
