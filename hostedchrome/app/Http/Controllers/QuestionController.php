<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('exams.index')->with('error', 'Access Restricted: You do not have permission to view or manage examination questions. Please contact your Principal to enable question authoring rights.');
        }

        $examCode = $request->query('exam_code');
        $type = $request->query('type');
        $exams = Exam::orderBy('created_at', 'desc')->get();

        $query = Question::with('exam');

        if ($examCode) {
            $query->where('exam_code', $examCode);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $questions = $query->orderBy('exam_code')->orderBy('question_number')->paginate(20);

        return view('questions.index', compact('questions', 'exams', 'examCode', 'type', 'currentUser'));
    }

    public function create(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('questions.index')->with('error', 'Access Restricted: You do not have permission to set or author questions. Please contact your Principal.');
        }

        $examCode = $request->query('exam_code');
        $type = $request->query('type', 'MCQ');
        $exams = Exam::orderBy('created_at', 'desc')->get();

        $nextNumber = 1;
        if ($examCode) {
            $lastQ = Question::where('exam_code', $examCode)->max('question_number');
            $nextNumber = ($lastQ ?? 0) + 1;
        }

        return view('questions.create', compact('exams', 'examCode', 'type', 'nextNumber', 'currentUser'));
    }

    public function store(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('questions.index')->with('error', 'Access Restricted: You do not have permission to set or author questions. Please contact your Principal.');
        }
        $request->validate([
            'exam_code' => 'required|exists:exams,exam_code',
            'type' => 'required|in:MCQ,CODING,PARAGRAPH',
            'question_number' => 'required|integer',
            'title' => 'required|max:255',
            'question_text' => 'required',
            'max_marks' => 'required|numeric|min:0.5',
        ]);

        $data = [
            'exam_code' => $request->exam_code,
            'question_number' => $request->question_number,
            'type' => $request->type,
            'title' => $request->title,
            'question_text' => $request->question_text,
            'constraints' => $request->constraints,
            'sample_input' => $request->sample_input,
            'sample_output' => $request->sample_output,
            'explanation' => $request->explanation,
            'max_marks' => $request->max_marks,
        ];

        if ($request->type === 'MCQ') {
            $options = [];
            $optKeys = ['A', 'B', 'C', 'D'];
            foreach ($optKeys as $k) {
                if ($request->filled("option_{$k}")) {
                    $options[] = [
                        'key' => $k,
                        'text' => $request->input("option_{$k}")
                    ];
                }
            }
            $data['options'] = $options;
            $data['correct_answer'] = $request->correct_answer ?? 'A';
        } elseif ($request->type === 'CODING') {
            $data['entry_function'] = $request->entry_function ?? 'solve';
            $data['reference_solution'] = $request->reference_solution;
            $data['public_weightage_marks'] = $request->public_weightage_marks ?? 10.00;
            $data['hidden_weightage_marks'] = $request->hidden_weightage_marks ?? 40.00;

            // Starter codes across supported student languages
            $data['coding_starter_code'] = [
                'cpp' => $request->input('starter_cpp'),
                'c' => $request->input('starter_c'),
                'java' => $request->input('starter_java'),
                'python' => $request->input('starter_python'),
                'javascript' => $request->input('starter_javascript'),
            ];

            // Test cases parsing
            if ($request->filled('public_test_cases_json')) {
                $data['public_test_cases'] = is_array($request->public_test_cases_json) 
                    ? $request->public_test_cases_json 
                    : (json_decode($request->public_test_cases_json, true) ?? []);
            }
            if ($request->filled('hidden_test_cases_json')) {
                $data['hidden_test_cases'] = is_array($request->hidden_test_cases_json) 
                    ? $request->hidden_test_cases_json 
                    : (json_decode($request->hidden_test_cases_json, true) ?? []);
            }
        } elseif ($request->type === 'PARAGRAPH') {
            if ($request->filled('rubric_json_raw')) {
                $data['rubric_json'] = is_array($request->rubric_json_raw)
                    ? $request->rubric_json_raw
                    : (json_decode($request->rubric_json_raw, true) ?? []);
            } else {
                $data['rubric_json'] = [
                    'max_marks' => (float)$request->max_marks,
                    'min_words_soft_limit' => (int)($request->min_words ?? 40),
                    'concepts' => []
                ];
            }
        }

        $question = Question::create($data);

        // Update exam total_questions count
        $exam = Exam::find($request->exam_code);
        if ($exam) {
            $exam->update(['total_questions' => $exam->questions()->count()]);
        }

        return redirect()->route('exams.show', $request->exam_code)->with('success', 'Question #' . $question->question_number . ' created successfully.');
    }

    public function edit($id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('questions.index')->with('error', 'Access Restricted: You do not have permission to edit questions.');
        }

        $question = Question::findOrFail($id);
        $exams = Exam::orderBy('created_at', 'desc')->get();

        return view('questions.edit', compact('question', 'exams', 'currentUser'));
    }

    public function update(Request $request, $id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('questions.index')->with('error', 'Access Restricted: You do not have permission to update questions.');
        }

        $question = Question::findOrFail($id);

        $request->validate([
            'title' => 'required|max:255',
            'question_text' => 'required',
            'max_marks' => 'required|numeric|min:0.5',
            'question_number' => 'required|integer',
        ]);

        $data = [
            'question_number' => $request->question_number,
            'title' => $request->title,
            'question_text' => $request->question_text,
            'constraints' => $request->constraints,
            'sample_input' => $request->sample_input,
            'sample_output' => $request->sample_output,
            'explanation' => $request->explanation,
            'max_marks' => $request->max_marks,
        ];

        if ($question->type === 'MCQ') {
            $options = [];
            $optKeys = ['A', 'B', 'C', 'D'];
            foreach ($optKeys as $k) {
                if ($request->filled("option_{$k}")) {
                    $options[] = [
                        'key' => $k,
                        'text' => $request->input("option_{$k}")
                    ];
                }
            }
            $data['options'] = $options;
            $data['correct_answer'] = $request->correct_answer ?? $question->correct_answer;
        } elseif ($question->type === 'CODING') {
            $data['entry_function'] = $request->entry_function ?? $question->entry_function;
            $data['reference_solution'] = $request->reference_solution;
            $data['public_weightage_marks'] = $request->public_weightage_marks ?? 10.00;
            $data['hidden_weightage_marks'] = $request->hidden_weightage_marks ?? 40.00;

            $data['coding_starter_code'] = [
                'cpp' => $request->input('starter_cpp'),
                'c' => $request->input('starter_c'),
                'java' => $request->input('starter_java'),
                'python' => $request->input('starter_python'),
                'javascript' => $request->input('starter_javascript'),
            ];

            if ($request->filled('public_test_cases_json')) {
                $data['public_test_cases'] = is_array($request->public_test_cases_json)
                    ? $request->public_test_cases_json
                    : (json_decode($request->public_test_cases_json, true) ?? $question->public_test_cases);
            }
            if ($request->filled('hidden_test_cases_json')) {
                $data['hidden_test_cases'] = is_array($request->hidden_test_cases_json)
                    ? $request->hidden_test_cases_json
                    : (json_decode($request->hidden_test_cases_json, true) ?? $question->hidden_test_cases);
            }
        } elseif ($question->type === 'PARAGRAPH') {
            if ($request->filled('rubric_json_raw')) {
                $data['rubric_json'] = is_array($request->rubric_json_raw)
                    ? $request->rubric_json_raw
                    : (json_decode($request->rubric_json_raw, true) ?? $question->rubric_json);
            }
        }

        $question->update($data);

        return redirect()->route('exams.show', $question->exam_code)->with('success', 'Question #' . $question->question_number . ' updated successfully.');
    }

    public function destroy($id)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canSetQuestions()) {
            return redirect()->route('questions.index')->with('error', 'Access Restricted: You do not have permission to delete questions.');
        }

        $question = Question::findOrFail($id);
        $examCode = $question->exam_code;
        $question->delete();

        $exam = Exam::find($examCode);
        if ($exam) {
            $exam->update(['total_questions' => $exam->questions()->count()]);
        }

        return redirect()->route('exams.show', $examCode)->with('success', 'Question deleted.');
    }
}
