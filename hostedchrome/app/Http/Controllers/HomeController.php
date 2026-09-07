<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Models\Exam;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalExams = Exam::count();
        $totalOrganizations = Organization::count();
        $totalStudents = User::where('role', 'CANDIDATE')->count();
        $totalTeachers = User::whereIn('role', ['PROCTOR', 'TEACHER'])->count();
        $liveExams = Exam::where('status', 'ACTIVE')->limit(3)->get();
        $featuredCourses = \App\Models\Course::withCount('lessons')->limit(4)->get();

        return view('public.index', compact(
            'totalExams',
            'totalOrganizations',
            'totalStudents',
            'totalTeachers',
            'liveExams',
            'featuredCourses'
        ));
    }

    public function about()
    {
        return view('public.about');
    }

    public function help()
    {
        return view('public.help');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'full_name' => 'required|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|max:50',
            'organization_name' => 'nullable|max:255',
            'subject' => 'required|max:255',
            'message' => 'required|min:10',
        ]);

        $inquiry = ContactInquiry::create([
            'full_name' => trim($request->full_name),
            'email' => trim($request->email),
            'phone' => trim($request->phone),
            'organization_name' => trim($request->organization_name),
            'subject' => trim($request->subject),
            'message' => trim($request->message),
            'status' => 'PENDING',
        ]);

        // Dispatch Email Notifications (Admin + Applicant Receipt)
        try {
            $adminEmail = config('mail.from.address', 'admin@examfort.com');
            $applicantEmail = $inquiry->email;

            // 1. Email to Platform Admin
            \Illuminate\Support\Facades\Mail::raw(
                "New Institutional Consultation Request Received!\n\n" .
                "Applicant Name: {$inquiry->full_name}\n" .
                "Email: {$inquiry->email}\n" .
                "Phone: " . ($inquiry->phone ?: 'N/A') . "\n" .
                "Institution / College: " . ($inquiry->organization_name ?: 'N/A') . "\n" .
                "Subject: {$inquiry->subject}\n\n" .
                "Assessment Requirements / Message:\n{$inquiry->message}\n\n" .
                "---\nReview and follow up in Super Admin Portal: " . url('/dashboard'),
                function ($message) use ($adminEmail, $inquiry) {
                    $message->to($adminEmail)
                            ->subject("[ExamFort Lead] New Inquiry from {$inquiry->full_name}: {$inquiry->subject}");
                }
            );

            // 2. Receipt Confirmation Email to Applicant
            \Illuminate\Support\Facades\Mail::raw(
                "Dear {$inquiry->full_name},\n\n" .
                "Thank you for contacting ExamFort Institutional Support.\n" .
                "We have received your consultation request regarding \"{$inquiry->subject}\".\n\n" .
                "Our academic assessment specialist will review your requirements for " . ($inquiry->organization_name ?: 'your institution') . " and get back to you within 2-4 business hours.\n\n" .
                "Warm regards,\n" .
                "ExamFort Institutional Technology Team\n" .
                "Website: " . url('/'),
                function ($message) use ($applicantEmail, $inquiry) {
                    $message->to($applicantEmail)
                            ->subject("Inquiry Received: {$inquiry->subject} - ExamFort Institutional Platform");
                }
            );

            \Illuminate\Support\Facades\Log::info("Consultation inquiry emails successfully queued/sent for inquiry ID: {$inquiry->id}");

        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error("Mail dispatch error on contact inquiry: " . $ex->getMessage());
        }

        return back()->with('success', 'Thank you! Your institutional inquiry has been received and forwarded to our leadership team. A confirmation email has been dispatched.');
    }

    public function download()
    {
        return view('public.download');
    }
}
