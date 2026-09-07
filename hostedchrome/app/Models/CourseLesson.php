<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    use HasFactory;

    protected $table = 'course_lessons';
    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'module_num',
        'module_title',
        'lesson_num',
        'lesson_title',
        'duration_text',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'module_num' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function content()
    {
        return $this->hasOne(CourseLessonContent::class, 'lesson_num', 'lesson_num')
                    ->where('course_id', $this->course_id);
    }

    public function mcqs()
    {
        return $this->hasMany(CourseTopicMcq::class, 'lesson_num', 'lesson_num')
                    ->where('course_id', $this->course_id);
    }

    public function codingChallenge()
    {
        return $this->hasOne(CourseTopicCoding::class, 'lesson_num', 'lesson_num')
                    ->where('course_id', $this->course_id);
    }
}
