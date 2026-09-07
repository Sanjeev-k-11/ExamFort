<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLessonContent extends Model
{
    use HasFactory;

    protected $table = 'course_lesson_content';
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'lesson_num',
        'concept_summary',
        'detailed_notes',
        'code_example',
        'key_takeaways',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
