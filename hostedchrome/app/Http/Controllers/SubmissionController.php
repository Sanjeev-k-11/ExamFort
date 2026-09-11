<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $examCode = $request->query('exam_code');
        $search = $request->query('search');
        $attemptType = $request->query('attempt_type');
        $exams = Exam::orderBy('created_at', 'desc')->get();

        $query = Submission::with(['candidate', 'exam']);

        if ($examCode) {
            $query->where('exam_code', $examCode);
        }

        if ($attemptType === 'initial') {
            $query->where(function ($q) {
                $q->whereNull('attempt_number')->orWhere('attempt_number', 1);
            });
        } elseif ($attemptType === 'reattempt') {
            $query->where('attempt_number', '>', 1);
        }

        if ($search) {
            $query->whereHas('candidate', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            })->orWhere('candidate_id', 'like', "%{$search}%");
        }

        $submissions = $query->orderBy('submission_timestamp', 'desc')->paginate(15);

        return view('submissions.index', compact('submissions', 'exams', 'examCode', 'search', 'attemptType'));
    }

    public function show($id)
    {
        $submission = Submission::with(['candidate', 'exam.questions'])->findOrFail($id);
        $exam = $submission->exam;
        $questions = $exam ? $exam->questions->keyBy('question_number') : collect();

        // Fetch all attempts by this student for this exam to show comparisons
        $allAttempts = Submission::where('exam_code', $submission->exam_code)
            ->where(function ($q) use ($submission) {
                $q->where('candidate_id', $submission->candidate_id);
                if ($submission->candidate && $submission->candidate->student_id) {
                    $q->orWhere('candidate_id', $submission->candidate->student_id);
                }
            })
            ->orderBy('submission_timestamp', 'asc')
            ->get();

        // Parse answers_json if string or array
        $answers = is_array($submission->answers_json)
            ? $submission->answers_json
            : (json_decode($submission->answers_json, true) ?? []);

        // Parse evaluation_report if available
        $evaluation = is_array($submission->evaluation_report)
            ? $submission->evaluation_report
            : (json_decode($submission->evaluation_report, true) ?? []);

        return view('submissions.show', compact('submission', 'exam', 'questions', 'answers', 'evaluation', 'allAttempts'));
    }

    public function updateScores(Request $request, $id)
    {
        $submission = Submission::findOrFail($id);

        $request->validate([
            'mcq_score' => 'required|numeric|min:0',
            'coding_public_score' => 'required|numeric|min:0',
            'coding_hidden_score' => 'required|numeric|min:0',
            'essay_score' => 'required|numeric|min:0',
        ]);

        $mcq = (float)$request->mcq_score;
        $pub = (float)$request->coding_public_score;
        $hid = (float)$request->coding_hidden_score;
        $essay = (float)$request->essay_score;
        $total = $mcq + $pub + $hid + $essay;

        $submission->update([
            'mcq_score' => $mcq,
            'coding_public_score' => $pub,
            'coding_hidden_score' => $hid,
            'essay_score' => $essay,
            'total_score' => $total,
        ]);

        return back()->with('success', "Submission scores updated successfully. New Total: {$total}");
    }

    public function destroy($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();

        return redirect()->route('submissions.index')->with('success', 'Submission record deleted.');
    }
}
