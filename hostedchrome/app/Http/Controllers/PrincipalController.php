<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrincipalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $orgId = $request->query('org_id');

        $query = User::where('role', 'PRINCIPAL')->with(['organization', 'createdTeachers']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('college_name', 'like', "%{$search}%");
            });
        }

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        $principals = $query->paginate(15);
        $organizations = Organization::all();

        return view('principals.index', compact('principals', 'organizations', 'search', 'orgId'));
    }

    public function create(Request $request)
    {
        $orgId = $request->query('org_id');
        $organizations = Organization::where('status', 'ACTIVE')->get();
        return view('principals.create', compact('organizations', 'orgId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'student_id' => 'required|string|max:50|unique:users,student_id',
            'org_id' => 'required|exists:organizations,id',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|min:4',
            'max_students_allowed' => 'required|integer|min:1',
            'max_exams_allowed' => 'required|integer|min:1',
        ]);

        $org = Organization::findOrFail($request->org_id);
        $principalId = 'PRIN_' . strtoupper(Str::random(6));

        User::create([
            'id' => $principalId,
            'org_id' => $org->id,
            'student_id' => trim($request->student_id),
            'full_name' => $request->full_name,
            'email' => trim($request->email),
            'phone' => $request->phone,
            'college_name' => $org->name,
            'course' => 'Institutional Leadership',
            'stream' => $request->department ?? 'Academic Administration',
            'designation' => $request->designation ?? 'Principal / Dean',
            'department' => $request->department ?? 'Dean Academic Affairs',
            'bio' => $request->bio,
            'password' => $request->password,
            'role' => 'PRINCIPAL',
            'access_code' => '999999',
            'status' => 'ACTIVE',
            'max_students_allowed' => $request->max_students_allowed,
            'max_exams_allowed' => $request->max_exams_allowed,
            'gemini_api_key' => $request->gemini_api_key,
        ]);

        return redirect()->route('principals.index')->with('success', "Principal / Dean '{$request->full_name}' created successfully.");
    }

    public function show($id)
    {
        $principal = User::where('role', 'PRINCIPAL')->with(['organization', 'createdTeachers.students'])->findOrFail($id);
        return view('principals.show', compact('principal'));
    }

    public function edit($id)
    {
        $principal = User::where('role', 'PRINCIPAL')->findOrFail($id);
        $organizations = Organization::where('status', 'ACTIVE')->get();
        return view('principals.edit', compact('principal', 'organizations'));
    }

    public function update(Request $request, $id)
    {
        $principal = User::where('role', 'PRINCIPAL')->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $id . ',id',
            'student_id' => 'required|string|max:50|unique:users,student_id,' . $id . ',id',
            'org_id' => 'required|exists:organizations,id',
            'max_students_allowed' => 'required|integer|min:1',
            'max_exams_allowed' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        $org = Organization::findOrFail($request->org_id);

        $updateData = [
            'full_name' => $request->full_name,
            'email' => trim($request->email),
            'student_id' => trim($request->student_id),
            'org_id' => $org->id,
            'college_name' => $org->name,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'department' => $request->department,
            'bio' => $request->bio,
            'status' => $request->status,
            'max_students_allowed' => $request->max_students_allowed,
            'max_exams_allowed' => $request->max_exams_allowed,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        if ($request->has('gemini_api_key')) {
            $updateData['gemini_api_key'] = trim($request->gemini_api_key);
        }

        $principal->update($updateData);

        return redirect()->route('principals.show', $id)->with('success', 'Principal credentials & quota updated.');
    }

    public function updateGeminiKey(Request $request)
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $currentUser = User::findOrFail($userId);

        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Only Principal and Administrator can update the institutional Google Gemini API Key.');
        }

        $request->validate([
            'gemini_api_key' => 'nullable|string|max:255',
        ]);

        $key = trim($request->gemini_api_key);
        $currentUser->update(['gemini_api_key' => $key]);

        // Also update organization if exists
        if ($currentUser->org_id) {
            Organization::where('id', $currentUser->org_id)->update(['gemini_api_key' => $key]);
        }

        return back()->with('success', '✅ Google Gemini AI API Key saved securely to the backend database.');
    }

    public function destroy($id)
    {
        $principal = User::where('role', 'PRINCIPAL')->findOrFail($id);
        $principal->delete();

        return redirect()->route('principals.index')->with('success', 'Principal account deleted.');
    }
}
