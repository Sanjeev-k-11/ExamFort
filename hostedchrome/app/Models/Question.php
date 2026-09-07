<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';
    public $timestamps = false;

    protected $fillable = [
        'exam_code',
        'question_number',
        'type',
        'title',
        'question_text',
        'constraints',
        'sample_input',
        'sample_output',
        'explanation',
        'options',
        'correct_answer',
        'entry_function',
        'coding_starter_code',
        'reference_solution',
        'random_input_schema',
        'public_test_cases',
        'hidden_test_cases',
        'public_weightage_marks',
        'hidden_weightage_marks',
        'max_marks',
        'rubric_json',
    ];

    protected $casts = [
        'options' => 'array',
        'coding_starter_code' => 'array',
        'random_input_schema' => 'array',
        'public_test_cases' => 'array',
        'hidden_test_cases' => 'array',
        'rubric_json' => 'array',
        'public_weightage_marks' => 'float',
        'hidden_weightage_marks' => 'float',
        'max_marks' => 'float',
        'question_number' => 'integer',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_code', 'exam_code');
    }
}
