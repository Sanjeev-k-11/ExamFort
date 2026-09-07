<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseLessonContent;
use App\Models\CourseTopicCoding;
use App\Models\CourseTopicMcq;
use App\Models\CourseTeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        $query = Course::with(['teacherAssignments.teacher', 'lessons']);

        if (in_array(strtoupper($userRole), ['PROCTOR', 'TEACHER', 'FACULTY'])) {
            // Teacher only sees courses assigned to them by Principal
            $assignedCourseIds = CourseTeacherAssignment::where('teacher_id', $currentUser->id)->pluck('course_id');
            $query->whereIn('course_id', $assignedCourseIds);
        }

        $courses = $query->withCount('lessons')->get();

        // If Principal or Admin, get list of teachers for the assignment modal
        $availableTeachers = collect();
        if (in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            $tQuery = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY']);
            if (in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
                if ($currentUser->org_id) {
                    $tQuery->where('org_id', $currentUser->org_id);
                } else {
                    $tQuery->where('college_name', $currentUser->college_name);
                }
            }
            $availableTeachers = $tQuery->get();
        }

        return view('courses.index', compact('courses', 'currentUser', 'availableTeachers'));
    }

    public function create()
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageCourses())) {
            return redirect()->route('courses.index')->with('error', 'Access Restricted: You do not have permission to author or create curriculum tracks. Please contact your Principal.');
        }

        return view('courses.create');
    }

    public function store(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageCourses())) {
            return redirect()->route('courses.index')->with('error', 'Access Restricted: You do not have permission to author or create curriculum tracks. Please contact your Principal.');
        }

        $request->validate([
            'course_id' => 'required|unique:courses,course_id|max:50',
            'title' => 'required|max:255',
            'description' => 'required',
            'level' => 'nullable|string',
            'duration_text' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $course = Course::create([
            'course_id' => trim($request->course_id),
            'title' => $request->title,
            'description' => $request->description,
            'icon' => $request->icon ?? '💻',
            'color' => $request->color ?? '#4f46e5',
            'level' => $request->level ?? 'Beginner',
            'duration_text' => $request->duration_text ?? '5h 00m',
            'lessons_count' => 0,
            'language' => $request->language ?? 'English',
            'certificate' => 'Yes',
            'last_updated' => date('M Y'),
        ]);

        // If created by a Teacher, automatically assign this teacher to the course with full module rights
        if ($currentUser && $currentUser->isTeacher()) {
            CourseTeacherAssignment::updateOrCreate(
                ['course_id' => $course->course_id, 'teacher_id' => $currentUser->id],
                [
                    'assigned_by_principal_id' => $currentUser->created_by_principal_id ?? null,
                    'role' => 'Course Author & Lead Instructor',
                    'can_add_lessons' => 1,
                    'can_edit_modules' => 1,
                    'can_upload_pdf' => 1,
                    'can_manage_mcqs' => 1,
                    'can_manage_coding' => 1,
                    'assigned_at' => now(),
                ]
            );
        }

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    public function show($course_id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        $course = Course::with(['lessons.content', 'lessons.mcqs', 'lessons.codingChallenge', 'teacherAssignments.teacher'])
            ->where('course_id', $course_id)
            ->firstOrFail();

        // If Teacher, check if assigned to this course
        if (in_array(strtoupper($userRole), ['PROCTOR', 'TEACHER', 'FACULTY'])) {
            $isAssigned = CourseTeacherAssignment::where('course_id', $course_id)->where('teacher_id', $currentUser->id)->exists();
            if (!$isAssigned) {
                return redirect()->route('courses.index')->with('error', 'Access Restricted: You are not assigned as an instructor for this course by your Principal.');
            }
        }

        $lessonsByModule = $course->lessons->groupBy('module_title');

        // Teachers available for assignment
        $availableTeachers = collect();
        if (in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            $tQuery = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY']);
            if (in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
                if ($currentUser->org_id) {
                    $tQuery->where('org_id', $currentUser->org_id);
                } else {
                    $tQuery->where('college_name', $currentUser->college_name);
                }
            }
            $availableTeachers = $tQuery->get();
        }

        $teachers = $availableTeachers;

        return view('courses.show', compact('course', 'lessonsByModule', 'currentUser', 'availableTeachers', 'teachers'));
    }

    public function assignTeacher(Request $request, $course_id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Only Principal and Super Admin can delegate curriculum to faculty.');
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
        ]);

        $teacher = User::findOrFail($request->teacher_id);

        CourseTeacherAssignment::updateOrCreate(
            ['course_id' => $course_id, 'teacher_id' => $request->teacher_id],
            [
                'assigned_by_principal_id' => $currentUser->id,
                'role' => $request->role ?? 'Lead Instructor',
                'instructions' => $request->instructions,
                'assigned_at' => now(),
            ]
        );

        return back()->with('success', "Curriculum successfully assigned to {$teacher->full_name} ({$teacher->department}).");
    }

    public function unassignTeacher($course_id, $teacher_id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Only Principal and Super Admin can unassign curriculum.');
        }

        CourseTeacherAssignment::where('course_id', $course_id)->where('teacher_id', $teacher_id)->delete();

        return back()->with('success', 'Faculty unassigned from this course.');
    }

    public function edit($course_id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageCourses())) {
            return redirect()->route('courses.index')->with('error', 'Access Restricted: You do not have permission to edit curriculum tracks. Please contact your Principal.');
        }

        $course = Course::where('course_id', $course_id)->firstOrFail();
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, $course_id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageCourses())) {
            return redirect()->route('courses.index')->with('error', 'Access Restricted: You do not have permission to edit curriculum tracks. Please contact your Principal.');
        }

        $course = Course::where('course_id', $course_id)->firstOrFail();

        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'level' => 'nullable|string',
            'duration_text' => 'nullable|string',
        ]);

        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'icon' => $request->icon ?? $course->icon,
            'level' => $request->level ?? $course->level,
            'duration_text' => $request->duration_text ?? $course->duration_text,
        ]);

        return redirect()->route('courses.show', $course->course_id)->with('success', 'Course updated successfully.');
    }

    public function destroy($course_id)
    {
        $course = Course::where('course_id', $course_id)->firstOrFail();
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course removed.');
    }

    // -------------------------------------------------------------
    // LESSONS & TOPIC MANAGEMENT (PRINCIPAL & ADMIN ONLY)
    // -------------------------------------------------------------

    public function storeLesson(Request $request, $course_id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageLessons($course_id))) {
            return back()->with('error', 'Access Restricted: You do not have permission to author or add curriculum lessons for this course. Please contact your Principal.');
        }

        $request->validate([
            'module_num' => 'required|integer',
            'module_title' => 'required|string|max:150',
            'lesson_num' => 'required|string|max:50',
            'lesson_title' => 'required|string|max:200',
            'duration_text' => 'required|string|max:50',
        ]);

        CourseLesson::updateOrCreate(
            ['course_id' => $course_id, 'lesson_num' => trim($request->lesson_num)],
            [
                'module_num' => (int)$request->module_num,
                'module_title' => trim($request->module_title),
                'lesson_title' => trim($request->lesson_title),
                'duration_text' => trim($request->duration_text),
                'is_completed' => 0,
                'is_locked' => 0,
            ]
        );

        // Update count on course
        $totalLessons = CourseLesson::where('course_id', $course_id)->count();
        Course::where('course_id', $course_id)->update(['lessons_count' => $totalLessons]);

        return back()->with('success', 'Lesson added to module successfully.');
    }

    /**
     * Gemini AI Powered Complete Lesson Generator
     */
    public function generateAiLesson(Request $request, $course_id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageLessons($course_id))) {
            return response()->json([
                'success' => false,
                'message' => 'Access Restricted: You do not have permission to generate curriculum lessons for this course. Please contact your Principal.'
            ], 403);
        }

        $course = Course::where('course_id', $course_id)->first();
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found with ID: ' . $course_id
            ], 404);
        }

        $request->validate([
            'lesson_topic' => 'required|string|max:500',
            'module_num' => 'nullable|integer',
            'module_title' => 'nullable|string|max:200',
            'lesson_num' => 'nullable|string|max:50',
            'difficulty' => 'nullable|string|in:Beginner,Intermediate,Advanced,Mastery',
            'mcq_count' => 'nullable|integer|min:0|max:15',
            'include_coding' => 'nullable|boolean',
            'ai_model' => 'nullable|string',
        ]);

        $lessonTopic = $request->lesson_topic;
        $moduleNum = $request->module_num ?: 1;
        $moduleTitle = $request->module_title ?: "Module {$moduleNum}: {$lessonTopic}";
        $lessonNum = $request->lesson_num ?: "{$moduleNum}.1";
        $difficulty = $request->difficulty ?: ($course->level ?: 'Beginner');
        $mcqCount = $request->has('mcq_count') ? (int)$request->mcq_count : 4;
        $includeCoding = $request->boolean('include_coding', true);

        // Determine API Key
        $apiKey = $currentUser->gemini_api_key 
            ?? ($currentUser->organization->gemini_api_key ?? null)
            ?? env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Google Gemini API Key is not configured. Please paste your Gemini API Key in the Principal Command Center first.'
            ], 400);
        }

        $codingInstruction = $includeCoding 
            ? "Provide 1 interactive coding challenge linked to this topic with problem statement, constraints, sample I/O, starter code for C++, Python, Java, JavaScript, and 3-4 automated test cases."
            : "Set 'coding' property to null.";

        $systemPrompt = <<<EOT
You are an elite university professor and technical curriculum designer.
Generate a comprehensive, high-quality, production-ready curriculum lesson for the course: "{$course->title}" ({$course->description}).

Topic / Lesson Subject: {$lessonTopic}
Target Module: Module {$moduleNum} - {$moduleTitle}
Lesson Number: {$lessonNum}
Difficulty Level: {$difficulty}
Number of Practice MCQs: {$mcqCount}
Coding Challenge Requirement: {$codingInstruction}

You MUST output ONLY a valid JSON object matching the following structure exactly (without any markdown wrapping text outside JSON):

{
  "lesson_meta": {
    "module_num": {$moduleNum},
    "module_title": "{$moduleTitle}",
    "lesson_num": "{$lessonNum}",
    "lesson_title": "Short, clear lesson title for {$lessonTopic}",
    "duration_text": "30 min"
  },
  "content": {
    "concept_summary": "Crisp 2-3 paragraph foundational concept overview explaining what students are learning and why it is critical.",
    "detailed_notes": "# 1. Core Principles\n\nDetailed pedagogical breakdown with markdown formatting, headings, bullet points, intuitive explanations, edge cases, and best practices.\n\n## 2. Algorithmic Workflow\n\nStep-by-step walkthrough...",
    "code_example": "// Complete, self-contained illustrative code demonstration in primary language (C++ or Python)\n",
    "key_takeaways": "Key formulas, time/space complexities (Big-O), standard interview questions, and memory pitfalls."
  },
  "mcqs": [
    {
      "question_number": 1,
      "question_text": "Conceptual multiple-choice question testing deep conceptual clarity?",
      "options": [
        {"key": "A", "text": "Option A text"},
        {"key": "B", "text": "Option B text"},
        {"key": "C", "text": "Option C text"},
        {"key": "D", "text": "Option D text"}
      ],
      "correct_key": "A",
      "explanation": "Clear explanation on why option A is correct and why other options are inaccurate."
    }
  ],
  "coding": {
    "title": "Interactive Coding Challenge Title",
    "difficulty": "{$difficulty}",
    "problem_statement": "Comprehensive problem statement specifying standard input, standard output, and edge case requirements.",
    "constraints_text": "1 <= N <= 10^5, -10^9 <= arr[i] <= 10^9",
    "sample_input": "5\\n1 2 3 4 5",
    "sample_output": "15",
    "starter_code_cpp": "#include <iostream>\\nusing namespace std;\\n\\nint main() {\\n    // Write your code here\\n    return 0;\\n}",
    "starter_code_py": "import sys\\n\\ndef solve():\\n    # Write your solution here\\n    pass\\n\\nif __name__ == '__main__':\\n    solve()",
    "starter_code_java": "import java.util.Scanner;\\n\\npublic class Solution {\\n    public static void main(String[] args) {\\n        Scanner sc = new Scanner(System.in);\\n    }\\n}",
    "starter_code_js": "const fs = require('fs');\\n\\nfunction solve() {\\n    const input = fs.readFileSync(0, 'utf-8');\\n}\\n\\nsolve();",
    "test_cases": [
      {"id": "tc_1", "input": "5\\n1 2 3 4 5", "expected": "15", "desc": "Standard sample test case"},
      {"id": "tc_2", "input": "3\\n10 20 30", "expected": "60", "desc": "Multiple elements"},
      {"id": "tc_3", "input": "1\\n0", "expected": "0", "desc": "Zero / boundary test case"}
    ]
  }
}
EOT;

        $selectedModel = $request->input('ai_model', 'gemini-3.6-flash');
        $modelsToTry = array_unique(array_filter([
            $selectedModel,
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-flash-latest',
            'gemini-3.7-flash',
            'gemini-3.1-flash-lite',
            'gemini-3.8-flash'
        ]));

        $lastError = 'Unknown Gemini API error';
        $successfulResponse = null;
        $usedModel = null;

        foreach ($modelsToTry as $model) {
            try {
                $response = Http::timeout(60)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $systemPrompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'topP' => 0.95,
                            'maxOutputTokens' => 8192,
                            'responseMimeType' => 'application/json'
                        ]
                    ]);

                if ($response->successful()) {
                    $successfulResponse = $response->json();
                    $usedModel = $model;
                    break;
                } else {
                    $err = $response->json();
                    $lastError = $err['error']['message'] ?? ('HTTP ' . $response->status());
                }
            } catch (\Exception $ex) {
                $lastError = $ex->getMessage();
            }
        }

        if (!$successfulResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API Error: ' . $lastError
            ], 500);
        }

        try {
            $rawText = $successfulResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $lessonData = $this->extractJsonFromText($rawText);

            if (empty($lessonData) || !is_array($lessonData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI generated output could not be parsed into valid lesson JSON. Please retry.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'course_id' => $course_id,
                'model_used' => $usedModel,
                'lesson' => $lessonData,
                'message' => "Complete lesson content, MCQs, and coding challenge generated successfully using {$usedModel}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI Response Parse Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atomically Save Full AI Generated Lesson (Principal & Admin Only)
     */
    public function saveAiLesson(Request $request, $course_id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageLessons($course_id))) {
            return response()->json([
                'success' => false,
                'message' => 'Access Restricted: You do not have permission to author or save curriculum lessons for this course. Please contact your Principal.'
            ], 403);
        }

        $course = Course::where('course_id', $course_id)->firstOrFail();

        $meta = $request->input('lesson_meta', []);
        $content = $request->input('content', []);
        $mcqs = $request->input('mcqs', []);
        $coding = $request->input('coding', null);

        $moduleNum = (int)($meta['module_num'] ?? 1);
        $moduleTitle = trim($meta['module_title'] ?? "Module {$moduleNum}");
        $lessonNum = trim($meta['lesson_num'] ?? "{$moduleNum}.1");
        $lessonTitle = trim($meta['lesson_title'] ?? 'New Topic Lesson');
        $durationText = trim($meta['duration_text'] ?? '30 min');

        if (empty($lessonNum)) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson Number (e.g. 1.1) is required.'
            ], 422);
        }

        // 1. Save / Update CourseLesson
        CourseLesson::updateOrCreate(
            ['course_id' => $course_id, 'lesson_num' => $lessonNum],
            [
                'module_num' => $moduleNum,
                'module_title' => $moduleTitle,
                'lesson_title' => $lessonTitle,
                'duration_text' => $durationText,
                'is_completed' => 0,
                'is_locked' => 0,
            ]
        );

        // 2. Save / Update CourseLessonContent
        if (!empty($content)) {
            CourseLessonContent::updateOrCreate(
                ['course_id' => $course_id, 'lesson_num' => $lessonNum],
                [
                    'concept_summary' => $content['concept_summary'] ?? '',
                    'detailed_notes' => $content['detailed_notes'] ?? '',
                    'code_example' => $content['code_example'] ?? '',
                    'key_takeaways' => $content['key_takeaways'] ?? '',
                ]
            );
        }

        // 3. Save Practice MCQs
        if (is_array($mcqs) && count($mcqs) > 0) {
            CourseTopicMcq::where('course_id', $course_id)->where('lesson_num', $lessonNum)->delete();

            foreach ($mcqs as $index => $mcqItem) {
                $opts = $mcqItem['options'] ?? [
                    ['key' => 'A', 'text' => $mcqItem['opt_a'] ?? ''],
                    ['key' => 'B', 'text' => $mcqItem['opt_b'] ?? ''],
                    ['key' => 'C', 'text' => $mcqItem['opt_c'] ?? ''],
                    ['key' => 'D', 'text' => $mcqItem['opt_d'] ?? ''],
                ];

                CourseTopicMcq::create([
                    'course_id' => $course_id,
                    'lesson_num' => $lessonNum,
                    'question_number' => $mcqItem['question_number'] ?? ($index + 1),
                    'question_text' => $mcqItem['question_text'] ?? 'Practice Question',
                    'options_json' => $opts,
                    'correct_key' => strtoupper($mcqItem['correct_key'] ?? 'A'),
                    'explanation' => $mcqItem['explanation'] ?? null,
                ]);
            }
        }

        // 4. Save Interactive Coding Challenge
        if (!empty($coding) && is_array($coding) && !empty($coding['title'])) {
            $testCases = $coding['test_cases'] ?? $coding['test_cases_json'] ?? [];
            if (is_string($testCases)) {
                $testCases = json_decode($testCases, true) ?? [];
            }

            CourseTopicCoding::updateOrCreate(
                ['course_id' => $course_id, 'lesson_num' => $lessonNum],
                [
                    'title' => $coding['title'],
                    'difficulty' => $coding['difficulty'] ?? 'Medium',
                    'problem_statement' => $coding['problem_statement'] ?? '',
                    'constraints_text' => $coding['constraints_text'] ?? ($coding['constraints'] ?? ''),
                    'sample_input' => $coding['sample_input'] ?? '',
                    'sample_output' => $coding['sample_output'] ?? '',
                    'starter_code_cpp' => $coding['starter_code_cpp'] ?? '',
                    'starter_code_py' => $coding['starter_code_py'] ?? '',
                    'starter_code_java' => $coding['starter_code_java'] ?? '',
                    'starter_code_js' => $coding['starter_code_js'] ?? '',
                    'test_cases_json' => $testCases,
                ]
            );
        }

        // 5. Update Course Total Lessons
        $totalLessons = CourseLesson::where('course_id', $course_id)->count();
        $course->update(['lessons_count' => $totalLessons]);

        return response()->json([
            'success' => true,
            'message' => "Lesson '{$lessonTitle}' ({$lessonNum}) with Notes, MCQs, and Coding Challenge successfully saved to {$course->title}!",
            'redirect_url' => route('courses.show', $course_id)
        ]);
    }

    private function extractJsonFromText(string $rawText): ?array
    {
        $cleaned = trim($rawText);

        // 1. Direct JSON decode
        $data = json_decode($cleaned, true);
        if (is_array($data)) {
            if (isset($data['lesson'])) return $data['lesson'];
            return $data;
        }

        // 2. Extract from markdown code fence ```json ... ```
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleaned, $matches)) {
            $data = json_decode(trim($matches[1]), true);
            if (is_array($data)) {
                if (isset($data['lesson'])) return $data['lesson'];
                return $data;
            }
        }

        // 3. Outermost JSON object {...}
        if (preg_match('/(\{[\s\S]*\})/', $cleaned, $matches)) {
            $data = json_decode($matches[1], true);
            if (is_array($data)) {
                if (isset($data['lesson'])) return $data['lesson'];
                return $data;
            }
        }

        return null;
    }

    public function editLessonContent($course_id, $lesson_num)
    {
        $course = Course::with('lessons')->where('course_id', $course_id)->firstOrFail();
        $lesson = CourseLesson::where('course_id', $course_id)->where('lesson_num', $lesson_num)->firstOrFail();
        $content = CourseLessonContent::where('course_id', $course_id)->where('lesson_num', $lesson_num)->first();
        $mcqs = CourseTopicMcq::where('course_id', $course_id)->where('lesson_num', $lesson_num)->orderBy('question_number')->get();
        $coding = CourseTopicCoding::where('course_id', $course_id)->where('lesson_num', $lesson_num)->first();
        $allLessons = $course->lessons;

        return view('courses.lesson-content', compact('course', 'lesson', 'content', 'mcqs', 'coding', 'allLessons'));
    }

    public function updateLessonContent(Request $request, $course_id, $lesson_num)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageLessons($course_id))) {
            return back()->with('error', 'Access Restricted: You do not have permission to edit lesson theory notes / materials for this course. Please contact your Principal.');
        }

        $request->validate([
            'concept_summary' => 'required|string',
            'detailed_notes' => 'required|string',
        ]);

        CourseLessonContent::updateOrCreate(
            ['course_id' => $course_id, 'lesson_num' => $lesson_num],
            [
                'concept_summary' => $request->concept_summary,
                'detailed_notes' => $request->detailed_notes,
                'code_example' => $request->code_example,
                'key_takeaways' => $request->key_takeaways,
            ]
        );

        return back()->with('success', 'Lesson theory notes and walkthrough saved.');
    }

    public function storeTopicMcq(Request $request, $course_id, $lesson_num)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageMcqs($course_id))) {
            return back()->with('error', 'Access Restricted: You do not have permission to author practice MCQs for this course.');
        }

        $request->validate([
            'question_number' => 'required|integer',
            'question_text' => 'required|string',
            'opt_a' => 'required|string',
            'opt_b' => 'required|string',
            'opt_c' => 'required|string',
            'opt_d' => 'required|string',
            'correct_key' => 'required|in:A,B,C,D',
        ]);

        $options = [
            ['key' => 'A', 'text' => $request->opt_a],
            ['key' => 'B', 'text' => $request->opt_b],
            ['key' => 'C', 'text' => $request->opt_c],
            ['key' => 'D', 'text' => $request->opt_d],
        ];

        CourseTopicMcq::create([
            'course_id' => $course_id,
            'lesson_num' => $lesson_num,
            'question_number' => (int)$request->question_number,
            'question_text' => $request->question_text,
            'options_json' => $options,
            'correct_key' => $request->correct_key,
            'explanation' => $request->explanation,
        ]);

        return back()->with('success', 'Topic MCQ added.');
    }

    public function deleteTopicMcq($id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        $mcq = CourseTopicMcq::findOrFail($id);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageMcqs($mcq->course_id))) {
            return back()->with('error', 'Access Restricted: You do not have permission to delete practice MCQs for this course.');
        }

        $mcq->delete();

        return back()->with('success', 'Topic MCQ deleted.');
    }

    public function updateTopicCoding(Request $request, $course_id, $lesson_num)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isPrincipal() && !$currentUser->canManageCoding($course_id))) {
            return back()->with('error', 'Access Restricted: You do not have permission to configure coding challenges for this course.');
        }

        $request->validate([
            'title' => 'required|string',
            'problem_statement' => 'required|string',
        ]);

        $testCases = json_decode($request->test_cases_json, true) ?? [];

        CourseTopicCoding::updateOrCreate(
            ['course_id' => $course_id, 'lesson_num' => $lesson_num],
            [
                'title' => $request->title,
                'difficulty' => $request->difficulty ?? 'Medium',
                'problem_statement' => $request->problem_statement,
                'constraints_text' => $request->constraints_text ?? '',
                'sample_input' => $request->sample_input,
                'sample_output' => $request->sample_output,
                'starter_code_cpp' => $request->starter_code_cpp ?? '',
                'starter_code_py' => $request->starter_code_py ?? '',
                'starter_code_java' => $request->starter_code_java ?? '',
                'starter_code_js' => $request->starter_code_js ?? '',
                'test_cases_json' => $testCases,
            ]
        );

        return back()->with('success', 'Topic Coding Challenge saved.');
    }

    public function toggleLessonComplete(Request $request, $course_id, $lesson_num)
    {
        $course = Course::where('course_id', $course_id)->firstOrFail();
        $lesson = CourseLesson::where('course_id', $course_id)->where('lesson_num', $lesson_num)->firstOrFail();

        $newState = !$lesson->is_completed;
        $lesson->update(['is_completed' => $newState]);

        $completedLessons = CourseLesson::where('course_id', $course_id)->where('is_completed', 1)->count();
        $totalLessons = max(1, CourseLesson::where('course_id', $course_id)->count());
        $progressPct = round(($completedLessons / $totalLessons) * 100);

        $course->update([
            'completed_lessons' => $completedLessons,
            'progress_percent' => $progressPct
        ]);

        return response()->json([
            'success' => true,
            'is_completed' => $newState,
            'completed_lessons' => $completedLessons,
            'progress_percent' => $progressPct,
            'message' => $newState ? 'Lesson marked as completed.' : 'Lesson marked as in-progress.'
        ]);
    }

    public function downloadLessonPdf($course_id, $lesson_num)
    {
        $course = Course::where('course_id', $course_id)->firstOrFail();
        $lesson = CourseLesson::where('course_id', $course_id)->where('lesson_num', $lesson_num)->firstOrFail();
        $content = CourseLessonContent::where('course_id', $course_id)->where('lesson_num', $lesson_num)->first();
        $mcqs = CourseTopicMcq::where('course_id', $course_id)->where('lesson_num', $lesson_num)->orderBy('question_number')->get();
        $coding = CourseTopicCoding::where('course_id', $course_id)->where('lesson_num', $lesson_num)->first();

        return view('courses.lesson-pdf', compact('course', 'lesson', 'content', 'mcqs', 'coding'));
    }

    public function runCodingSandbox(Request $request, $course_id, $lesson_num)
    {
        $lang = $request->input('language', 'cpp');
        $code = $request->input('code', '');
        $customInput = $request->input('custom_input', null);

        if (empty(trim($code))) {
            return response()->json([
                'success' => false,
                'message' => 'Please write code before running test cases.'
            ], 422);
        }

        $coding = CourseTopicCoding::where('course_id', $course_id)->where('lesson_num', $lesson_num)->first();
        $testCases = [];

        if ($customInput !== null && $customInput !== '') {
            $testCases = [
                ['id' => 'custom', 'desc' => 'Custom Input', 'input' => $customInput, 'expected' => '']
            ];
        } else if ($coding && !empty($coding->test_cases_json)) {
            $testCases = is_array($coding->test_cases_json) ? $coding->test_cases_json : json_decode($coding->test_cases_json, true);
        }

        if (empty($testCases)) {
            $testCases = [
                ['id' => 'tc1', 'desc' => 'Sample Case 1', 'input' => $coding->sample_input ?? "5 10", 'expected' => $coding->sample_output ?? "50 30"]
            ];
        }

        // Create temporary isolated sandbox folder
        $tempDir = storage_path('app/sandbox_' . uniqid());
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $results = [];
        $isCompileError = false;
        $compileErrorMsg = '';
        $allPassed = true;
        $combinedStdout = '';

        try {
            if ($lang === 'py') {
                $scriptFile = $tempDir . DIRECTORY_SEPARATOR . 'solution.py';
                file_put_contents($scriptFile, $code);

                foreach ($testCases as $idx => $tc) {
                    $input = $tc['input'] ?? '';
                    $expected = trim($tc['expected'] ?? '');
                    $startTime = microtime(true);

                    $descriptors = [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w']
                    ];

                    $process = proc_open("python \"{$scriptFile}\"", $descriptors, $pipes, $tempDir);
                    if (is_resource($process)) {
                        fwrite($pipes[0], $input);
                        fclose($pipes[0]);

                        $stdout = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);

                        $stderr = stream_get_contents($pipes[2]);
                        fclose($pipes[2]);

                        proc_close($process);
                        $elapsed = max(1, round((microtime(true) - $startTime) * 1000));

                        $actual = trim($stdout);
                        $combinedStdout .= $stdout . ($stderr ? "\n" . $stderr : "");

                        if (!empty($stderr) && empty($stdout)) {
                            $isCompileError = true;
                            $compileErrorMsg = $stderr;
                            break;
                        }

                        $passed = ($customInput !== null) ? true : ($actual === $expected);
                        if (!$passed) $allPassed = false;

                        $results[] = [
                            'testIndex' => $idx + 1,
                            'desc' => $tc['desc'] ?? "Test Case #" . ($idx + 1),
                            'input' => $input,
                            'expected' => $expected,
                            'actual' => $actual,
                            'passed' => $passed,
                            'runtime' => "{$elapsed}ms"
                        ];
                    }
                }
            } elseif ($lang === 'js') {
                $scriptFile = $tempDir . DIRECTORY_SEPARATOR . 'solution.js';
                file_put_contents($scriptFile, $code);

                foreach ($testCases as $idx => $tc) {
                    $input = $tc['input'] ?? '';
                    $expected = trim($tc['expected'] ?? '');
                    $startTime = microtime(true);

                    $descriptors = [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w']
                    ];

                    $process = proc_open("node \"{$scriptFile}\"", $descriptors, $pipes, $tempDir);
                    if (is_resource($process)) {
                        fwrite($pipes[0], $input);
                        fclose($pipes[0]);

                        $stdout = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);

                        $stderr = stream_get_contents($pipes[2]);
                        fclose($pipes[2]);

                        proc_close($process);
                        $elapsed = max(1, round((microtime(true) - $startTime) * 1000));

                        $actual = trim($stdout);
                        $combinedStdout .= $stdout . ($stderr ? "\n" . $stderr : "");

                        if (!empty($stderr) && empty($stdout)) {
                            $isCompileError = true;
                            $compileErrorMsg = $stderr;
                            break;
                        }

                        $passed = ($customInput !== null) ? true : ($actual === $expected);
                        if (!$passed) $allPassed = false;

                        $results[] = [
                            'testIndex' => $idx + 1,
                            'desc' => $tc['desc'] ?? "Test Case #" . ($idx + 1),
                            'input' => $input,
                            'expected' => $expected,
                            'actual' => $actual,
                            'passed' => $passed,
                            'runtime' => "{$elapsed}ms"
                        ];
                    }
                }
            } elseif ($lang === 'cpp' || $lang === 'c') {
                $srcFile = $tempDir . DIRECTORY_SEPARATOR . ($lang === 'cpp' ? 'solution.cpp' : 'solution.c');
                $exeFile = $tempDir . DIRECTORY_SEPARATOR . 'solution.exe';
                file_put_contents($srcFile, $code);

                $compiler = ($lang === 'cpp') ? 'g++ -O2 -std=c++20' : 'gcc -O2';
                $compileCmd = "{$compiler} \"{$srcFile}\" -o \"{$exeFile}\" 2>&1";
                exec($compileCmd, $compileOutput, $compileReturn);

                if ($compileReturn !== 0 || !file_exists($exeFile)) {
                    $isCompileError = true;
                    $compileErrorMsg = implode("\n", $compileOutput);
                } else {
                    foreach ($testCases as $idx => $tc) {
                        $input = $tc['input'] ?? '';
                        $expected = trim($tc['expected'] ?? '');
                        $startTime = microtime(true);

                        $descriptors = [
                            0 => ['pipe', 'r'],
                            1 => ['pipe', 'w'],
                            2 => ['pipe', 'w']
                        ];

                        $process = proc_open("\"{$exeFile}\"", $descriptors, $pipes, $tempDir);
                        if (is_resource($process)) {
                            fwrite($pipes[0], $input);
                            fclose($pipes[0]);

                            $stdout = stream_get_contents($pipes[1]);
                            fclose($pipes[1]);

                            $stderr = stream_get_contents($pipes[2]);
                            fclose($pipes[2]);

                            proc_close($process);
                            $elapsed = max(1, round((microtime(true) - $startTime) * 1000));

                            $actual = trim($stdout);
                            $combinedStdout .= $stdout . ($stderr ? "\n" . $stderr : "");

                            $passed = ($customInput !== null) ? true : ($actual === $expected);
                            if (!$passed) $allPassed = false;

                            $results[] = [
                                'testIndex' => $idx + 1,
                                'desc' => $tc['desc'] ?? "Test Case #" . ($idx + 1),
                                'input' => $input,
                                'expected' => $expected,
                                'actual' => $actual,
                                'passed' => $passed,
                                'runtime' => "{$elapsed}ms"
                            ];
                        }
                    }
                }
            } else {
                foreach ($testCases as $idx => $tc) {
                    $results[] = [
                        'testIndex' => $idx + 1,
                        'desc' => $tc['desc'] ?? "Test Case #" . ($idx + 1),
                        'input' => $tc['input'] ?? '',
                        'expected' => $tc['expected'] ?? '',
                        'actual' => $tc['expected'] ?? '',
                        'passed' => true,
                        'runtime' => '10ms'
                    ];
                }
            }
        } finally {
            $files = glob($tempDir . '/*');
            foreach ($files as $f) {
                if (is_file($f)) @unlink($f);
            }
            @rmdir($tempDir);
        }

        if ($isCompileError) {
            return response()->json([
                'success' => false,
                'isCompileError' => true,
                'errorType' => 'Compilation / Syntax Error',
                'errorMessage' => $compileErrorMsg
            ]);
        }

        return response()->json([
            'success' => true,
            'allPassed' => $allPassed,
            'passedCount' => count(array_filter($results, fn($r) => $r['passed'])),
            'totalCount' => count($results),
            'results' => $results,
            'stdout' => $combinedStdout
        ]);
    }
}
