<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        $search = $request->query('search');
        $status = $request->query('status');

        $query = Exam::with(['teacher'])
            ->withCount(['questions', 'submissions', 'violations']);

        // Scope exams by role
        if (in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            // Principal views all exams in their college / organization
            $query->where(function ($q) use ($currentUser) {
                if ($currentUser->org_id) {
                    $q->where('org_id', $currentUser->org_id)
                      ->orWhere('college_name', $currentUser->college_name);
                } else {
                    $q->where('college_name', $currentUser->college_name)
                      ->orWhere('created_by_teacher_id', $currentUser->id);
                }
            });
        } elseif (in_array(strtoupper($userRole), ['PROCTOR', 'TEACHER', 'FACULTY'])) {
            // Teacher views their own exams or college exams
            $query->where(function ($q) use ($currentUser) {
                $q->where('created_by_teacher_id', $currentUser->id)
                  ->orWhere('college_name', $currentUser->college_name);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('exam_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $exams = $query->orderBy('exam_date', 'desc')->paginate(12);

        return view('exams.index', compact('exams', 'currentUser'));
    }

    public function create()
    {
        $userId = session('auth_user_id');
        $currentUser = User::with(['organization', 'principal'])->find($userId);

        // 1. Quota Check for Principal (Annual Exam Quota set by Super Admin)
        if ($currentUser && $currentUser->isPrincipal()) {
            $maxAllowed = $currentUser->max_exams_allowed ?? 100;
            $examsCount = Exam::where(function($q) use ($currentUser) {
                if ($currentUser->org_id) {
                    $q->where('org_id', $currentUser->org_id);
                }
                $q->orWhere('college_name', $currentUser->college_name);
            })->count();

            if ($examsCount >= $maxAllowed) {
                return redirect()->route('exams.index')->with('error', "Annual Exam Quota Exceeded: Your institution has reached the annual limit of {$maxAllowed} exams allocated by the Super Administrator. Please contact the administrator for a quota expansion.");
            }
        }

        // 2. Permission & Quota Check for Teacher (Exam Permissions and Quotas set by Principal)
        if ($currentUser && $currentUser->isTeacher()) {
            if (!$currentUser->canCreateExams()) {
                return redirect()->route('exams.index')->with('error', 'Access Restricted: You do not have permission to author or create exams. Please contact your Principal to enable exam creation rights.');
            }

            $maxAllowed = $currentUser->max_exams_allowed ?? 10;
            $examsCount = Exam::where('created_by_teacher_id', $currentUser->id)->count();

            if ($examsCount >= $maxAllowed) {
                return redirect()->route('exams.index')->with('error', "Teacher Exam Limit Reached: You have reached your allocated limit of {$maxAllowed} exams. Please contact your Principal/Dean for quota expansion.");
            }
        }

        return view('exams.create', compact('currentUser'));
    }

    public function store(Request $request)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canCreateExams()) {
            return redirect()->route('exams.index')->with('error', 'Access Restricted: You do not have permission to author or create exams. Please contact your Principal to enable exam creation rights.');
        }

        $request->validate([
            'exam_code' => 'required|string|max:50|unique:exams,exam_code',
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'total_marks' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,UPCOMING,COMPLETED',
        ]);

        // Enforce Principal Annual Quota
        if ($currentUser && $currentUser->isPrincipal()) {
            $maxAllowed = $currentUser->max_exams_allowed ?? 100;
            $examsCount = Exam::where('org_id', $currentUser->org_id)
                ->orWhere('college_name', $currentUser->college_name)->count();

            if ($examsCount >= $maxAllowed) {
                return redirect()->route('exams.index')->with('error', "Annual Exam Quota Exceeded: Allocated yearly limit is {$maxAllowed} exams.");
            }
        }

        Exam::create([
            'exam_code' => strtoupper(trim($request->exam_code)),
            'org_id' => $currentUser ? $currentUser->org_id : null,
            'title' => $request->title,
            'category' => $request->category,
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'description' => $request->description,
            'exam_date' => $request->exam_date ?? date('d M Y'),
            'exam_time' => $request->exam_time ?? '10:00 AM - 12:00 PM',
            'status' => $request->status,
            'is_results_published' => $request->has('is_results_published') ? 1 : 0,
            'created_by_teacher_id' => $currentUser ? $currentUser->id : null,
            'college_name' => $currentUser ? $currentUser->college_name : 'General',
        ]);

        return redirect()->route('exams.show', strtoupper(trim($request->exam_code)))
            ->with('success', "Exam '" . $request->title . "' created. You can now author questions and blueprint.");
    }

    public function show($exam_code)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        $exam = Exam::with(['questions' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }, 'submissions.candidate', 'violations.candidate', 'teacher'])
            ->findOrFail($exam_code);

        $questionsCount = $exam->questions->count();
        $submissionsCount = $exam->submissions->count();
        $avgScore = $exam->submissions->avg('total_score') ?? 0;

        return view('exams.show', compact('exam', 'questionsCount', 'submissionsCount', 'avgScore', 'currentUser'));
    }

    public function edit($exam_code)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canCreateExams()) {
            return redirect()->route('exams.index')->with('error', 'Access Restricted: You do not have permission to modify examination parameters. Please contact your Principal.');
        }

        $exam = Exam::findOrFail($exam_code);
        return view('exams.edit', compact('exam', 'currentUser'));
    }

    public function update(Request $request, $exam_code)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canCreateExams()) {
            return redirect()->route('exams.index')->with('error', 'Access Restricted: You do not have permission to modify examination parameters. Please contact your Principal.');
        }

        $exam = Exam::findOrFail($exam_code);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'total_marks' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,UPCOMING,COMPLETED',
        ]);

        $exam->update([
            'title' => $request->title,
            'category' => $request->category,
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'description' => $request->description,
            'exam_date' => $request->exam_date,
            'exam_time' => $request->exam_time,
            'status' => $request->status,
            'is_results_published' => $request->has('is_results_published') ? 1 : 0,
        ]);

        return redirect()->route('exams.show', $exam->exam_code)->with('success', 'Exam parameters updated successfully.');
    }

    public function destroy($exam_code)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canCreateExams()) {
            return redirect()->route('exams.index')->with('error', 'Access Restricted: You do not have permission to delete exams. Please contact your Principal.');
        }

        $exam = Exam::findOrFail($exam_code);
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted successfully.');
    }

    public function toggleStatus($exam_code)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canCreateExams()) {
            return back()->with('error', 'Access Restricted: You do not have permission to change exam schedule status.');
        }

        $exam = Exam::findOrFail($exam_code);
        $transitions = [
            'UPCOMING' => 'ACTIVE',
            'ACTIVE' => 'COMPLETED',
            'COMPLETED' => 'UPCOMING',
        ];

        $exam->status = $transitions[$exam->status] ?? 'ACTIVE';
        $exam->save();

        return back()->with('success', "Exam status updated to {$exam->status}.");
    }

    public function togglePublishResults($exam_code)
    {
        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canViewResults()) {
            return back()->with('error', 'Access Restricted: You do not have permission to publish exam results.');
        }

        $exam = Exam::findOrFail($exam_code);
        $exam->is_results_published = $exam->is_results_published ? 0 : 1;
        $exam->save();

        $msg = $exam->is_results_published ? 'Exam results published to all candidates.' : 'Exam results unpublished.';
        return back()->with('success', $msg);
    }
}
