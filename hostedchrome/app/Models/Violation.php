<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;

    protected $table = 'violations';
    public $timestamps = false;

    protected $fillable = [
        'candidate_id',
        'exam_code',
        'violation_type',
        'details',
        'timestamp',
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
