<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTopicMcq extends Model
{
    use HasFactory;

    protected $table = 'course_topic_mcqs';
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'lesson_num',
        'question_number',
        'question_text',
        'options_json',
        'correct_key',
        'explanation',
    ];

    protected $casts = [
        'options_json' => 'array',
        'question_number' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
