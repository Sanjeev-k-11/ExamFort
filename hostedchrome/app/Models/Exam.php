<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $table = 'exams';
    protected $primaryKey = 'exam_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'exam_code',
        'title',
        'description',
        'category',
        'duration_minutes',
        'total_marks',
        'total_questions',
        'exam_date',
        'exam_time',
        'status',
        'is_results_published',
        'created_by_teacher_id',
        'college_name',
    ];

    protected $casts = [
        'is_results_published' => 'boolean',
        'duration_minutes' => 'integer',
        'total_marks' => 'integer',
        'total_questions' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'created_by_teacher_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'exam_code', 'exam_code')->orderBy('question_number');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'exam_code', 'exam_code');
    }

    public function violations()
    {
        return $this->hasMany(Violation::class, 'exam_code', 'exam_code');
    }

    public function drafts()
    {
        return $this->hasMany(CandidateDraft::class, 'exam_code', 'exam_code');
    }
}
