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
        return $this->belongsTo(User::class, 'candidate_id', 'student_id')
            ->withDefault(function ($user, $submission) {
                return User::where('id', $submission->candidate_id)
                    ->orWhere('student_id', $submission->candidate_id)
                    ->first() ?? new User([
                        'id' => $submission->candidate_id,
                        'student_id' => $submission->candidate_id,
                        'full_name' => 'Candidate (' . $submission->candidate_id . ')',
                        'email' => $submission->candidate_id . '@examfort.local'
                    ]);
            });
    }

    public function getCandidateNameAttribute(): string
    {
        if ($this->candidate && !empty($this->candidate->full_name)) {
            return $this->candidate->full_name;
        }
        $user = User::where('student_id', $this->candidate_id)->orWhere('id', $this->candidate_id)->first();
        return $user?->full_name ?? ('Candidate ' . $this->candidate_id);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_code', 'exam_code');
    }
}
