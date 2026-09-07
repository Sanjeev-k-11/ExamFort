<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $userRole = session('auth_user_role');
        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted.');
        }

        $query = ContactInquiry::orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('organization_name', 'like', $search)
                  ->orWhere('subject', 'like', $search);
            });
        }

        $inquiries = $query->paginate(15);
        $pendingCount = ContactInquiry::where('status', 'PENDING')->count();
        $totalCount = ContactInquiry::count();

        return view('inquiries.index', compact('inquiries', 'pendingCount', 'totalCount'));
    }

    public function updateStatus(Request $request, $id)
    {
        $userRole = session('auth_user_role');
        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR'])) {
            return back()->with('error', 'Access Restricted.');
        }

        $request->validate([
            'status' => 'required|in:PENDING,CONTACTED,RESOLVED,REJECTED'
        ]);

        $inquiry = ContactInquiry::findOrFail($id);
        $inquiry->update(['status' => $request->status]);

        return back()->with('success', "Inquiry status updated to {$request->status}.");
    }

    public function destroy($id)
    {
        $userRole = session('auth_user_role');
        if (!in_array(strtoupper($userRole), ['ADMIN', 'SUPERADMIN'])) {
            return back()->with('error', 'Only Super Admin can delete consultation inquiries.');
        }

        ContactInquiry::findOrFail($id)->delete();

        return back()->with('success', 'Inquiry record removed.');
    }
}
