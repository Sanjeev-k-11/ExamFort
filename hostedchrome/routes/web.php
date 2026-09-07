<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveMonitoringController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\PlacementExamController;

/*
|--------------------------------------------------------------------------
| Public Facing Website Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/about', [HomeController::class, 'about'])->name('public.about');
Route::get('/help', [HomeController::class, 'help'])->name('public.help');
Route::get('/contact', [HomeController::class, 'contact'])->name('public.contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('public.contact.submit');
Route::get('/download', [HomeController::class, 'download'])->name('public.download');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Anti-Brute Force Protected)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Institutional Examination & Management Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['admin.proctor'])->group(function () {
    // Master Dashboard (Super Admin, Principal, or Teacher based on role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Live Monitoring & Proctoring Radar
    Route::get('/monitoring', [LiveMonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/api/monitoring/violations', [LiveMonitoringController::class, 'apiViolations'])->name('api.monitoring.violations');
    Route::get('/api/monitoring/drafts', [LiveMonitoringController::class, 'apiDrafts'])->name('api.monitoring.drafts');
    Route::post('/api/monitoring/violations', [LiveMonitoringController::class, 'logViolation'])->name('api.monitoring.logViolation');
    Route::delete('/api/monitoring/violations/{id}', [LiveMonitoringController::class, 'dismissViolation'])->name('api.monitoring.dismissViolation');

    // Super Admin: Multi-Tenant Organizations & Deans / Principals
    Route::resource('organizations', OrganizationController::class);
    Route::resource('principals', PrincipalController::class);
    Route::post('/principal/update-gemini-key', [PrincipalController::class, 'updateGeminiKey'])->name('principals.updateGeminiKey');

    // Consultation Leads & Inquiries
    Route::get('/inquiries', [\App\Http\Controllers\InquiryController::class, 'index'])->name('inquiries.index');
    Route::post('/inquiries/{id}/status', [\App\Http\Controllers\InquiryController::class, 'updateStatus'])->name('inquiries.updateStatus');
    Route::delete('/inquiries/{id}', [\App\Http\Controllers\InquiryController::class, 'destroy'])->name('inquiries.destroy');

    // Faculty & Teacher Management
    Route::resource('teachers', TeacherController::class);
    Route::post('/teachers/{id}/permissions', [TeacherController::class, 'updatePermissions'])->name('teachers.updatePermissions');

    // Examination Lifecycle & Question Bank
    Route::resource('exams', ExamController::class);
    Route::post('/exams/{exam_code}/toggle-status', [ExamController::class, 'toggleStatus'])->name('exams.toggleStatus');
    Route::post('/exams/{exam_code}/toggle-publish', [ExamController::class, 'togglePublishResults'])->name('exams.togglePublish');

    Route::resource('questions', QuestionController::class);
    Route::get('/ai-question-generator', [\App\Http\Controllers\AiQuestionGeneratorController::class, 'showGenerator'])->name('ai.generator');
    Route::post('/ai-question-generator/generate', [\App\Http\Controllers\AiQuestionGeneratorController::class, 'generate'])->name('ai.generate');
    Route::post('/ai-question-generator/save-batch', [\App\Http\Controllers\AiQuestionGeneratorController::class, 'saveBatch'])->name('ai.saveBatch');

    // Student Submissions & Paper Evaluation
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{id}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{id}/update-scores', [SubmissionController::class, 'updateScores'])->name('submissions.updateScores');
    Route::delete('/submissions/{id}', [SubmissionController::class, 'destroy'])->name('submissions.destroy');

    // Course Curriculum & Practice Modules
    Route::resource('courses', CourseController::class);
    Route::post('/courses/{course_id}/assign-teacher', [CourseController::class, 'assignTeacher'])->name('courses.assignTeacher');
    Route::delete('/courses/{course_id}/unassign-teacher/{teacher_id}', [CourseController::class, 'unassignTeacher'])->name('courses.unassignTeacher');
    Route::post('/courses/{course_id}/lessons', [CourseController::class, 'storeLesson'])->name('courses.lessons.store');
    Route::post('/courses/{course_id}/ai-generate-lesson', [CourseController::class, 'generateAiLesson'])->name('courses.lessons.aiGenerate');
    Route::post('/courses/{course_id}/ai-save-lesson', [CourseController::class, 'saveAiLesson'])->name('courses.lessons.aiSave');
    Route::get('/courses/{course_id}/lessons/{lesson_num}', [CourseController::class, 'editLessonContent'])->name('courses.lessons.show');
    Route::get('/courses/{course_id}/lessons/{lesson_num}/content', [CourseController::class, 'editLessonContent'])->name('courses.lessons.content');
    Route::post('/courses/{course_id}/lessons/{lesson_num}/content', [CourseController::class, 'updateLessonContent'])->name('courses.lessons.content.update');
    Route::post('/courses/{course_id}/lessons/{lesson_num}/mcqs', [CourseController::class, 'storeTopicMcq'])->name('courses.lessons.mcqs.store');
    Route::post('/courses/{course_id}/lessons/{lesson_num}/coding', [CourseController::class, 'updateTopicCoding'])->name('courses.lessons.coding.update');
    Route::post('/courses/{course_id}/lessons/{lesson_num}/toggle-complete', [CourseController::class, 'toggleLessonComplete'])->name('courses.lessons.toggleComplete');
    Route::get('/courses/{course_id}/lessons/{lesson_num}/download-pdf', [CourseController::class, 'downloadLessonPdf'])->name('courses.lessons.downloadPdf');
    Route::post('/courses/{course_id}/lessons/{lesson_num}/run-sandbox', [CourseController::class, 'runCodingSandbox'])->name('courses.lessons.runSandbox');

    // Candidate / Student Roster
    Route::resource('candidates', CandidateController::class);
    Route::post('/candidates/{student_id}/assign-subject-teacher', [CandidateController::class, 'assignSubjectTeacher'])->name('candidates.assignSubjectTeacher');
    Route::delete('/candidates/{student_id}/unassign-subject-teacher/{assignment_id}', [CandidateController::class, 'unassignSubjectTeacher'])->name('candidates.unassignSubjectTeacher');

    // Proctoring Incidents & Threat Audit Log
    Route::get('/violations', [ViolationController::class, 'index'])->name('violations.index');
    Route::delete('/violations/{id}', [ViolationController::class, 'destroy'])->name('violations.destroy');

    // Campus Placement Drives & Examinations (Secret-Code Gated)
    Route::resource('placement-exams', PlacementExamController::class);
    Route::post('/placement-exams/{id}/teachers', [PlacementExamController::class, 'assignTeacher'])->name('placement-exams.assignTeacher');
    Route::post('/placement-exams/{id}/teachers/{assignmentId}/permissions', [PlacementExamController::class, 'updateTeacherPermission'])->name('placement-exams.updateTeacherPermission');
    Route::delete('/placement-exams/{id}/teachers/{assignmentId}', [PlacementExamController::class, 'removeTeacher'])->name('placement-exams.removeTeacher');
    Route::post('/placement-exams/{id}/enroll-students', [PlacementExamController::class, 'enrollStudents'])->name('placement-exams.enrollStudents');
    Route::post('/placement-exams/{id}/quick-add-candidate', [PlacementExamController::class, 'quickAddCandidate'])->name('placement-exams.quickAddCandidate');
    Route::delete('/placement-exams/{id}/candidates-clear-all', [PlacementExamController::class, 'clearAllCandidates'])->name('placement-exams.clearAllCandidates');
    Route::delete('/placement-exams/{id}/candidates/{candidateId}', [PlacementExamController::class, 'removeCandidate'])->name('placement-exams.removeCandidate');
    Route::post('/placement-exams/{id}/candidates/{candidateId}/regenerate-code', [PlacementExamController::class, 'regenerateCandidateCode'])->name('placement-exams.regenerateCandidateCode');
    Route::post('/placement-exams/{id}/candidates/{candidateId}/reschedule', [PlacementExamController::class, 'rescheduleCandidate'])->name('placement-exams.rescheduleCandidate');
    Route::post('/placement-exams/{id}/reschedule-drive', [PlacementExamController::class, 'rescheduleDrive'])->name('placement-exams.rescheduleDrive');
    Route::get('/placement-exams/{id}/export-csv', [PlacementExamController::class, 'exportCodesCsv'])->name('placement-exams.exportCsv');
    Route::get('/placement-exams/{id}/export-results-csv', [PlacementExamController::class, 'exportResultsCsv'])->name('placement-exams.exportResultsCsv');
    Route::post('/placement-exams/{id}/candidates/{candidateId}/shortlist', [PlacementExamController::class, 'updateCandidateShortlist'])->name('placement-exams.updateCandidateShortlist');
    Route::post('/placement-exams/{id}/questions', [PlacementExamController::class, 'storeQuestion'])->name('placement-exams.storeQuestion');
    Route::post('/placement-exams/{id}/ai-generate', [PlacementExamController::class, 'generateAiQuestions'])->name('placement-exams.aiGenerate');
});
