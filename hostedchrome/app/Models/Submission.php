<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'submissions';
    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'exam_code',
        'answers_json',
        'mcq_score',
        'coding_public_score',
        'coding_hidden_score',
        'essay_score',
        'total_score',
        'evaluation_report',
        'submission_timestamp',
    ];

    protected $casts = [
        'answers_json' => 'array',
        'evaluation_report' => 'array',
        'mcq_score' => 'float',
        'coding_public_score' => 'float',
        'coding_hidden_score' => 'float',
        'essay_score' => 'float',
        'total_score' => 'float',
    ];

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_code', 'exam_code');
    }
}
