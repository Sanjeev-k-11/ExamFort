<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiQuestionGeneratorController extends Controller
{
    public function showGenerator(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('questions.index')->with('error', 'Access Restricted: You do not have permission to generate or author questions. Please contact your Principal.');
        }

        $examCode = $request->query('exam_code');
        
        // Load exams available to this user (Institutional + System Global Papers)
        $examsQuery = Exam::orderBy('created_at', 'desc');
        if ($currentUser && in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            $examsQuery->where(function ($q) use ($currentUser) {
                if (!empty($currentUser->org_id)) {
                    $q->where('org_id', $currentUser->org_id);
                }
                if (!empty($currentUser->college_name)) {
                    $q->orWhere('college_name', $currentUser->college_name);
                }
                $q->orWhereNull('org_id')
                  ->orWhere('created_by_teacher_id', $currentUser->id);
            });
        } elseif ($currentUser && in_array(strtoupper($userRole), ['PROCTOR', 'TEACHER', 'FACULTY'])) {
            $examsQuery->where(function ($q) use ($currentUser) {
                $q->where('created_by_teacher_id', $currentUser->id)
                  ->orWhereNull('created_by_teacher_id')
                  ->orWhereNull('org_id');
            });
        }
        $exams = $examsQuery->get();
        if ($exams->isEmpty()) {
            $exams = Exam::orderBy('created_at', 'desc')->get();
        }

        $selectedExam = $examCode ? Exam::where('exam_code', $examCode)->first() : null;

        return view('exams.ai-generator', compact('currentUser', 'exams', 'selectedExam', 'examCode'));
    }

    public function generate(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Restricted: You do not have permission to generate questions.'
            ], 403);
        }

        $request->validate([
            'exam_code' => 'required|exists:exams,exam_code',
            'subject_topic' => 'required|string|max:500',
            'difficulty' => 'required|in:Easy,Medium,Hard,Competitive',
            'mcq_count' => 'required|integer|min:0|max:20',
            'coding_count' => 'required|integer|min:0|max:10',
            'paragraph_count' => 'required|integer|min:0|max:10',
        ]);

        $mcqCount = (int)$request->mcq_count;
        $codingCount = (int)$request->coding_count;
        $paragraphCount = (int)$request->paragraph_count;
        $totalCount = $mcqCount + $codingCount + $paragraphCount;

        if ($totalCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please request at least 1 question (MCQ, Coding, or Essay).'
            ], 422);
        }

        // Get API Key from Principal/User or Organization or Environment
        $apiKey = $currentUser->gemini_api_key 
            ?? ($currentUser->organization->gemini_api_key ?? null)
            ?? env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Google Gemini API Key is not configured. Please paste your Gemini API Key in the Principal Command Center first.'
            ], 400);
        }

        $systemPrompt = <<<EOT
You are an expert university professor and examination board chief setter.
Generate a complete assessment paper in valid JSON format based on the following specifications:
- Subject / Syllabus Topic: {$request->subject_topic}
- Difficulty: {$request->difficulty}
- Target MCQs Count: {$mcqCount}
- Target Coding Challenges Count: {$codingCount}
- Target Descriptive / Essay Questions Count: {$paragraphCount}

You MUST return ONLY a valid JSON object matching this schema:
{
  "questions": [
    {
      "type": "MCQ",
      "title": "Short title",
      "question_text": "Complete question text",
      "max_marks": 2.0,
      "options": [
        {"key": "A", "text": "Option A text"},
        {"key": "B", "text": "Option B text"},
        {"key": "C", "text": "Option C text"},
        {"key": "D", "text": "Option D text"}
      ],
      "correct_answer": "A",
      "explanation": "Why A is correct"
    },
    {
      "type": "CODING",
      "title": "Problem Title (e.g. Balanced Parentheses, Subarray Sum)",
      "question_text": "Detailed problem statement describing input, output, and expectations",
      "constraints": "1 <= N <= 10^5, -10^9 <= arr[i] <= 10^9",
      "sample_input": "5\\n1 2 3 4 5",
      "sample_output": "15",
      "explanation": "Sum of array is 15",
      "entry_function": "solve",
      "public_weightage_marks": 10.0,
      "hidden_weightage_marks": 40.0,
      "max_marks": 50.0,
      "starter_cpp": "#include <iostream>\\nusing namespace std;\\n\\nint main() {\\n    return 0;\\n}",
      "starter_python": "import sys\\n\\ndef solve():\\n    pass\\n\\nif __name__ == '__main__':\\n    solve()",
      "starter_java": "import java.util.Scanner;\\n\\npublic class Solution {\\n    public static void main(String[] args) {\\n    }\\n}",
      "starter_c": "#include <stdio.h>\\n\\nint main() {\\n    return 0;\\n}",
      "public_test_cases": [
        {"id": "pub_1", "input": "5\\n1 2 3 4 5", "expected": "15", "desc": "Sample input"}
      ],
      "hidden_test_cases": [
        {"id": "hid_1", "input": "1\\n0", "expected": "0", "desc": "Boundary condition"}
      ]
    },
    {
      "type": "PARAGRAPH",
      "title": "Essay / Concept Topic Title",
      "question_text": "Detailed essay prompt asking students to explain core architectural mechanisms...",
      "max_marks": 20.0,
      "rubric_json": {
        "max_marks": 20.0,
        "min_words_soft_limit": 60,
        "concepts": [
          {
            "id": "c1",
            "name": "Core Principle & Mechanics",
            "weight": 10.0,
            "keywords": ["architecture", "scaling"],
            "explanation_indicators": ["high availability", "zero downtime"]
          }
        ]
      }
    }
  ]
}
EOT;

        // Active working models on Gemini API with automatic priority fallback
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
                $response = Http::timeout(45)
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
            $extractedQuestions = $this->extractQuestionsFromText($rawText);

            if (empty($extractedQuestions) || !is_array($extractedQuestions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI generation completed but no questions could be parsed from output. Please retry.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'exam_code' => $request->exam_code,
                'model_used' => $usedModel,
                'count' => count($extractedQuestions),
                'questions' => $extractedQuestions,
                'message' => "Generated " . count($extractedQuestions) . " questions using {$usedModel} successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI Response Parse Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resilient JSON Question Extractor
     */
    private function extractQuestionsFromText(string $rawText): ?array
    {
        $cleaned = trim($rawText);

        // 1. Direct JSON decode
        $data = json_decode($cleaned, true);
        if (is_array($data)) {
            if (isset($data['questions']) && is_array($data['questions'])) return $data['questions'];
            if (isset($data['exam']) && is_array($data['exam'])) return $data['exam'];
            if (isset($data['items']) && is_array($data['items'])) return $data['items'];
            // If root is indexed array of question objects
            if (isset($data[0]) && is_array($data[0]) && isset($data[0]['type'])) return $data;
        }

        // 2. Extract from markdown code fence ```json ... ```
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleaned, $matches)) {
            $data = json_decode(trim($matches[1]), true);
            if (is_array($data)) {
                if (isset($data['questions']) && is_array($data['questions'])) return $data['questions'];
                if (isset($data[0]) && is_array($data[0])) return $data;
            }
        }

        // 3. Extract first outermost JSON object {...}
        if (preg_match('/(\{[\s\S]*\})/', $cleaned, $matches)) {
            $data = json_decode($matches[1], true);
            if (is_array($data)) {
                if (isset($data['questions']) && is_array($data['questions'])) return $data['questions'];
            }
        }

        // 4. Extract first outermost JSON array [...]
        if (preg_match('/(\[[\s\S]*\])/', $cleaned, $matches)) {
            $data = json_decode($matches[1], true);
            if (is_array($data) && count($data) > 0) return $data;
        }

        return null;
    }

    public function saveBatch(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Restricted: You do not have permission to save generated questions.'
            ], 403);
        }

        $request->validate([
            'exam_code' => 'required|exists:exams,exam_code',
            'questions' => 'required|array|min:1',
        ]);

        $examCode = $request->exam_code;
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();

        $currentMaxNumber = Question::where('exam_code', $examCode)->max('question_number') ?? 0;
        $savedCount = 0;

        foreach ($request->questions as $q) {
            $currentMaxNumber++;

            $data = [
                'exam_code' => $examCode,
                'question_number' => $currentMaxNumber,
                'type' => $q['type'] ?? 'MCQ',
                'title' => $q['title'] ?? ('Question #' . $currentMaxNumber),
                'question_text' => $q['question_text'] ?? 'Question Statement',
                'constraints' => $q['constraints'] ?? null,
                'sample_input' => $q['sample_input'] ?? null,
                'sample_output' => $q['sample_output'] ?? null,
                'explanation' => $q['explanation'] ?? null,
                'max_marks' => $q['max_marks'] ?? 10.0,
            ];

            if ($q['type'] === 'MCQ') {
                $data['options'] = $q['options'] ?? [];
                $data['correct_answer'] = $q['correct_answer'] ?? 'A';
            } elseif ($q['type'] === 'CODING') {
                $data['entry_function'] = $q['entry_function'] ?? 'solve';
                $data['public_weightage_marks'] = $q['public_weightage_marks'] ?? 10.0;
                $data['hidden_weightage_marks'] = $q['hidden_weightage_marks'] ?? 40.0;
                $data['coding_starter_code'] = [
                    'cpp' => $q['starter_cpp'] ?? '',
                    'c' => $q['starter_c'] ?? '',
                    'java' => $q['starter_java'] ?? '',
                    'python' => $q['starter_python'] ?? '',
                    'javascript' => $q['starter_javascript'] ?? '',
                ];
                $data['public_test_cases'] = $q['public_test_cases'] ?? [];
                $data['hidden_test_cases'] = $q['hidden_test_cases'] ?? [];
            } elseif ($q['type'] === 'PARAGRAPH') {
                $data['rubric_json'] = $q['rubric_json'] ?? [];
            }

            Question::create($data);
            $savedCount++;
        }

        // Update exam total questions & total marks
        $examQuestions = Question::where('exam_code', $examCode)->get();
        $exam->update([
            'total_questions' => $examQuestions->count(),
            'total_marks' => $examQuestions->sum('max_marks'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully saved {$savedCount} AI generated questions to Exam '{$exam->title}' ({$examCode}).",
            'redirect_url' => route('exams.show', $examCode)
        ]);
    }
}
