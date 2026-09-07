@extends('layouts.admin')

@section('title', 'Edit Placement Exam - ExamFort')

@section('content')
<div class="space-y-6" style="max-width: 900px; margin: 0 auto;">

    <!-- Back Nav & Header -->
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <a href="{{ route('placement-exams.show', $placementExam->id) }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #818cf8; text-decoration: none; margin-bottom: 8px; font-weight: 600;">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span>Back to {{ $placementExam->company_name }} Drive Hub</span>
            </a>
            <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Edit Placement Drive Details</h1>
            <p style="font-size: 13px; color: var(--text-secondary);">
                Update company profile, examination timing, and eligibility requirements.
            </p>
        </div>
    </div>

    <!-- Edit Form -->
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <form action="{{ route('placement-exams.update', $placementExam->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Section 1: Company Profile -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 15px; font-weight: 800; color: #818cf8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>🏢</span> Company &amp; Recruitment Profile
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div>
                        <label class="form-label">Company Name <span style="color: #f87171;">*</span></label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $placementExam->company_name) }}" required>
                    </div>

                    <div>
                        <label class="form-label">Job Role / Designation <span style="color: #f87171;">*</span></label>
                        <input type="text" name="job_role" class="form-control" value="{{ old('job_role', $placementExam->job_role) }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div>
                        <label class="form-label">Package (CTC in LPA) <span style="color: #f87171;">*</span></label>
                        <input type="text" name="package_lpa" class="form-control" value="{{ old('package_lpa', $placementExam->package_lpa) }}" required>
                    </div>

                    <div>
                        <label class="form-label">Min CGPA Required <span style="color: #f87171;">*</span></label>
                        <input type="number" step="0.01" min="0" max="10" name="min_cgpa" class="form-control" value="{{ old('min_cgpa', $placementExam->min_cgpa) }}" required>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="SCHEDULED" {{ $placementExam->status === 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
                            <option value="ACTIVE" {{ $placementExam->status === 'ACTIVE' ? 'selected' : '' }}>Active Now</option>
                            <option value="COMPLETED" {{ $placementExam->status === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                            <option value="DRAFT" {{ $placementExam->status === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="ARCHIVED" {{ $placementExam->status === 'ARCHIVED' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Exam Title <span style="color: #f87171;">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $placementExam->title) }}" required>
                </div>
            </div>

            <!-- Section 2: Schedule & Timing -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 15px; font-weight: 800; color: #34d399; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>⏱️</span> Examination Schedule &amp; Window
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div>
                        <label class="form-label">Exam Date <span style="color: #f87171;">*</span></label>
                        <input type="date" name="exam_date" class="form-control" value="{{ old('exam_date', date('Y-m-d', strtotime($placementExam->exam_date ?? now()))) }}" required style="color-scheme: dark;">
                    </div>

                    <div>
                        <label class="form-label">Start Time <span style="color: #f87171;">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', date('H:i', strtotime($placementExam->start_time ?? '10:00'))) }}" required style="color-scheme: dark;">
                    </div>

                    <div>
                        <label class="form-label">End Time <span style="color: #f87171;">*</span></label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', date('H:i', strtotime($placementExam->end_time ?? '13:00'))) }}" required style="color-scheme: dark;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div>
                        <label class="form-label">Duration (Minutes) <span style="color: #f87171;">*</span></label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $placementExam->duration_minutes) }}" required>
                    </div>

                    <div>
                        <label class="form-label">Total Marks <span style="color: #f87171;">*</span></label>
                        <input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', $placementExam->total_marks) }}" required>
                    </div>
                </div>
            </div>

            <!-- Section 3: Eligibility & Instructions -->
            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 15px; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>📋</span> Eligibility Criteria &amp; Instructions
                </h3>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Eligibility Criteria</label>
                    <textarea name="eligibility_criteria" rows="2" class="form-control">{{ old('eligibility_criteria', $placementExam->eligibility_criteria) }}</textarea>
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Instructions / Description</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $placementExam->description) }}</textarea>
                </div>

                <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <strong style="font-size: 13px; color: #fff; display: block; margin-bottom: 2px;">Require AI Biometric Face Verification</strong>
                        <span style="font-size: 11.5px; color: var(--text-secondary);">Candidates must authenticate facial biometrics before entering examination workspace.</span>
                    </div>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="face_verification_required" value="1" {{ $placementExam->face_verification_required ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #6366f1;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;">Enabled</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="{{ route('placement-exams.show', $placementExam->id) }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 800;">
                    <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
