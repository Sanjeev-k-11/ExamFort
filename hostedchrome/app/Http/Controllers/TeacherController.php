<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        // If a standard teacher tries to open teacher directory, redirect them unless they have can_create_teachers permission
        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']) && !($currentUser && $currentUser->canCreateTeachers())) {
            return redirect()->route('candidates.index')->with('error', 'Faculty accounts are managed exclusively by the Institution Principal, Super Administrator, or authorized HOD Faculty.');
        }

        $search = $request->query('search');

        $query = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])
            ->with(['students', 'createdExams', 'organization', 'principal']);

        // If Principal or Authorized HOD, filter to their college / organization
        if (in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR']) || ($currentUser && $currentUser->isTeacher())) {
            $query->where(function($q) use ($currentUser) {
                if ($currentUser->org_id) {
                    $q->where('org_id', $currentUser->org_id);
                }
                $q->orWhere('created_by_principal_id', $currentUser->id)
                  ->orWhere('college_name', $currentUser->college_name);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('college_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(15);

        return view('teachers.index', compact('teachers', 'currentUser'));
    }

    public function create()
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']) && !($currentUser && $currentUser->canCreateTeachers())) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin, College Principals, or Authorized HOD Faculty can create Faculty accounts.');
        }

        $organizations = Organization::where('status', 'ACTIVE')->get();
        $principals = User::where('role', 'PRINCIPAL')->get();

        return view('teachers.create', compact('currentUser', 'organizations', 'principals'));
    }

    public function store(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']) && !($currentUser && $currentUser->canCreateTeachers())) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin, College Principals, or Authorized HOD Faculty can create Faculty accounts.');
        }

        $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'student_id' => 'required|string|max:50|unique:users,student_id',
            'college_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'password' => 'required|min:4',
            'max_students_allowed' => 'required|integer|min:1',
            'max_exams_allowed' => 'required|integer|min:1',
        ]);

        $teacherId = 'TEACH_' . strtoupper(Str::random(6));

        $orgId = $request->org_id;
        $principalId = $request->created_by_principal_id;

        if ($currentUser && $currentUser->isPrincipal()) {
            $orgId = $currentUser->org_id;
            $principalId = $currentUser->id;
        } elseif ($currentUser && $currentUser->isTeacher()) {
            $orgId = $currentUser->org_id;
            $principalId = $currentUser->created_by_principal_id;
        }

        User::create([
            'id' => $teacherId,
            'org_id' => $orgId,
            'created_by_principal_id' => $principalId,
            'student_id' => trim($request->student_id),
            'full_name' => $request->full_name,
            'email' => trim($request->email),
            'phone' => $request->phone,
            'college_name' => $request->college_name,
            'course' => 'Faculty & Assessment Operations',
            'stream' => $request->department ?? 'Computer Science',
            'designation' => $request->designation ?? 'Assistant Professor',
            'department' => $request->department ?? 'Computer Science & Engineering',
            'bio' => $request->bio,
            'password' => $request->password,
            'role' => 'PROCTOR',
            'access_code' => $request->access_code ?? '123456',
            'status' => $request->status ?? 'ACTIVE',
            'max_students_allowed' => $request->max_students_allowed,
            'max_exams_allowed' => $request->max_exams_allowed,
            'can_create_exams' => $request->has('can_create_exams') ? (bool)$request->can_create_exams : true,
            'can_set_questions' => $request->has('can_set_questions') ? (bool)$request->can_set_questions : true,
            'can_manage_lessons' => $request->has('can_manage_lessons') ? (bool)$request->can_manage_lessons : true,
            'can_manage_courses' => $request->has('can_manage_courses') ? (bool)$request->can_manage_courses : false,
            'can_enroll_students' => $request->has('can_enroll_students') ? (bool)$request->can_enroll_students : true,
            'can_view_results' => $request->has('can_view_results') ? (bool)$request->can_view_results : true,
            'can_create_teachers' => $request->has('can_create_teachers') ? (bool)$request->can_create_teachers : false,
        ]);

        return redirect()->route('teachers.index')->with('success', "Faculty / Teacher '{$request->full_name}' created successfully with assigned permissions.");
    }

    public function show($id)
    {
        $teacher = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])
            ->with(['createdExams', 'organization', 'principal'])
            ->findOrFail($id);

        $students = User::where('role', 'CANDIDATE')
            ->where(function($q) use ($teacher) {
                $q->where('created_by_teacher_id', $teacher->id)
                  ->orWhere('college_name', $teacher->college_name)
                  ->orWhere('org_id', $teacher->org_id);
            })
            ->orderBy('student_id', 'asc')
            ->get();

        $allCourses = \App\Models\Course::all();
        $assignedCourses = \App\Models\CourseTeacherAssignment::where('teacher_id', $id)->get()->keyBy('course_id');

        $userId = session('auth_user_id');
        $currentUser = User::find($userId);

        return view('teachers.show', compact('teacher', 'currentUser', 'students', 'allCourses', 'assignedCourses'));
    }

    public function edit($id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::find($userId);

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin and College Principals can edit Faculty accounts.');
        }

        $teacher = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->findOrFail($id);
        $organizations = Organization::where('status', 'ACTIVE')->get();
        $principals = User::where('role', 'PRINCIPAL')->get();

        return view('teachers.edit', compact('teacher', 'organizations', 'principals'));
    }

    public function update(Request $request, $id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin and College Principals can update Faculty accounts.');
        }

        $teacher = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $id . ',id',
            'student_id' => 'required|string|max:50|unique:users,student_id,' . $id . ',id',
            'college_name' => 'required|string|max:255',
            'max_students_allowed' => 'required|integer|min:1',
            'max_exams_allowed' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        $updateData = [
            'full_name' => $request->full_name,
            'email' => trim($request->email),
            'student_id' => trim($request->student_id),
            'college_name' => $request->college_name,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'department' => $request->department,
            'bio' => $request->bio,
            'status' => $request->status,
            'max_students_allowed' => $request->max_students_allowed,
            'max_exams_allowed' => $request->max_exams_allowed,
            'can_create_exams' => $request->has('can_create_exams') ? (bool)$request->can_create_exams : false,
            'can_set_questions' => $request->has('can_set_questions') ? (bool)$request->can_set_questions : false,
            'can_manage_lessons' => $request->has('can_manage_lessons') ? (bool)$request->can_manage_lessons : false,
            'can_manage_courses' => $request->has('can_manage_courses') ? (bool)$request->can_manage_courses : false,
            'can_enroll_students' => $request->has('can_enroll_students') ? (bool)$request->can_enroll_students : false,
            'can_view_results' => $request->has('can_view_results') ? (bool)$request->can_view_results : false,
            'can_create_teachers' => $request->has('can_create_teachers') ? (bool)$request->can_create_teachers : false,
        ];

        if ($request->filled('org_id')) {
            $updateData['org_id'] = $request->org_id;
        }

        if ($request->filled('created_by_principal_id')) {
            $updateData['created_by_principal_id'] = $request->created_by_principal_id;
        }

        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        if ($request->filled('access_code')) {
            $updateData['access_code'] = $request->access_code;
        }

        $teacher->update($updateData);

        return redirect()->route('teachers.show', $id)->with('success', 'Faculty details & permissions updated successfully.');
    }

    // ==========================================
    // QUICK PERMISSIONS UPDATE BY PRINCIPAL
    // ==========================================

    public function updatePermissions(Request $request, $id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return redirect()->back()->with('error', 'Access Restricted: Only Principal and Super Admin can modify Faculty permissions.');
        }

        $teacher = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->findOrFail($id);

        $teacher->update([
            'can_create_exams' => (bool)$request->input('can_create_exams', 0),
            'can_set_questions' => (bool)$request->input('can_set_questions', 0),
            'can_manage_lessons' => (bool)$request->input('can_manage_lessons', 0),
            'can_manage_courses' => (bool)$request->input('can_manage_courses', 0),
            'can_enroll_students' => (bool)$request->input('can_enroll_students', 0),
            'can_view_results' => (bool)$request->input('can_view_results', 0),
            'can_create_teachers' => (bool)$request->input('can_create_teachers', 0),
        ]);

        // Save Course & Module Lesson Authoring Delegations
        $coursePerms = $request->input('courses', []);
        $allCourses = \App\Models\Course::all();
        foreach ($allCourses as $c) {
            $cid = $c->course_id;
            if (isset($coursePerms[$cid]) && !empty($coursePerms[$cid]['assigned'])) {
                \App\Models\CourseTeacherAssignment::updateOrCreate(
                    ['course_id' => $cid, 'teacher_id' => $id],
                    [
                        'assigned_by_principal_id' => $userId,
                        'role' => $coursePerms[$cid]['role'] ?? 'Module Lead Instructor',
                        'can_add_lessons' => !empty($coursePerms[$cid]['can_add_lessons']),
                        'can_edit_modules' => !empty($coursePerms[$cid]['can_edit_modules']),
                        'can_upload_pdf' => !empty($coursePerms[$cid]['can_upload_pdf']),
                        'can_manage_mcqs' => !empty($coursePerms[$cid]['can_manage_mcqs']),
                        'can_manage_coding' => !empty($coursePerms[$cid]['can_manage_coding']),
                    ]
                );
            } else {
                \App\Models\CourseTeacherAssignment::where('course_id', $cid)->where('teacher_id', $id)->delete();
            }
        }

        return redirect()->back()->with('success', "Granular permissions & course module authoring rights updated successfully for Prof. {$teacher->full_name}.");
    }

    public function destroy($id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin and College Principals can delete Faculty accounts.');
        }

        $teacher = User::whereIn('role', ['PROCTOR', 'TEACHER', 'FACULTY'])->findOrFail($id);
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Faculty account deleted.');
    }
}
