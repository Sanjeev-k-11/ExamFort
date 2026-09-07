<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacementExamCandidate extends Model
{
    use HasFactory;

    protected $table = 'placement_exam_candidates';

    protected $fillable = [
        'placement_exam_id',
        'exam_code',
        'student_id',
        'full_name',
        'email',
        'phone',
        'stream',
        'course',
        'cgpa',
        'access_code',
        'scheduled_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'is_rescheduled',
        'rescheduled_reason',
        'attempt_status',
        'score',
        'mcq_score',
        'coding_score',
        'essay_score',
        'violations_count',
        'trust_score',
        'shortlist_status',
        'submitted_at',
        'enrolled_by_id',
    ];

    protected $casts = [
        'cgpa' => 'float',
        'score' => 'float',
        'mcq_score' => 'float',
        'coding_score' => 'float',
        'essay_score' => 'float',
        'violations_count' => 'integer',
        'trust_score' => 'integer',
        'is_rescheduled' => 'boolean',
    ];

    public function placementExam()
    {
        return $this->belongsTo(PlacementExam::class, 'placement_exam_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'student_id');
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by_id', 'id');
    }

    public function submission()
    {
        return $this->hasOne(Submission::class, 'candidate_id', 'student_id')
            ->where('exam_code', $this->exam_code);
    }

    /**
     * Generate a unique 6-digit access code for this exam
     */
    public static function generateUniqueAccessCode($examId = null)
    {
        do {
            $code = strval(random_int(100000, 999999));
            $exists = self::where('access_code', $code)
                ->when($examId, fn($q) => $q->where('placement_exam_id', $examId))
                ->exists();
        } while ($exists);

        return $code;
    }
}
