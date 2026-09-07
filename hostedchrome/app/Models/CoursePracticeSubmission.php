<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePracticeSubmission extends Model
{
    use HasFactory;

    protected $table = 'course_practice_submissions';
    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'course_id',
        'lesson_num',
        'practice_type',
        'score',
        'total_score',
        'passed',
        'details_json',
        'submitted_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'score' => 'integer',
        'total_score' => 'integer',
        'details_json' => 'array',
    ];

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id', 'id');
    }
}
