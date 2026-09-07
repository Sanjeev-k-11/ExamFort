<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Organization;
use App\Models\PlacementExam;
use App\Models\PlacementExamCandidate;
use App\Models\PlacementExamTeacher;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class PlacementExamController extends Controller
{
    /**
     * Get authenticated user and permission context
     */
    private function getAuthContext()
    {
        $userId = session('auth_user_id');
        $userRole = strtoupper(session('auth_user_role', ''));
        $user = User::find($userId);

        $isPrincipalOrAdmin = in_array($userRole, ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']);
        $isTeacher = in_array($userRole, ['TEACHER', 'FACULTY', 'PROCTOR']);

        return [$user, $userRole, $isPrincipalOrAdmin, $isTeacher];
    }

    /**
     * Display a listing of placement exams
     */
    public function index(Request $request)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();

        $search = $request->query('search');
        $status = $request->query('status');

        $query = PlacementExam::with(['principal', 'assignedTeachers.teacher', 'candidates'])
            ->orderBy('created_at', 'desc');

        if ($isPrincipalOrAdmin) {
            if ($user && $user->org_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('org_id', $user->org_id)
                      ->orWhereNull('org_id');
                });
            }
        } elseif ($isTeacher && $user) {
            // Teacher sees drives assigned to them or created by them
            $query->where(function ($q) use ($user) {
                $q->where('created_by_principal_id', $user->id)
                  ->orWhereHas('assignedTeachers', function ($tq) use ($user) {
                      $tq->where('teacher_id', $user->id);
                  });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('exam_code', 'like', "%{$search}%")
                  ->orWhere('job_role', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['DRAFT', 'SCHEDULED', 'ACTIVE', 'COMPLETED', 'ARCHIVED'])) {
            $query->where('status', $status);
        }

        $placementExams = $query->paginate(12);

        // Summary metrics
        $totalDrives = PlacementExam::count();
        $activeDrives = PlacementExam::where('status', 'ACTIVE')->count();
        $totalEnrolled = PlacementExamCandidate::count();
        $completedDrives = PlacementExam::where('status', 'COMPLETED')->count();

        return view('placement.index', compact(
            'placementExams', 'totalDrives', 'activeDrives', 'totalEnrolled', 'completedDrives',
            'search', 'status', 'user', 'isPrincipalOrAdmin', 'isTeacher'
        ));
    }

    /**
     * Show form to create a new Placement Exam
     */
    public function create()
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();

        if (!$isPrincipalOrAdmin && !$isTeacher) {
            return redirect()->route('placement-exams.index')->with('error', 'Unauthorized access.');
        }

        // Available teachers to assign
        $teachersQuery = User::whereIn('role', ['TEACHER', 'FACULTY', 'PROCTOR'])->where('status', 'ACTIVE');
        if ($user && $user->org_id) {
            $teachersQuery->where('org_id', $user->org_id);
        }
        $teachers = $teachersQuery->get();

        // Available students for eligibility selection
        $studentsQuery = User::where('role', 'CANDIDATE')->where('status', 'ACTIVE');
        if ($user && $user->org_id) {
            $studentsQuery->where('org_id', $user->org_id);
        }
        $students = $studentsQuery->get();

        // Distinct streams
        $streams = User::where('role', 'CANDIDATE')->whereNotNull('stream')->distinct()->pluck('stream');

        return view('placement.create', compact('teachers', 'students', 'streams', 'user', 'isPrincipalOrAdmin'));
    }

    /**
     * Store newly created Placement Exam & sync to exams table
     */
    public function store(Request $request)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();

        $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:150',
            'job_role' => 'required|string|max:150',
            'package_lpa' => 'required|string|max:50',
            'min_cgpa' => 'required|numeric|min:0|max:10',
            'exam_date' => 'required|string|max:50',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'duration_minutes' => 'required|integer|min:10|max:300',
            'total_marks' => 'required|integer|min:10|max:500',
        ]);

        $slugCompany = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($request->company_name, 0, 4)));
        $examCode = 'PLC-' . $slugCompany . '-' . date('Y') . '-' . strtoupper(Str::random(4));

        $cleanDate = $this->normalizeDate($request->exam_date);
        $cleanStart = $this->normalizeTime($request->start_time);
        $cleanEnd = $this->normalizeTime($request->end_time);

        $placementExam = PlacementExam::create([
            'exam_code' => $examCode,
            'title' => $request->title,
            'company_name' => $request->company_name,
            'company_logo_url' => $request->company_logo_url,
            'job_role' => $request->job_role,
            'package_lpa' => $request->package_lpa,
            'min_cgpa' => $request->min_cgpa,
            'eligibility_criteria' => $request->eligibility_criteria,
            'description' => $request->description,
            'drive_type' => $request->drive_type ?? 'ON_CAMPUS',
            'exam_date' => $cleanDate,
            'start_time' => $cleanStart,
            'end_time' => $cleanEnd,
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'total_questions' => 0,
            'status' => 'SCHEDULED',
            'face_verification_required' => $request->has('face_verification_required'),
            'is_code_only' => true,
            'created_by_principal_id' => $user ? $user->id : null,
            'org_id' => $user ? $user->org_id : null,
            'college_name' => $user ? ($user->college_name ?? ($user->organization->name ?? 'ExamFort')) : 'ExamFort',
        ]);

        // Sync to standard exams table for assessment engine & proctoring
        Exam::updateOrCreate(
            ['exam_code' => $examCode],
            [
                'title' => $request->title . ' (' . $request->company_name . ' Placement Drive)',
                'description' => $request->description ?? "Campus Placement Drive for {$request->company_name} - {$request->job_role} ({$request->package_lpa})",
                'category' => 'Campus Placement',
                'duration_minutes' => $request->duration_minutes,
                'total_marks' => $request->total_marks,
                'total_questions' => 0,
                'exam_date' => $cleanDate,
                'exam_time' => $cleanStart . ' - ' . $cleanEnd,
                'status' => 'ACTIVE',
                'is_results_published' => false,
                'created_by_teacher_id' => $user ? $user->id : null,
                'college_name' => $placementExam->college_name,
            ]
        );

        // Assign initial teachers if selected
        if ($request->has('assigned_teacher_ids') && is_array($request->assigned_teacher_ids)) {
            foreach ($request->assigned_teacher_ids as $teacherId) {
                PlacementExamTeacher::create([
                    'placement_exam_id' => $placementExam->id,
                    'teacher_id' => $teacherId,
                    'can_add_students' => true,
                    'can_create_questions' => true,
                    'assigned_by_id' => $user ? $user->id : null,
                ]);
            }
        }

        // Handle initial student enrollment if selected
        $enrollMode = $request->input('enroll_mode', 'none');
        $enrolledCount = 0;

        if ($enrollMode === 'manual' && $request->has('student_ids') && is_array($request->student_ids)) {
            $studentsToEnroll = User::whereIn('id', $request->student_ids)->where('role', 'CANDIDATE')->get();
            foreach ($studentsToEnroll as $stu) {
                $uniqueCode = PlacementExamCandidate::generateUniqueAccessCode($placementExam->id);
                PlacementExamCandidate::create([
                    'placement_exam_id' => $placementExam->id,
                    'exam_code' => $placementExam->exam_code,
                    'student_id' => $stu->student_id ?? $stu->id,
                    'full_name' => $stu->full_name,
                    'email' => $stu->email,
                    'phone' => $stu->phone,
                    'stream' => $stu->stream ?? 'CSE',
                    'course' => $stu->course ?? 'B.Tech',
                    'cgpa' => 7.50,
                    'access_code' => $uniqueCode,
                    'scheduled_date' => $placementExam->exam_date,
                    'scheduled_start_time' => $placementExam->start_time,
                    'scheduled_end_time' => $placementExam->end_time,
                    'is_rescheduled' => false,
                    'attempt_status' => 'PENDING',
                    'enrolled_by_id' => $user ? $user->id : null,
                ]);
                $enrolledCount++;
            }
        } elseif ($enrollMode === 'branch_filter') {
            $query = User::where('role', 'CANDIDATE')->where('status', 'ACTIVE');
            if ($placementExam->org_id) {
                $query->where('org_id', $placementExam->org_id);
            }
            if ($request->filled('filter_stream')) {
                $query->where('stream', $request->filter_stream);
            }
            $studentsToEnroll = $query->get();
            $minCgpa = (float)$request->input('min_cgpa', $placementExam->min_cgpa);

            foreach ($studentsToEnroll as $stu) {
                $uniqueCode = PlacementExamCandidate::generateUniqueAccessCode($placementExam->id);
                PlacementExamCandidate::create([
                    'placement_exam_id' => $placementExam->id,
                    'exam_code' => $placementExam->exam_code,
                    'student_id' => $stu->student_id ?? $stu->id,
                    'full_name' => $stu->full_name,
                    'email' => $stu->email,
                    'phone' => $stu->phone,
                    'stream' => $stu->stream ?? 'Engineering',
                    'course' => $stu->course ?? 'B.Tech',
                    'cgpa' => max(7.00, $minCgpa),
                    'access_code' => $uniqueCode,
                    'scheduled_date' => $placementExam->exam_date,
                    'scheduled_start_time' => $placementExam->start_time,
                    'scheduled_end_time' => $placementExam->end_time,
                    'is_rescheduled' => false,
                    'attempt_status' => 'PENDING',
                    'enrolled_by_id' => $user ? $user->id : null,
                ]);
                $enrolledCount++;
            }
        }

        $msg = "Placement Drive '{$placementExam->title}' created successfully!";
        if ($enrolledCount > 0) {
            $msg .= " ({$enrolledCount} eligible candidates enrolled with unique 6-digit access codes)";
        } else {
            $msg .= " (0 candidates enrolled - you can add/filter eligible students anytime from the Candidate Roster tab)";
        }

        return redirect()->route('placement-exams.show', $placementExam->id)->with('success', $msg);
    }

    /**
     * Show comprehensive Command Hub for a Placement Exam
     */
    public function show($id, Request $request)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();

        $placementExam = PlacementExam::with([
            'principal',
            'assignedTeachers.teacher',
            'candidates',
            'questions',
        ])->findOrFail($id);

        // Check permissions if teacher
        $teacherPerm = null;
        if ($isTeacher && $user) {
            $teacherPerm = $placementExam->assignedTeachers()->where('teacher_id', $user->id)->first();
            if (!$teacherPerm && $placementExam->created_by_principal_id !== $user->id && !$isPrincipalOrAdmin) {
                return redirect()->route('placement-exams.index')->with('error', 'You do not have permission to manage this placement exam.');
            }
        }

        $canAddStudents = $isPrincipalOrAdmin || ($teacherPerm && $teacherPerm->can_add_students) || ($placementExam->created_by_principal_id === ($user->id ?? ''));
        $canCreateQuestions = $isPrincipalOrAdmin || ($teacherPerm && $teacherPerm->can_create_questions) || ($placementExam->created_by_principal_id === ($user->id ?? ''));
        $canReschedule = $isPrincipalOrAdmin || ($teacherPerm && $teacherPerm->can_reschedule) || ($placementExam->created_by_principal_id === ($user->id ?? ''));

        // All active students for enrollment filter
        $enrolledStudentIds = $placementExam->candidates->pluck('student_id')->toArray();
        $allStudentsQuery = User::where('role', 'CANDIDATE')->where('status', 'ACTIVE');
        if ($placementExam->org_id) {
            $allStudentsQuery->where('org_id', $placementExam->org_id);
        }
        $availableStudents = $allStudentsQuery->get();

        // Distinct streams/branches
        $streams = User::where('role', 'CANDIDATE')->whereNotNull('stream')->distinct()->pluck('stream');

        // Available teachers not yet assigned
        $assignedTeacherIds = $placementExam->assignedTeachers->pluck('teacher_id')->toArray();
        $availableTeachers = User::whereIn('role', ['TEACHER', 'FACULTY', 'PROCTOR'])
            ->whereNotIn('id', $assignedTeacherIds)
            ->where('status', 'ACTIVE')
            ->get();

        // Live synchronization from submissions and violations
        $submissions = \App\Models\Submission::where('exam_code', $placementExam->exam_code)->get();
        if ($submissions->isNotEmpty()) {
            foreach ($submissions as $sub) {
                $cand = PlacementExamCandidate::where('placement_exam_id', $id)
                    ->where('student_id', $sub->candidate_id)
                    ->first();
                if ($cand) {
                    $violCount = \App\Models\Violation::where('exam_code', $placementExam->exam_code)
                        ->where('candidate_id', $sub->candidate_id)
                        ->count();
                    $trustScore = max(10, 100 - ($violCount * 15));

                    $cand->update([
                        'attempt_status' => 'COMPLETED',
                        'score' => $sub->total_score,
                        'mcq_score' => $sub->mcq_score,
                        'coding_score' => ($sub->coding_public_score ?? 0) + ($sub->coding_hidden_score ?? 0),
                        'essay_score' => $sub->essay_score,
                        'violations_count' => $violCount,
                        'trust_score' => $trustScore,
                        'submitted_at' => $sub->submission_timestamp ?? date('d M Y H:i'),
                    ]);
                }
            }
            // Reload candidates with fresh scores
            $placementExam->load('candidates');
        }

        $activeTab = $request->query('tab', 'candidates');

        return view('placement.show', compact(
            'placementExam', 'user', 'isPrincipalOrAdmin', 'isTeacher',
            'canAddStudents', 'canCreateQuestions', 'canReschedule', 'availableStudents',
            'enrolledStudentIds', 'streams', 'availableTeachers', 'activeTab'
        ));
    }

    /**
     * Edit Placement Exam Details
     */
    public function edit($id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        if (!$isPrincipalOrAdmin && $placementExam->created_by_principal_id !== ($user->id ?? '')) {
            return redirect()->route('placement-exams.show', $id)->with('error', 'Unauthorized.');
        }

        return view('placement.edit', compact('placementExam', 'user', 'isPrincipalOrAdmin'));
    }

    /**
     * Update Placement Exam
     */
    public function update(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:150',
            'job_role' => 'required|string|max:150',
            'package_lpa' => 'required|string|max:50',
            'min_cgpa' => 'required|numeric|min:0|max:10',
            'exam_date' => 'required|string|max:50',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'duration_minutes' => 'required|integer|min:10|max:300',
            'total_marks' => 'required|integer|min:10|max:500',
            'status' => 'required|in:DRAFT,SCHEDULED,ACTIVE,COMPLETED,ARCHIVED',
        ]);

        $cleanDate = $this->normalizeDate($request->exam_date);
        $cleanStart = $this->normalizeTime($request->start_time);
        $cleanEnd = $this->normalizeTime($request->end_time);

        $placementExam->update([
            'title' => $request->title,
            'company_name' => $request->company_name,
            'job_role' => $request->job_role,
            'package_lpa' => $request->package_lpa,
            'min_cgpa' => $request->min_cgpa,
            'eligibility_criteria' => $request->eligibility_criteria,
            'description' => $request->description,
            'drive_type' => $request->drive_type ?? 'ON_CAMPUS',
            'exam_date' => $cleanDate,
            'start_time' => $cleanStart,
            'end_time' => $cleanEnd,
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'status' => $request->status,
            'face_verification_required' => $request->has('face_verification_required'),
        ]);

        // Sync to exams table
        Exam::where('exam_code', $placementExam->exam_code)->update([
            'title' => $request->title . ' (' . $request->company_name . ' Placement Drive)',
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'exam_date' => $cleanDate,
            'exam_time' => $cleanStart . ' - ' . $cleanEnd,
            'status' => $request->status === 'ACTIVE' ? 'ACTIVE' : ($request->status === 'COMPLETED' ? 'COMPLETED' : 'ACTIVE'),
        ]);

        return redirect()->route('placement-exams.show', $placementExam->id)
            ->with('success', 'Placement Exam updated successfully.');
    }

    /**
     * Delete Placement Exam
     */
    public function destroy($id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        if (!$isPrincipalOrAdmin && $placementExam->created_by_principal_id !== ($user->id ?? '')) {
            return redirect()->route('placement-exams.index')->with('error', 'Unauthorized.');
        }

        $code = $placementExam->exam_code;
        $placementExam->delete();
        Exam::where('exam_code', $code)->delete();
        Question::where('exam_code', $code)->delete();

        return redirect()->route('placement-exams.index')->with('success', "Placement Exam '{$code}' deleted.");
    }

    // ==========================================
    // TEACHER DELEGATION & PERMISSIONS
    // ==========================================

    public function assignTeacher(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        if (!$isPrincipalOrAdmin && $placementExam->created_by_principal_id !== ($user->id ?? '')) {
            return back()->with('error', 'Only the Principal can assign faculty members.');
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        $exists = PlacementExamTeacher::where('placement_exam_id', $id)
            ->where('teacher_id', $request->teacher_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This faculty member is already assigned to this placement drive.');
        }

        PlacementExamTeacher::create([
            'placement_exam_id' => $id,
            'teacher_id' => $request->teacher_id,
            'can_add_students' => $request->has('can_add_students'),
            'can_create_questions' => $request->has('can_create_questions'),
            'can_reschedule' => $request->has('can_reschedule'),
            'assigned_by_id' => $user ? $user->id : null,
        ]);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'teachers'])
            ->with('success', 'Faculty member assigned with specified permissions.');
    }

    public function updateTeacherPermission(Request $request, $id, $assignmentId)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        if (!$isPrincipalOrAdmin && $placementExam->created_by_principal_id !== ($user->id ?? '')) {
            return back()->with('error', 'Only the Principal can update faculty permissions.');
        }

        $assignment = PlacementExamTeacher::where('placement_exam_id', $id)->findOrFail($assignmentId);
        $assignment->update([
            'can_add_students' => $request->has('can_add_students'),
            'can_create_questions' => $request->has('can_create_questions'),
            'can_reschedule' => $request->has('can_reschedule'),
        ]);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'teachers'])
            ->with('success', 'Faculty permissions updated.');
    }

    public function removeTeacher($id, $assignmentId)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        if (!$isPrincipalOrAdmin && $placementExam->created_by_principal_id !== ($user->id ?? '')) {
            return back()->with('error', 'Only the Principal can remove faculty members.');
        }

        $assignment = PlacementExamTeacher::where('placement_exam_id', $id)->findOrFail($assignmentId);
        $assignment->delete();

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'teachers'])
            ->with('success', 'Faculty member unassigned.');
    }

    // ==========================================
    // CANDIDATE ENROLLMENT & SECRET CODE GENERATION
    // ==========================================

    public function enrollStudents(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        // Check if user has permission to add students
        $canAdd = $isPrincipalOrAdmin || ($placementExam->created_by_principal_id === ($user->id ?? ''));
        if (!$canAdd && $user) {
            $perm = $placementExam->assignedTeachers()->where('teacher_id', $user->id)->first();
            $canAdd = $perm && $perm->can_add_students;
        }

        if (!$canAdd) {
            return back()->with('error', 'You do not have permission to add candidates to this exam.');
        }

        $enrollMode = $request->input('enroll_mode', 'manual'); // 'manual', 'cgpa_branch', 'all'
        $enrolledCount = 0;

        if ($enrollMode === 'manual') {
            $studentIds = $request->input('student_ids', []);
            if (empty($studentIds)) {
                return back()->with('error', 'Please select at least one student to enroll.');
            }

            $students = User::whereIn('id', $studentIds)->where('role', 'CANDIDATE')->get();
            foreach ($students as $stu) {
                // Check if already enrolled
                $exists = PlacementExamCandidate::where('placement_exam_id', $id)
                    ->where('student_id', $stu->student_id ?? $stu->id)
                    ->exists();

                if (!$exists) {
                    $uniqueCode = PlacementExamCandidate::generateUniqueAccessCode($id);
                    PlacementExamCandidate::create([
                        'placement_exam_id' => $id,
                        'exam_code' => $placementExam->exam_code,
                        'student_id' => $stu->student_id ?? $stu->id,
                        'full_name' => $stu->full_name,
                        'email' => $stu->email,
                        'phone' => $stu->phone,
                        'stream' => $stu->stream ?? 'CSE',
                        'course' => $stu->course ?? 'B.Tech',
                        'cgpa' => 7.50, // default if not on user model
                        'access_code' => $uniqueCode,
                        'scheduled_date' => $placementExam->exam_date,
                        'scheduled_start_time' => $placementExam->start_time,
                        'scheduled_end_time' => $placementExam->end_time,
                        'is_rescheduled' => false,
                        'attempt_status' => 'PENDING',
                        'enrolled_by_id' => $user ? $user->id : null,
                    ]);
                    $enrolledCount++;
                }
            }
        } elseif ($enrollMode === 'cgpa_branch' || $enrollMode === 'all') {
            $query = User::where('role', 'CANDIDATE')->where('status', 'ACTIVE');
            if ($placementExam->org_id) {
                $query->where('org_id', $placementExam->org_id);
            }

            if ($request->filled('filter_stream')) {
                $query->where('stream', $request->filter_stream);
            }

            $students = $query->get();
            $minCgpa = (float)$request->input('min_cgpa', $placementExam->min_cgpa);

            foreach ($students as $stu) {
                $exists = PlacementExamCandidate::where('placement_exam_id', $id)
                    ->where('student_id', $stu->student_id ?? $stu->id)
                    ->exists();

                if (!$exists) {
                    $uniqueCode = PlacementExamCandidate::generateUniqueAccessCode($id);
                    PlacementExamCandidate::create([
                        'placement_exam_id' => $id,
                        'exam_code' => $placementExam->exam_code,
                        'student_id' => $stu->student_id ?? $stu->id,
                        'full_name' => $stu->full_name,
                        'email' => $stu->email,
                        'phone' => $stu->phone,
                        'stream' => $stu->stream ?? 'Engineering',
                        'course' => $stu->course ?? 'B.Tech',
                        'cgpa' => max(7.00, $minCgpa),
                        'access_code' => $uniqueCode,
                        'scheduled_date' => $placementExam->exam_date,
                        'scheduled_start_time' => $placementExam->start_time,
                        'scheduled_end_time' => $placementExam->end_time,
                        'is_rescheduled' => false,
                        'attempt_status' => 'PENDING',
                        'enrolled_by_id' => $user ? $user->id : null,
                    ]);
                    $enrolledCount++;
                }
            }
        }

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'candidates'])
            ->with('success', "🎉 Successfully enrolled {$enrolledCount} eligible students with unique 6-digit access codes!");
    }

    public function quickAddCandidate(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $request->validate([
            'student_query' => 'required|string|max:100',
        ]);

        $queryStr = trim($request->student_query);
        $stu = User::where('role', 'CANDIDATE')
            ->where(function($q) use ($queryStr) {
                $q->where('student_id', $queryStr)
                  ->orWhere('id', $queryStr)
                  ->orWhere('email', $queryStr)
                  ->orWhere('full_name', 'LIKE', "%{$queryStr}%");
            })
            ->first();

        if (!$stu) {
            return back()->with('error', "No registered student found matching '{$queryStr}'.");
        }

        $exists = PlacementExamCandidate::where('placement_exam_id', $id)
            ->where('student_id', $stu->student_id ?? $stu->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "Student {$stu->full_name} ({$stu->student_id}) is already enrolled in this placement drive.");
        }

        $uniqueCode = PlacementExamCandidate::generateUniqueAccessCode($id);
        PlacementExamCandidate::create([
            'placement_exam_id' => $id,
            'exam_code' => $placementExam->exam_code,
            'student_id' => $stu->student_id ?? $stu->id,
            'full_name' => $stu->full_name,
            'email' => $stu->email,
            'phone' => $stu->phone,
            'stream' => $stu->stream ?? 'CSE',
            'course' => $stu->course ?? 'B.Tech',
            'cgpa' => 8.00,
            'access_code' => $uniqueCode,
            'scheduled_date' => $placementExam->exam_date,
            'scheduled_start_time' => $placementExam->start_time,
            'scheduled_end_time' => $placementExam->end_time,
            'is_rescheduled' => false,
            'attempt_status' => 'PENDING',
            'enrolled_by_id' => $user ? $user->id : null,
        ]);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'candidates'])
            ->with('success', "🎉 Student {$stu->full_name} enrolled successfully! Secret Access Code: {$uniqueCode}");
    }

    public function clearAllCandidates($id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $count = PlacementExamCandidate::where('placement_exam_id', $id)->count();
        PlacementExamCandidate::where('placement_exam_id', $id)->delete();

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'candidates'])
            ->with('success', "Cleared all {$count} candidates from placement drive roster. You can now enroll specific eligible candidates.");
    }

    public function removeCandidate($id, $candidateId)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $candidate = PlacementExamCandidate::where('placement_exam_id', $id)->findOrFail($candidateId);
        $candidate->delete();

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'candidates'])
            ->with('success', 'Candidate removed from placement drive roster.');
    }

    public function regenerateCandidateCode($id, $candidateId)
    {
        $placementExam = PlacementExam::findOrFail($id);
        $candidate = PlacementExamCandidate::where('placement_exam_id', $id)->findOrFail($candidateId);

        $newCode = PlacementExamCandidate::generateUniqueAccessCode($id);
        $candidate->update([
            'access_code' => $newCode,
        ]);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'candidates'])
            ->with('success', "New unique 6-digit access code generated for {$candidate->full_name}: {$newCode}");
    }

    // ==========================================
    // RESCHEDULING (PER-STUDENT & GLOBAL DRIVE)
    // ==========================================

    public function rescheduleCandidate(Request $request, $id, $candidateId)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $canReschedule = $isPrincipalOrAdmin || ($placementExam->created_by_principal_id === ($user->id ?? ''));
        if (!$canReschedule && $user && $isTeacher) {
            $perm = $placementExam->assignedTeachers()->where('teacher_id', $user->id)->first();
            $canReschedule = $perm && $perm->can_reschedule;
        }

        if (!$canReschedule) {
            return back()->with('error', '🔒 Access Denied: Only the Principal or faculty explicitly granted Reschedule permission can reschedule candidate exams.');
        }

        $candidate = PlacementExamCandidate::where('placement_exam_id', $id)->findOrFail($candidateId);

        $request->validate([
            'scheduled_date' => 'required|string|max:50',
            'scheduled_start_time' => 'required|string|max:20',
            'scheduled_end_time' => 'required|string|max:20',
            'rescheduled_reason' => 'nullable|string|max:255',
        ]);

        $cleanDate = $this->normalizeDate($request->scheduled_date);
        $cleanStart = $this->normalizeTime($request->scheduled_start_time);
        $cleanEnd = $this->normalizeTime($request->scheduled_end_time);

        $updateData = [
            'scheduled_date' => $cleanDate,
            'scheduled_start_time' => $cleanStart,
            'scheduled_end_time' => $cleanEnd,
            'is_rescheduled' => true,
            'rescheduled_reason' => $request->rescheduled_reason ?? 'Individual candidate reschedule requested by Principal / Faculty.',
        ];

        if ($request->has('reset_attempt')) {
            $updateData['attempt_status'] = 'PENDING';
            $updateData['score'] = null;
        }

        if ($request->has('regenerate_code')) {
            $updateData['access_code'] = PlacementExamCandidate::generateUniqueAccessCode($id);
        }

        $candidate->update($updateData);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'candidates'])
            ->with('success', "✅ Exam rescheduled specifically for candidate {$candidate->full_name} to {$cleanDate} ({$cleanStart} - {$cleanEnd}).");
    }

    public function rescheduleDrive(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $canReschedule = $isPrincipalOrAdmin || ($placementExam->created_by_principal_id === ($user->id ?? ''));
        if (!$canReschedule && $user && $isTeacher) {
            $perm = $placementExam->assignedTeachers()->where('teacher_id', $user->id)->first();
            $canReschedule = $perm && $perm->can_reschedule;
        }

        if (!$canReschedule) {
            return back()->with('error', '🔒 Access Denied: Only the Principal or faculty explicitly granted Reschedule permission can reschedule the whole placement drive.');
        }

        $request->validate([
            'exam_date' => 'required|string|max:50',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
        ]);

        $cleanDate = $this->normalizeDate($request->exam_date);
        $cleanStart = $this->normalizeTime($request->start_time);
        $cleanEnd = $this->normalizeTime($request->end_time);

        $placementExam->update([
            'exam_date' => $cleanDate,
            'start_time' => $cleanStart,
            'end_time' => $cleanEnd,
        ]);

        // Sync to exams table
        Exam::where('exam_code', $placementExam->exam_code)->update([
            'exam_date' => $cleanDate,
            'exam_time' => $cleanStart . ' - ' . $cleanEnd,
        ]);

        // If requested, also update all non-customized candidate schedules
        if ($request->has('apply_to_all_candidates')) {
            PlacementExamCandidate::where('placement_exam_id', $id)
                ->where('is_rescheduled', false)
                ->update([
                    'scheduled_date' => $cleanDate,
                    'scheduled_start_time' => $cleanStart,
                    'scheduled_end_time' => $cleanEnd,
                ]);
        }

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'reschedule'])
            ->with('success', "✅ Placement Drive schedule updated to {$cleanDate} ({$cleanStart} - {$cleanEnd}).");
    }

    // ==========================================
    // EXCEL / CSV EXPORT OF CANDIDATE ACCESS CODES
    // ==========================================

    public function exportCodesCsv($id)
    {
        $placementExam = PlacementExam::with('candidates')->findOrFail($id);

        $filename = "Placement_Codes_{$placementExam->company_name}_{$placementExam->exam_code}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($placementExam) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['EXAMFORT INSTITUTIONAL PLACEMENT DRIVE - CANDIDATE ACCESS CODES']);
            fputcsv($handle, ['Company:', $placementExam->company_name, 'Job Role:', $placementExam->job_role, 'Package (CTC):', $placementExam->package_lpa]);
            fputcsv($handle, ['Exam Title:', $placementExam->title, 'Exam Code:', $placementExam->exam_code]);
            fputcsv($handle, ['Drive Date:', $placementExam->exam_date, 'Default Window:', $placementExam->start_time . ' - ' . $placementExam->end_time]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'S.No',
                'Student Roll / ID',
                'Candidate Full Name',
                'Email Address',
                'Phone Number',
                'Branch / Stream',
                'Course',
                'Eligibility / CGPA',
                'SECRET 6-DIGIT ACCESS CODE',
                'Scheduled Date',
                'Scheduled Start Time',
                'Scheduled End Time',
                'Individually Rescheduled',
                'Attempt Status',
            ]);

            $index = 1;
            foreach ($placementExam->candidates as $cand) {
                fputcsv($handle, [
                    $index++,
                    $cand->student_id,
                    $cand->full_name,
                    $cand->email,
                    $cand->phone,
                    $cand->stream,
                    $cand->course,
                    $cand->cgpa,
                    $cand->access_code,
                    $cand->scheduled_date ?? $placementExam->exam_date,
                    $cand->scheduled_start_time ?? $placementExam->start_time,
                    $cand->scheduled_end_time ?? $placementExam->end_time,
                    $cand->is_rescheduled ? 'YES' : 'NO',
                    $cand->attempt_status,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    // ==========================================
    // EXCEL / CSV EXPORT OF COMPLETE RESULTS & STUDENT DETAILS
    // ==========================================

    public function exportResultsCsv($id)
    {
        $placementExam = PlacementExam::with('candidates')->findOrFail($id);

        // Sort candidates by score descending for merit list
        $candidates = $placementExam->candidates->sortByDesc('score');

        $filename = "Placement_Results_MeritList_{$placementExam->company_name}_{$placementExam->exam_code}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($placementExam, $candidates) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['EXAMFORT INSTITUTIONAL PLACEMENT DRIVE - COMPLETE CANDIDATE EVALUATION & MERIT LIST']);
            fputcsv($handle, ['Company / Recruiter:', $placementExam->company_name, 'Job Role:', $placementExam->job_role, 'Package (CTC):', $placementExam->package_lpa]);
            fputcsv($handle, ['Exam Code:', $placementExam->exam_code, 'Total Maximum Marks:', $placementExam->total_marks, 'Duration (Min):', $placementExam->duration_minutes]);
            fputcsv($handle, ['Report Generated On:', date('d M Y H:i:s'), 'College / Institute:', $placementExam->college_name ?? 'ExamFort Partner College']);
            fputcsv($handle, []);

            // Column Headers with All Student Details & Score Breakdown
            fputcsv($handle, [
                'Rank / Merit Position',
                'Student Roll / ID',
                'Candidate Full Name',
                'Email Address',
                'Phone Number',
                'College Name',
                'Branch / Stream',
                'Course Degree',
                'Academic CGPA',
                'Unique 6-Digit Access Code',
                'Attempt Status',
                'Total Score Obtained',
                'Total Max Marks',
                'Score Percentage (%)',
                'MCQ Aptitude Score',
                'Coding Challenges Score',
                'Descriptive / Essay Score',
                'AI Proctoring Trust Score (%)',
                'Threat Violations Count',
                'Submission Date & Time',
                'SHORTLIST / INTERVIEW STATUS',
            ]);

            $rank = 1;
            foreach ($candidates as $cand) {
                $totalScore = $cand->score !== null ? $cand->score : 0;
                $pct = $placementExam->total_marks > 0 && $cand->score !== null
                    ? round(($cand->score / $placementExam->total_marks) * 100, 2)
                    : 0;

                fputcsv($handle, [
                    $cand->score !== null ? $rank++ : 'Unattempted',
                    $cand->student_id,
                    $cand->full_name,
                    $cand->email,
                    $cand->phone,
                    $cand->student->college_name ?? ($placementExam->college_name ?? 'N/A'),
                    $cand->stream,
                    $cand->course,
                    $cand->cgpa,
                    $cand->access_code,
                    $cand->attempt_status,
                    $cand->score !== null ? $cand->score : 'N/A',
                    $placementExam->total_marks,
                    $cand->score !== null ? $pct . '%' : 'N/A',
                    $cand->mcq_score !== null ? $cand->mcq_score : 'N/A',
                    $cand->coding_score !== null ? $cand->coding_score : 'N/A',
                    $cand->essay_score !== null ? $cand->essay_score : 'N/A',
                    $cand->trust_score !== null ? $cand->trust_score . '%' : '100%',
                    $cand->violations_count ?? 0,
                    $cand->submitted_at ?? 'N/A',
                    str_replace('_', ' ', $cand->shortlist_status ?? 'PENDING'),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function updateCandidateShortlist(Request $request, $id, $candidateId)
    {
        $placementExam = PlacementExam::findOrFail($id);
        $candidate = PlacementExamCandidate::where('placement_exam_id', $id)->findOrFail($candidateId);

        $request->validate([
            'shortlist_status' => 'required|in:PENDING,SHORTLISTED,INTERVIEW_ROUND_1,INTERVIEW_ROUND_2,HIRED,REJECTED',
        ]);

        $candidate->update([
            'shortlist_status' => $request->shortlist_status,
        ]);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'results'])
            ->with('success', "Candidate {$candidate->full_name} status updated to " . str_replace('_', ' ', $request->shortlist_status) . ".");
    }

    // ==========================================
    // QUESTION BANK & GEMINI AI QUESTION GENERATOR
    // ==========================================

    public function storeQuestion(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $request->validate([
            'type' => 'required|in:MCQ,PARAGRAPH,CODING',
            'title' => 'required|string|max:255',
            'question_text' => 'required|string',
            'max_marks' => 'required|numeric|min:1',
        ]);

        $nextQNum = Question::where('exam_code', $placementExam->exam_code)->max('question_number') + 1;

        $qData = [
            'exam_code' => $placementExam->exam_code,
            'question_number' => $nextQNum,
            'type' => $request->type,
            'title' => $request->title,
            'question_text' => $request->question_text,
            'max_marks' => (float)$request->max_marks,
            'explanation' => $request->explanation,
        ];

        if ($request->type === 'MCQ') {
            $options = array_values(array_filter($request->input('options', [])));
            $qData['options'] = $options;
            $qData['correct_answer'] = $request->correct_answer;
        } elseif ($request->type === 'CODING') {
            $qData['sample_input'] = $request->sample_input;
            $qData['sample_output'] = $request->sample_output;
            $qData['constraints'] = $request->constraints;
            $qData['entry_function'] = $request->entry_function ?? 'solve';
            $qData['coding_starter_code'] = [
                'cpp' => $request->starter_cpp ?? "// Write C++ solution here\n#include <iostream>\nusing namespace std;\n\nint main() {\n    // your code\n    return 0;\n}",
                'python' => $request->starter_py ?? "# Write Python solution here\ndef solve():\n    pass\n",
                'java' => $request->starter_java ?? "// Write Java solution here\nimport java.util.*;\n\npublic class Solution {\n    public static void main(String[] args) {\n        // your code\n    }\n}"
            ];

            $publicTests = [];
            if ($request->sample_input && $request->sample_output) {
                $publicTests[] = [
                    'input' => $request->sample_input,
                    'expected_output' => $request->sample_output,
                    'explanation' => 'Sample test case'
                ];
            }
            $qData['public_test_cases'] = $publicTests;
            $qData['hidden_test_cases'] = [];
            $qData['public_weightage_marks'] = (float)$request->max_marks * 0.4;
            $qData['hidden_weightage_marks'] = (float)$request->max_marks * 0.6;
        } elseif ($request->type === 'PARAGRAPH') {
            $qData['rubric_json'] = [
                'max_score' => (float)$request->max_marks,
                'criteria' => [
                    ['name' => 'Conceptual Clarity & Technical Depth', 'weight' => 50],
                    ['name' => 'Structure, Logic & Correctness', 'weight' => 50],
                ]
            ];
        }

        Question::create($qData);

        // Update counts
        $totalQ = Question::where('exam_code', $placementExam->exam_code)->count();
        $placementExam->update(['total_questions' => $totalQ]);
        Exam::where('exam_code', $placementExam->exam_code)->update(['total_questions' => $totalQ]);

        return redirect()->route('placement-exams.show', [$id, 'tab' => 'questions'])
            ->with('success', "Question #{$nextQNum} added to Placement Exam Bank.");
    }

    public function generateAiQuestions(Request $request, $id)
    {
        [$user, $userRole, $isPrincipalOrAdmin, $isTeacher] = $this->getAuthContext();
        $placementExam = PlacementExam::findOrFail($id);

        $request->validate([
            'domain_focus' => 'required|string|max:255',
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
            return response()->json(['success' => false, 'message' => 'Please select at least 1 question to generate.'], 422);
        }

        // Get Gemini API Key
        $apiKey = $user->gemini_api_key 
            ?? ($user->organization->gemini_api_key ?? null)
            ?? env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Google Gemini API Key is missing. Please save your Gemini API Key in the Principal Command Center first.'
            ], 400);
        }

        $systemPrompt = <<<EOT
You are an expert technical interviewer and placement test setter for top tech companies like {$placementExam->company_name}.
Generate a comprehensive campus placement test paper tailored for the role "{$placementExam->job_role}" (Package: {$placementExam->package_lpa}).
Domain / Focus: {$request->domain_focus}
Difficulty Level: {$request->difficulty}
Requirements:
- Number of MCQs (Quantitative Aptitude, Logical Reasoning, Core CS / Technical): {$mcqCount}
- Number of Coding Challenges (Algorithms, Data Structures, Real-world problems): {$codingCount}
- Number of Descriptive / Technical Essay Questions: {$paragraphCount}

You MUST return ONLY a valid JSON object matching this schema:
{
  "mcqs": [
    {
      "title": "Short title",
      "question_text": "Detailed question text",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "correct_answer": "Exact matching option text",
      "explanation": "Step-by-step reasoning",
      "marks": 2
    }
  ],
  "coding": [
    {
      "title": "Problem Title",
      "question_text": "Problem description with examples",
      "sample_input": "Sample input",
      "sample_output": "Sample output",
      "constraints": "1 <= N <= 10^5",
      "entry_function": "solve",
      "starter_python": "def solve(n, arr):\n    pass",
      "starter_cpp": "#include <iostream>\\nusing namespace std;\\nint main() { return 0; }",
      "starter_java": "import java.util.*;\\npublic class Solution { public static void main(String[] args){} }",
      "public_test_cases": [
        {"input": "3\\n1 2 3", "expected_output": "6", "explanation": "Sum is 6"}
      ],
      "hidden_test_cases": [
        {"input": "5\\n10 20 30 40 50", "expected_output": "150"}
      ],
      "marks": 10
    }
  ],
  "paragraph": [
    {
      "title": "System Design / Architecture / Core Concept Question",
      "question_text": "Detailed problem scenario",
      "max_marks": 5,
      "rubric": [
        {"name": "Architecture & Flow Clarity", "weight": 50},
        {"name": "Trade-offs & Scalability", "weight": 50}
      ]
    }
  ]
}
Return ONLY pure JSON. Do not include markdown code block backticks.
EOT;

        try {
            $modelsToTry = [
                'gemini-3.6-flash',
                'gemini-3.5-flash',
                'gemini-flash-latest',
                'gemini-3.7-flash',
                'gemini-3.1-flash-lite',
                'gemini-3.8-flash'
            ];

        $lastError = 'Unknown Gemini API error';
        $successfulResponse = null;

        foreach ($modelsToTry as $model) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $res = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(60)
                    ->post($endpoint, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $systemPrompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.3,
                            'maxOutputTokens' => 8192,
                        ]
                    ]);

                if ($res->successful()) {
                    $successfulResponse = $res->json();
                    break;
                } else {
                    $errData = $res->json();
                    $lastError = $errData['error']['message'] ?? ('HTTP ' . $res->status());
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

        $rawBody = $successfulResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $cleanedJson = trim($rawBody);
            if (str_starts_with($cleanedJson, '```json')) {
                $cleanedJson = substr($cleanedJson, 7);
            } elseif (str_starts_with($cleanedJson, '```')) {
                $cleanedJson = substr($cleanedJson, 3);
            }
            if (str_ends_with($cleanedJson, '```')) {
                $cleanedJson = substr($cleanedJson, 0, -3);
            }
            $cleanedJson = trim($cleanedJson);

            $parsed = json_decode($cleanedJson, true);
            if (!$parsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse AI question output. Please try again.',
                    'raw' => $rawBody
                ], 500);
            }

            // Save questions to database
            $startQNum = Question::where('exam_code', $placementExam->exam_code)->max('question_number') ?? 0;
            $savedCount = 0;

            // 1. MCQs
            if (!empty($parsed['mcqs']) && is_array($parsed['mcqs'])) {
                foreach ($parsed['mcqs'] as $mcq) {
                    $startQNum++;
                    Question::create([
                        'exam_code' => $placementExam->exam_code,
                        'question_number' => $startQNum,
                        'type' => 'MCQ',
                        'title' => $mcq['title'] ?? "Aptitude / Technical MCQ #{$startQNum}",
                        'question_text' => $mcq['question_text'] ?? '',
                        'options' => $mcq['options'] ?? [],
                        'correct_answer' => $mcq['correct_answer'] ?? '',
                        'explanation' => $mcq['explanation'] ?? '',
                        'max_marks' => (float)($mcq['marks'] ?? 2),
                    ]);
                    $savedCount++;
                }
            }

            // 2. Coding
            if (!empty($parsed['coding']) && is_array($parsed['coding'])) {
                foreach ($parsed['coding'] as $codeQ) {
                    $startQNum++;
                    $marks = (float)($codeQ['marks'] ?? 10);
                    Question::create([
                        'exam_code' => $placementExam->exam_code,
                        'question_number' => $startQNum,
                        'type' => 'CODING',
                        'title' => $codeQ['title'] ?? "Coding Challenge #{$startQNum}",
                        'question_text' => $codeQ['question_text'] ?? '',
                        'sample_input' => $codeQ['sample_input'] ?? '',
                        'sample_output' => $codeQ['sample_output'] ?? '',
                        'constraints' => $codeQ['constraints'] ?? '1 <= N <= 10^5',
                        'entry_function' => $codeQ['entry_function'] ?? 'solve',
                        'coding_starter_code' => [
                            'python' => $codeQ['starter_python'] ?? "def solve():\n    pass",
                            'cpp' => $codeQ['starter_cpp'] ?? "#include <iostream>\nusing namespace std;\nint main() { return 0; }",
                            'java' => $codeQ['starter_java'] ?? "import java.util.*;\npublic class Solution { public static void main(String[] args) {} }"
                        ],
                        'public_test_cases' => $codeQ['public_test_cases'] ?? [],
                        'hidden_test_cases' => $codeQ['hidden_test_cases'] ?? [],
                        'public_weightage_marks' => $marks * 0.4,
                        'hidden_weightage_marks' => $marks * 0.6,
                        'max_marks' => $marks,
                    ]);
                    $savedCount++;
                }
            }

            // 3. Paragraph / Descriptive
            if (!empty($parsed['paragraph']) && is_array($parsed['paragraph'])) {
                foreach ($parsed['paragraph'] as $paraQ) {
                    $startQNum++;
                    $marks = (float)($paraQ['max_marks'] ?? 5);
                    Question::create([
                        'exam_code' => $placementExam->exam_code,
                        'question_number' => $startQNum,
                        'type' => 'PARAGRAPH',
                        'title' => $paraQ['title'] ?? "Technical Scenario #{$startQNum}",
                        'question_text' => $paraQ['question_text'] ?? '',
                        'max_marks' => $marks,
                        'rubric_json' => [
                            'max_score' => $marks,
                            'criteria' => $paraQ['rubric'] ?? [
                                ['name' => 'Concept & Depth', 'weight' => 50],
                                ['name' => 'Logic & Completeness', 'weight' => 50]
                            ]
                        ]
                    ]);
                    $savedCount++;
                }
            }

            // Update question totals
            $totalQ = Question::where('exam_code', $placementExam->exam_code)->count();
            $placementExam->update(['total_questions' => $totalQ]);
            Exam::where('exam_code', $placementExam->exam_code)->update(['total_questions' => $totalQ]);

            return response()->json([
                'success' => true,
                'message' => "✨ Successfully generated and saved {$savedCount} placement questions using Gemini AI!",
                'saved_count' => $savedCount,
                'total_questions' => $totalQ,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI Generation Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    private function normalizeDate($dateStr)
    {
        if (!$dateStr) return date('d M Y');
        $ts = strtotime($dateStr);
        return $ts ? date('d M Y', $ts) : $dateStr;
    }

    private function normalizeTime($timeStr)
    {
        if (!$timeStr) return '10:00 AM';
        $ts = strtotime($timeStr);
        return $ts ? date('h:i A', $ts) : $timeStr;
    }
}
