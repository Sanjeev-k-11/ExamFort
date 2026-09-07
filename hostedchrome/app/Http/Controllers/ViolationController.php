<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Violation;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = \App\Models\User::find($userId);

        if ($currentUser && $currentUser->isTeacher() && !$currentUser->canViewResults()) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: You do not have permission to inspect security violation audit logs. Please contact your Principal.');
        }

        $examCode = $request->query('exam_code');
        $type = $request->query('type');
        $search = $request->query('search');

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

        $query = Violation::with(['candidate', 'exam']);

        // Scope violations by organization / college
        if ($currentUser && in_array(strtoupper($userRole), ['PRINCIPAL', 'DEAN', 'DIRECTOR', 'PROCTOR', 'TEACHER', 'FACULTY'])) {
            $query->whereHas('exam', function($eq) use ($currentUser) {
                if ($currentUser->org_id) {
                    $eq->where('org_id', $currentUser->org_id)->orWhere('college_name', $currentUser->college_name);
                } else {
                    $eq->where('college_name', $currentUser->college_name)->orWhere('created_by_teacher_id', $currentUser->id);
                }
            });
        }

        if ($examCode) {
            $query->where('exam_code', $examCode);
        }

        if ($type) {
            $query->where('violation_type', 'like', "%{$type}%");
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('details', 'like', "%{$search}%")
                  ->orWhere('candidate_id', 'like', "%{$search}%")
                  ->orWhereHas('candidate', function ($c) use ($search) {
                      $c->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $violations = $query->orderBy('timestamp', 'desc')->paginate(20);

        return view('violations.index', compact('violations', 'exams', 'examCode', 'type', 'search', 'currentUser'));
    }

    public function destroy($id)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Access Restricted: Only College Principals and Super Admins can purge proctoring security logs.');
        }

        $violation = Violation::findOrFail($id);
        $violation->delete();

        return back()->with('success', 'Violation record deleted.');
    }
}
