<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$principal = \App\Models\User::where('role', 'PRINCIPAL')->first() ?? \App\Models\User::first();
$teacher = \App\Models\User::whereIn('role', ['TEACHER', 'FACULTY', 'PROCTOR'])->first();

$pe = \App\Models\PlacementExam::firstOrCreate(
    ['exam_code' => 'PLC-TCS-2026-01'],
    [
        'title' => 'TCS NQT 2026 - National Qualifier Aptitude & Coding Round',
        'company_name' => 'Tata Consultancy Services',
        'job_role' => 'Digital Software Development Engineer',
        'package_lpa' => '7.5 LPA - 9.0 LPA',
        'min_cgpa' => 6.50,
        'eligibility_criteria' => 'B.Tech CSE/IT/ECE (Batch 2026), minimum 60% throughout in 10th/12th/B.Tech, No active backlogs.',
        'description' => 'Official national campus placement screening test. Includes Quantitative Aptitude, Logical Reasoning, Core Computer Science, and Data Structure Coding Challenges.',
        'exam_date' => '25 Oct 2026',
        'start_time' => '10:00 AM',
        'end_time' => '01:00 PM',
        'duration_minutes' => 90,
        'total_marks' => 100,
        'total_questions' => 3,
        'status' => 'ACTIVE',
        'face_verification_required' => true,
        'is_code_only' => true,
        'created_by_principal_id' => $principal ? $principal->id : null,
        'college_name' => $principal->college_name ?? 'National Institute of Technology',
    ]
);

// Sync to exams table
\App\Models\Exam::updateOrCreate(
    ['exam_code' => 'PLC-TCS-2026-01'],
    [
        'title' => 'TCS NQT 2026 - National Qualifier Aptitude & Coding Round (Placement Drive)',
        'description' => $pe->description,
        'category' => 'Campus Placement',
        'duration_minutes' => 90,
        'total_marks' => 100,
        'total_questions' => 3,
        'exam_date' => '25 Oct 2026',
        'exam_time' => '10:00 AM - 01:00 PM',
        'status' => 'ACTIVE',
        'college_name' => $pe->college_name,
    ]
);

// Assign teacher if exists
if ($teacher) {
    \App\Models\PlacementExamTeacher::firstOrCreate(
        ['placement_exam_id' => $pe->id, 'teacher_id' => $teacher->id],
        ['can_add_students' => true, 'can_create_questions' => true, 'assigned_by_id' => $principal ? $principal->id : null]
    );
}

// Enroll candidates if none
if ($pe->candidates()->count() === 0) {
    $candidates = \App\Models\User::where('role', 'CANDIDATE')->take(6)->get();
    foreach ($candidates as $c) {
        $code = \App\Models\PlacementExamCandidate::generateUniqueAccessCode($pe->id);
        \App\Models\PlacementExamCandidate::create([
            'placement_exam_id' => $pe->id,
            'exam_code' => $pe->exam_code,
            'student_id' => $c->student_id ?? $c->id,
            'full_name' => $c->full_name,
            'email' => $c->email,
            'phone' => $c->phone ?? '+91 98765 43210',
            'stream' => $c->stream ?? 'Computer Science & Engineering',
            'course' => $c->course ?? 'B.Tech',
            'cgpa' => 8.25,
            'access_code' => $code,
            'scheduled_date' => $pe->exam_date,
            'scheduled_start_time' => $pe->start_time,
            'scheduled_end_time' => $pe->end_time,
            'is_rescheduled' => false,
            'attempt_status' => 'PENDING',
            'enrolled_by_id' => $principal ? $principal->id : null,
        ]);
    }
}

// Add sample questions if none
if (\App\Models\Question::where('exam_code', $pe->exam_code)->count() === 0) {
    \App\Models\Question::create([
        'exam_code' => $pe->exam_code,
        'question_number' => 1,
        'type' => 'MCQ',
        'title' => 'Time Complexity of Binary Search Tree Operations',
        'question_text' => 'What is the worst-case time complexity of searching for an element in an unbalanced Binary Search Tree with N nodes?',
        'options' => ['O(1)', 'O(log N)', 'O(N)', 'O(N log N)'],
        'correct_answer' => 'O(N)',
        'explanation' => 'In the worst case (skewed tree / degenerate tree), BST acts like a linked list leading to O(N) operations.',
        'max_marks' => 10,
    ]);

    \App\Models\Question::create([
        'exam_code' => $pe->exam_code,
        'question_number' => 2,
        'type' => 'CODING',
        'title' => 'Two Sum - Target Pair Finder',
        'question_text' => 'Given an array of integers nums and an integer target, return indices of the two numbers such that they add up to target.',
        'sample_input' => "4 9\n2 7 11 15",
        'sample_output' => '0 1',
        'constraints' => '2 <= nums.length <= 10^4',
        'entry_function' => 'twoSum',
        'coding_starter_code' => [
            'python' => "def twoSum(nums, target):\n    # Write your solution here\n    pass\n",
            'cpp' => "#include <iostream>\n#include <vector>\nusing namespace std;\n\nint main() {\n    // solve\n    return 0;\n}",
            'java' => "import java.util.*;\npublic class Solution {\n    public static void main(String[] args) {\n        // solve\n    }\n}"
        ],
        'public_test_cases' => [
            ['input' => "4 9\n2 7 11 15", 'expected_output' => '0 1', 'explanation' => 'nums[0] + nums[1] == 9']
        ],
        'hidden_test_cases' => [
            ['input' => "3 6\n3 2 4", 'expected_output' => '1 2']
        ],
        'public_weightage_marks' => 20,
        'hidden_weightage_marks' => 30,
        'max_marks' => 50,
    ]);

    \App\Models\Question::create([
        'exam_code' => $pe->exam_code,
        'question_number' => 3,
        'type' => 'PARAGRAPH',
        'title' => 'System Design: Low Latency Distributed Cache Architecture',
        'question_text' => 'Explain the architectural trade-offs between Cache-Aside and Write-Through caching strategies in a microservices deployment experiencing high concurrent read/write traffic.',
        'max_marks' => 40,
        'rubric_json' => [
            'max_score' => 40,
            'criteria' => [
                ['name' => 'Depth of Cache Invalidation & Consistency', 'weight' => 50],
                ['name' => 'High Concurrency Trade-off Analysis', 'weight' => 50]
            ]
        ]
    ]);
}

echo "SUCCESS: Placement drive, candidates with secret codes, and questions ready!\n";
