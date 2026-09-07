<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Exam;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Organization::withCount(['principals', 'teachers', 'students', 'exams']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $organizations = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('organizations.index', compact('organizations', 'search'));
    }

    public function create()
    {
        return view('organizations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|unique:organizations,id|max:50',
            'name' => 'required|max:255',
            'code' => 'required|unique:organizations,code|max:50',
            'type' => 'required|string',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|max:50',
            'website' => 'nullable|url|max:255',
            'max_teachers_allowed' => 'required|integer|min:1',
            'max_students_allowed' => 'required|integer|min:1',
            'max_exams_allowed' => 'required|integer|min:1',
        ]);

        Organization::create([
            'id' => trim($request->id),
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'type' => $request->type,
            'email' => $request->email,
            'phone' => $request->phone,
            'website' => $request->website,
            'address' => $request->address,
            'max_teachers_allowed' => $request->max_teachers_allowed,
            'max_students_allowed' => $request->max_students_allowed,
            'max_exams_allowed' => $request->max_exams_allowed,
            'status' => $request->status ?? 'ACTIVE',
        ]);

        return redirect()->route('organizations.index')->with('success', "Organization '{$request->name}' created successfully.");
    }

    public function show($id)
    {
        $organization = Organization::with(['principals', 'teachers', 'students', 'exams'])
            ->findOrFail($id);

        return view('organizations.show', compact('organization'));
    }

    public function edit($id)
    {
        $organization = Organization::findOrFail($id);
        return view('organizations.edit', compact('organization'));
    }

    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
            'code' => 'required|max:50|unique:organizations,code,' . $id . ',id',
            'type' => 'required|string',
            'max_teachers_allowed' => 'required|integer|min:1',
            'max_students_allowed' => 'required|integer|min:1',
            'max_exams_allowed' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        $organization->update([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'type' => $request->type,
            'email' => $request->email,
            'phone' => $request->phone,
            'website' => $request->website,
            'address' => $request->address,
            'max_teachers_allowed' => $request->max_teachers_allowed,
            'max_students_allowed' => $request->max_students_allowed,
            'max_exams_allowed' => $request->max_exams_allowed,
            'status' => $request->status,
        ]);

        return redirect()->route('organizations.show', $id)->with('success', 'Organization details & institutional quotas updated.');
    }

    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        $organization->delete();

        return redirect()->route('organizations.index')->with('success', 'Organization removed.');
    }
}
