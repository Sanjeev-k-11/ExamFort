<?php

namespace App\Http\Controllers;

use App\Models\CandidateDraft;
use App\Models\Exam;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Http\Request;

class LiveMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canViewResults()) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: You do not have permission to view real-time proctoring radar. Please contact your Principal.');
        }

        $selectedExamCode = $request->query('exam_code');

        $examsQuery = Exam::orderBy('created_at', 'desc');
        if ($currentUser && in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR', 'PROCTOR', 'TEACHER', 'FACULTY'])) {
            $examsQuery->where(function($q) use ($currentUser) {
                if ($currentUser->org_id) {
                    $q->where('org_id', $currentUser->org_id)->orWhere('college_name', $currentUser->college_name);
                } else {
                    $q->where('college_name', $currentUser->college_name)->orWhere('created_by_teacher_id', $currentUser->id);
                }
            });
        }
        $exams = $examsQuery->get();

        if (!$selectedExamCode && $exams->isNotEmpty()) {
            $activeExam = $exams->firstWhere('status', 'ACTIVE');
            $selectedExamCode = $activeExam ? $activeExam->exam_code : $exams->first()->exam_code;
        }

        $currentExam = $selectedExamCode ? Exam::find($selectedExamCode) : null;

        $violations = Violation::with('candidate')
            ->when($selectedExamCode, function ($q) use ($selectedExamCode) {
                return $q->where('exam_code', $selectedExamCode);
            })
            ->orderBy('timestamp', 'desc')
            ->limit(50)
            ->get();

        $drafts = CandidateDraft::with('candidate')
            ->when($selectedExamCode, function ($q) use ($selectedExamCode) {
                return $q->where('exam_code', $selectedExamCode);
            })
            ->orderBy('last_saved_at', 'desc')
            ->get();

        $candidateUsers = User::where('role', 'CANDIDATE')->get();

        return view('monitoring.index', compact('exams', 'selectedExamCode', 'currentExam', 'violations', 'drafts', 'candidateUsers', 'currentUser'));
    }

    public function apiViolations(Request $request)
    {
        $examCode = $request->query('exam_code');

        $violations = Violation::with('candidate')
            ->when($examCode, function ($q) use ($examCode) {
                return $q->where('exam_code', $examCode);
            })
            ->orderBy('timestamp', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $violations->count(),
            'violations' => $violations
        ]);
    }

    public function apiDrafts(Request $request)
    {
        $examCode = $request->query('exam_code');

        $drafts = CandidateDraft::with('candidate')
            ->when($examCode, function ($q) use ($examCode) {
                return $q->where('exam_code', $examCode);
            })
            ->orderBy('last_saved_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $drafts->count(),
            'drafts' => $drafts
        ]);
    }

    public function logViolation(Request $request)
    {
        $request->validate([
            'candidate_id' => 'required',
            'exam_code' => 'required',
            'violation_type' => 'required|string',
            'details' => 'required|string',
        ]);

        $violation = Violation::create([
            'candidate_id' => $request->candidate_id,
            'exam_code' => $request->exam_code,
            'violation_type' => $request->violation_type,
            'details' => $request->details,
            'timestamp' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'violation' => $violation]);
        }

        return back()->with('success', 'Incident / Warning logged successfully for candidate.');
    }

    public function dismissViolation($id)
    {
        $violation = Violation::findOrFail($id);
        $violation->delete();

        if (request()->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Incident dismissed.']);
        }

        return back()->with('success', 'Violation record dismissed.');
    }
}
