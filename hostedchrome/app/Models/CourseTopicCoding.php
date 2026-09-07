<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTopicCoding extends Model
{
    use HasFactory;

    protected $table = 'course_topic_coding';
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'lesson_num',
        'title',
        'problem_statement',
        'difficulty',
        'constraints_text',
        'sample_input',
        'sample_output',
        'starter_code_cpp',
        'starter_code_py',
        'starter_code_java',
        'starter_code_js',
        'test_cases_json',
    ];

    protected $casts = [
        'test_cases_json' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
