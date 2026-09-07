<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacementExam extends Model
{
    use HasFactory;

    protected $table = 'placement_exams';

    protected $fillable = [
        'exam_code',
        'title',
        'company_name',
        'company_logo_url',
        'job_role',
        'package_lpa',
        'min_cgpa',
        'eligibility_criteria',
        'description',
        'drive_type',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_marks',
        'total_questions',
        'status',
        'face_verification_required',
        'is_code_only',
        'is_results_published',
        'created_by_principal_id',
        'org_id',
        'college_name',
    ];

    protected $casts = [
        'min_cgpa' => 'float',
        'duration_minutes' => 'integer',
        'total_marks' => 'integer',
        'total_questions' => 'integer',
        'face_verification_required' => 'boolean',
        'is_code_only' => 'boolean',
        'is_results_published' => 'boolean',
    ];

    public function principal()
    {
        return $this->belongsTo(User::class, 'created_by_principal_id', 'id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id');
    }

    public function assignedTeachers()
    {
        return $this->hasMany(PlacementExamTeacher::class, 'placement_exam_id', 'id');
    }

    public function candidates()
    {
        return $this->hasMany(PlacementExamCandidate::class, 'placement_exam_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'exam_code', 'exam_code')->orderBy('question_number');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'exam_code', 'exam_code');
    }

    /**
     * Check if a specific teacher has access / permissions
     */
    public function teacherPermission($teacherId)
    {
        return $this->assignedTeachers()->where('teacher_id', $teacherId)->first();
    }
}
