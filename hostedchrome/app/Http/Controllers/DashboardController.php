<?php

namespace App\Http\Controllers;

use App\Models\CandidateDraft;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Organization;
use App\Models\Submission;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::with(['organization', 'createdTeachers', 'students'])->find($userId);

        // 1. If User is PRINCIPAL / DEAN -> Show Institutional Dean Command Center
        if (in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return $this->principalDashboard($currentUser);
        }

        // 2. If User is TEACHER / PROCTOR -> Show Teacher Command Center
        if (in_array(strtoupper($userRole), ['PROCTOR', 'TEACHER', 'FACULTY'])) {
            return $this->teacherDashboard($currentUser);
        }

        // 3. Otherwise -> Super Admin Master Command Center
        $totalCandidates = User::where('role', 'CANDIDATE')->count();
        $totalTeachers = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->count();
        $totalPrincipals = User::whereIn('role', ['PRINCIPAL', 'DEAN', 'DIRECTOR'])->count();
        $totalOrganizations = Organization::count();
        $totalExams = Exam::count();
        $activeExamsCount = Exam::where('status', 'ACTIVE')->count();
        $totalSubmissions = Submission::count();
        $totalViolations = Violation::count();
        $activeDraftsCount = CandidateDraft::count();

        // Recent exams
        $recentExams = Exam::with(['teacher'])
            ->withCount(['questions', 'submissions', 'violations'])
            ->orderBy('exam_date', 'desc')
            ->limit(5)
            ->get();

        // Recent violations
        $recentViolations = Violation::with(['candidate', 'exam'])
            ->orderBy('timestamp', 'desc')
            ->limit(6)
            ->get();

        // Recent student submissions
        $recentSubmissions = Submission::with(['candidate', 'exam'])
            ->orderBy('submission_timestamp', 'desc')
            ->limit(5)
            ->get();

        // Organizations list for quick overview
        $organizations = Organization::withCount(['principals', 'teachers', 'students', 'exams'])->limit(4)->get();

        // Latest Consultation Inquiries (Public Leads)
        $latestInquiries = \App\Models\ContactInquiry::orderBy('created_at', 'desc')->limit(6)->get();
        $pendingInquiriesCount = \App\Models\ContactInquiry::where('status', 'PENDING')->count();
        $totalInquiriesCount = \App\Models\ContactInquiry::count();

        return view('dashboard.index', compact(
            'currentUser',
            'totalCandidates',
            'totalTeachers',
            'totalPrincipals',
            'totalOrganizations',
            'totalExams',
            'activeExamsCount',
            'totalSubmissions',
            'totalViolations',
            'activeDraftsCount',
            'recentExams',
            'recentViolations',
            'recentSubmissions',
            'organizations',
            'latestInquiries',
            'pendingInquiriesCount',
            'totalInquiriesCount'
        ));
    }

    private function principalDashboard($principal)
    {
        $currentUser = $principal;
        $orgId = $principal->org_id;
        $org = $principal->organization ?? Organization::find($orgId);

        // Teachers in this college
        $teachersQuery = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY']);
        if ($orgId) {
            $teachersQuery->where('org_id', $orgId);
        } else {
            $teachersQuery->where('created_by_principal_id', $principal->id);
        }
        $teachers = $teachersQuery->withCount(['students', 'createdExams'])->get();

        // Students in this college
        $studentsCount = User::where('role', 'CANDIDATE')
            ->where(function($q) use ($orgId, $principal) {
                if ($orgId) {
                    $q->where('org_id', $orgId)->orWhere('college_name', $principal->college_name);
                } else {
                    $q->where('college_name', $principal->college_name);
                }
            })->count();

        // Exams in this college
        $exams = Exam::where(function($q) use ($orgId, $principal) {
            if ($orgId) {
                $q->where('org_id', $orgId)->orWhere('college_name', $principal->college_name);
            } else {
                $q->where('college_name', $principal->college_name);
            }
        })->withCount(['questions', 'submissions', 'violations'])->get();

        $totalSubmissions = Submission::whereIn('exam_code', $exams->pluck('exam_code'))->count();
        $totalViolations = Violation::whereIn('exam_code', $exams->pluck('exam_code'))->count();

        return view('dashboard.principal', compact(
            'currentUser',
            'principal',
            'org',
            'teachers',
            'studentsCount',
            'exams',
            'totalSubmissions',
            'totalViolations'
        ));
    }

    private function teacherDashboard($teacher)
    {
        $currentUser = $teacher;

        // Students created by or assigned to this teacher
        $myStudents = User::where('role', 'CANDIDATE')
            ->where(function($q) use ($teacher) {
                $q->where('created_by_teacher_id', $teacher->id)
                  ->orWhere('college_name', $teacher->college_name);
            })
            ->get();

        $myStudentsCount = $myStudents->count();
        $maxStudentsAllowed = $teacher->max_students_allowed ?? 100;
        $studentQuotaPct = min(100, round(($myStudentsCount / max(1, $maxStudentsAllowed)) * 100));

        // Exams authored by this teacher vs visible in department
        $myAuthoredExamsCount = Exam::where('created_by_teacher_id', $teacher->id)->count();
        $myExamsCount = $myAuthoredExamsCount;
        $maxExamsAllowed = $teacher->max_exams_allowed ?? 10;
        $examQuotaPct = min(100, round(($myExamsCount / max(1, $maxExamsAllowed)) * 100));

        $myExams = Exam::where(function($q) use ($teacher) {
                $q->where('created_by_teacher_id', $teacher->id)
                  ->orWhere('college_name', $teacher->college_name);
            })
            ->withCount(['questions', 'submissions', 'violations'])
            ->orderBy('exam_date', 'desc')
            ->get();

        // Submissions for this teacher's exams and enrolled students
        $candidateIds = $myStudents->pluck('student_id')->concat($myStudents->pluck('id'))->filter()->unique()->toArray();
        $examCodes = $myExams->pluck('exam_code')->filter()->toArray();

        $mySubmissions = Submission::where(function($q) use ($examCodes, $candidateIds) {
                if (!empty($examCodes)) {
                    $q->whereIn('exam_code', $examCodes);
                }
                if (!empty($candidateIds)) {
                    $q->orWhereIn('candidate_id', $candidateIds);
                }
            })
            ->with(['candidate', 'exam'])
            ->orderBy('submission_timestamp', 'desc')
            ->get();

        $totalSubmissionsCount = $mySubmissions->count();
        if ($totalSubmissionsCount > 0) {
            $avgBatchScore = round($mySubmissions->avg('total_score'), 1);
            $maxScore = $mySubmissions->max('total_score') ?? 0;
            $passCount = $mySubmissions->where('total_score', '>=', 40)->count();
            $passRate = round(($passCount / $totalSubmissionsCount) * 100, 1);
        } else {
            // Calculate accurate metrics directly from candidate average scores
            $avgBatchScore = $myStudentsCount > 0 ? round($myStudents->avg('average_score') ?? 0, 1) : 0;
            $maxScore = $myStudentsCount > 0 ? round($myStudents->max('average_score') ?? 0, 1) : 0;
            $passCount = $myStudents->where('average_score', '>=', 40)->count();
            $passRate = $myStudentsCount > 0 ? round(($passCount / $myStudentsCount) * 100, 1) : 0;
        }

        // Violations logged for this teacher's exams or students
        $myViolations = Violation::where(function($q) use ($examCodes, $candidateIds) {
                if (!empty($examCodes)) {
                    $q->whereIn('exam_code', $examCodes);
                }
                if (!empty($candidateIds)) {
                    $q->orWhereIn('candidate_id', $candidateIds);
                }
            })
            ->with(['candidate', 'exam'])
            ->orderBy('timestamp', 'desc')
            ->limit(8)
            ->get();

        // Top Performing Students (re-indexed with values() so $index + 1 gives #1, #2, #3, ...)
        $topPerformers = $myStudents->sortByDesc('average_score')->values()->take(5);

        return view('dashboard.teacher', compact(
            'currentUser',
            'teacher',
            'myStudents',
            'myStudentsCount',
            'maxStudentsAllowed',
            'studentQuotaPct',
            'myExams',
            'myExamsCount',
            'maxExamsAllowed',
            'examQuotaPct',
            'mySubmissions',
            'totalSubmissionsCount',
            'avgBatchScore',
            'maxScore',
            'passRate',
            'myViolations',
            'topPerformers'
        ));
    }
}
