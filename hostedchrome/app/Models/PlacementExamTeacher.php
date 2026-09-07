<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacementExamTeacher extends Model
{
    use HasFactory;

    protected $table = 'placement_exam_teachers';

    protected $fillable = [
        'placement_exam_id',
        'teacher_id',
        'can_add_students',
        'can_create_questions',
        'can_reschedule',
        'assigned_by_id',
    ];

    protected $casts = [
        'can_add_students' => 'boolean',
        'can_create_questions' => 'boolean',
        'can_reschedule' => 'boolean',
    ];

    public function placementExam()
    {
        return $this->belongsTo(PlacementExam::class, 'placement_exam_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id', 'id');
    }
}
