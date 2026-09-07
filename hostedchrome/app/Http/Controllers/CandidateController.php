<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\StudentSubjectTeacher;
use App\Models\User;
use App\Models\Submission;
use App\Models\Violation;
use App\Models\StudentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role', 'CANDIDATE');
        $search = $request->query('search');
        $authUserId = session('auth_user_id');
        $authUserRole = session('auth_user_role');
        $currentUser = User::find($authUserId);

        $query = User::withCount(['submissions', 'violations'])
            ->with(['teacher', 'subjectTeachers.teacher', 'subjectTeachers.course'])
            ->where('role', $role);

        // If teacher, show students created by teacher OR assigned to teacher for any subject
        if (in_array(strtoupper($authUserRole), ['PROCTOR', 'TEACHER', 'FACULTY']) && $currentUser) {
            $assignedStudentIds = StudentSubjectTeacher::where('teacher_id', $currentUser->id)->pluck('student_id');
            $query->where(function($q) use ($currentUser, $assignedStudentIds) {
                $q->where('created_by_teacher_id', $currentUser->id)
                  ->orWhereIn('id', $assignedStudentIds)
                  ->orWhere('college_name', $currentUser->college_name);
            });
        }

        // If principal, show students belonging to this principal's college
        if (in_array(strtoupper($authUserRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR']) && $currentUser) {
            $query->where(function($q) use ($currentUser) {
                if ($currentUser->org_id) {
                    $q->where('org_id', $currentUser->org_id);
                }
                $q->orWhere('college_name', $currentUser->college_name);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('college_name', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('student_id', 'asc')->paginate(15);

        // Get available teachers in same college
        $teachersQuery = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY']);
        if ($currentUser && $currentUser->isPrincipal()) {
            if ($currentUser->org_id) {
                $teachersQuery->where('org_id', $currentUser->org_id);
            } else {
                $teachersQuery->where('college_name', $currentUser->college_name);
            }
        }
        $teachers = $teachersQuery->get();

        return view('candidates.index', compact('users', 'role', 'search', 'teachers', 'currentUser'));
    }

    public function show($id)
    {
        $authUserId = session('auth_user_id');
        $authUserRole = session('auth_user_role');
        $currentUser = User::find($authUserId);

        $user = User::with([
            'submissions.exam',
            'violations.exam',
            'drafts.exam',
            'activities',
            'teacher',
            'subjectTeachers.teacher',
            'subjectTeachers.course'
        ])->findOrFail($id);

        // Available teachers and courses for Principal delegation
        $teachersQuery = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY']);
        if ($currentUser && $currentUser->isPrincipal()) {
            if ($currentUser->org_id) {
                $teachersQuery->where('org_id', $currentUser->org_id);
            } else {
                $teachersQuery->where('college_name', $currentUser->college_name);
            }
        }
        $availableTeachers = $teachersQuery->get();
        $availableCourses = Course::all();

        return view('candidates.show', compact('user', 'currentUser', 'availableTeachers', 'availableCourses'));
    }

    public function assignSubjectTeacher(Request $request, $student_id)
    {
        $authUserId = session('auth_user_id');
        $authUserRole = session('auth_user_role');
        $currentUser = User::find($authUserId);

        if (!in_array(strtoupper($authUserRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Only Principal and Super Admin can assign subject faculty to students.');
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_name' => 'required|string|max:150',
            'course_id' => 'nullable|string|max:50',
            'academic_term' => 'nullable|string|max:100',
        ]);

        $student = User::findOrFail($student_id);
        $teacher = User::findOrFail($request->teacher_id);

        StudentSubjectTeacher::updateOrCreate(
            ['student_id' => $student_id, 'subject_name' => trim($request->subject_name)],
            [
                'teacher_id' => $request->teacher_id,
                'course_id' => $request->course_id,
                'assigned_by_principal_id' => $currentUser->id,
                'academic_term' => $request->academic_term ?? 'Current Semester',
                'assigned_at' => now(),
            ]
        );

        return back()->with('success', "Assigned Prof. {$teacher->full_name} as {$request->subject_name} teacher for {$student->full_name}.");
    }

    public function unassignSubjectTeacher($student_id, $assignment_id)
    {
        $authUserRole = session('auth_user_role');

        if (!in_array(strtoupper($authUserRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Only Principal and Super Admin can remove subject faculty assignments.');
        }

        StudentSubjectTeacher::where('student_id', $student_id)->where('id', $assignment_id)->delete();

        return back()->with('success', 'Subject faculty assignment removed.');
    }

    public function create()
    {
        $authUserId = session('auth_user_id');
        $authUserRole = session('auth_user_role');
        $currentUser = User::find($authUserId);

        // Check teacher permission & quota
        if (in_array(strtoupper($authUserRole), ['PROCTOR', 'TEACHER', 'FACULTY']) && $currentUser) {
            if (!$currentUser->canEnrollStudents()) {
                return redirect()->route('candidates.index')->with('error', 'Access Restricted: You do not have permission to enroll or add students. Please contact your Principal.');
            }

            $currentStudentsCount = User::where('created_by_teacher_id', $currentUser->id)->count();
            $maxAllowed = $currentUser->max_students_allowed ?? 100;
            if ($currentStudentsCount >= $maxAllowed) {
                return redirect()->route('candidates.index')->with('error', "Student Capacity Limit Reached: You have registered {$currentStudentsCount}/{$maxAllowed} students. Request your Principal for a quota increase.");
            }
        }

        $teachers = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->get();

        return view('candidates.create', compact('currentUser', 'teachers'));
    }

    public function store(Request $request)
    {
        $authUserId = session('auth_user_id');
        $authUserRole = session('auth_user_role');
        $currentUser = User::find($authUserId);

        if (in_array(strtoupper($authUserRole), ['PROCTOR', 'TEACHER', 'FACULTY']) && $currentUser) {
            if (!$currentUser->canEnrollStudents()) {
                return redirect()->route('candidates.index')->with('error', 'Access Restricted: You do not have permission to enroll or add students. Please contact your Principal.');
            }

            $currentStudentsCount = User::where('created_by_teacher_id', $currentUser->id)->count();
            $maxAllowed = $currentUser->max_students_allowed ?? 100;
            if ($currentStudentsCount >= $maxAllowed) {
                return redirect()->route('candidates.index')->with('error', "Quota Exceeded: Cannot add more students.");
            }
        }

        $request->validate([
            'student_id' => 'required|unique:users,student_id|max:50',
            'full_name' => 'required|max:150',
            'email' => 'required|email|unique:users,email|max:150',
            'phone' => 'nullable|max:30',
            'college_name' => 'required|max:255',
            'course' => 'nullable|max:100',
            'stream' => 'nullable|max:100',
            'password' => 'required|min:4',
            'access_code' => 'nullable|max:20',
        ]);

        $customId = 'CAND_' . strtoupper(Str::random(6));
        $teacherId = $currentUser && $currentUser->isTeacher() ? $currentUser->id : $request->created_by_teacher_id;
        $principalId = $currentUser && $currentUser->isPrincipal() ? $currentUser->id : null;
        $orgId = $currentUser ? $currentUser->org_id : null;

        User::create([
            'id' => $customId,
            'org_id' => $orgId,
            'created_by_principal_id' => $principalId,
            'created_by_teacher_id' => $teacherId,
            'student_id' => trim($request->student_id),
            'full_name' => $request->full_name,
            'email' => trim($request->email),
            'phone' => $request->phone,
            'college_name' => $request->college_name,
            'course' => $request->course ?? 'B.Tech Computer Science',
            'stream' => $request->stream ?? 'CSE',
            'batch_years' => $request->batch_years ?? '2022-2026',
            'bio' => $request->bio,
            'role' => 'CANDIDATE',
            'password' => $request->password,
            'access_code' => $request->access_code ?? '123456',
            'profile_completion_pct' => 100,
            'exams_enrolled' => 0,
            'exams_completed' => 0,
            'average_score' => 0,
            'best_score' => 0,
            'current_streak_days' => 0,
            'status' => 'ACTIVE',
        ]);

        return redirect()->route('candidates.index')->with('success', "Student '{$request->full_name}' enrolled successfully.");
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $teachers = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->get();
        return view('candidates.edit', compact('user', 'teachers'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'student_id' => 'required|max:50|unique:users,student_id,' . $id . ',id',
            'full_name' => 'required|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $id . ',id',
            'college_name' => 'required|max:255',
        ]);

        $updateData = [
            'student_id' => trim($request->student_id),
            'full_name' => $request->full_name,
            'email' => trim($request->email),
            'phone' => $request->phone,
            'college_name' => $request->college_name,
            'course' => $request->course,
            'stream' => $request->stream,
            'batch_years' => $request->batch_years,
            'bio' => $request->bio,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        if ($request->filled('access_code')) {
            $updateData['access_code'] = $request->access_code;
        }

        if ($request->filled('created_by_teacher_id')) {
            $updateData['created_by_teacher_id'] = $request->created_by_teacher_id;
        }

        $user->update($updateData);

        return redirect()->route('candidates.show', $user->id)->with('success', 'Student details updated.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('candidates.index')->with('success', 'Student profile deleted.');
    }
}
