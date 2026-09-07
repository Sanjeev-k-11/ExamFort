<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateDraft extends Model
{
    use HasFactory;

    protected $table = 'candidate_drafts';
    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'exam_code',
        'answers_json',
        'last_saved_at',
    ];

    protected $casts = [
        'answers_json' => 'array',
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
