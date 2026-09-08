@extends('layouts.admin')

@section('title', 'Create Campus Placement Exam - ExamFort')
@section('breadcrumb', 'Create Placement Drive')

@section('content')
<div class="space-y-6" style="max-width: 900px; margin: 0 auto;">

    <!-- Back Nav & Header -->
    <div style="margin-bottom: 24px;">
        <a href="{{ route('placement-exams.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #4f46e5; text-decoration: none; margin-bottom: 8px; font-weight: 600;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            <span>Back to Placement Drives</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Schedule New Placement Drive</h1>
        <p style="font-size: 14px; color: #64748b; margin-top: 4px;">
            Set company hiring specifications, CTC package, eligibility criteria, and examination window.
        </p>
    </div>

    <!-- Main Creation Form -->
    <div class="glass-card">
        <form action="{{ route('placement-exams.store') }}" method="POST">
            @csrf

            <!-- Section 1: Company & Job Profile -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 15px; font-weight: 800; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>🏢</span> Company &amp; Recruitment Profile
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div>
                        <label class="form-label">Company / Recruiter Name <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Google, TCS, Infosys, Amazon" value="{{ old('company_name') }}" required>
                    </div>

                    <div>
                        <label class="form-label">Job Role / Designation <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="job_role" class="form-control" placeholder="e.g. Software Development Engineer (SDE-1)" value="{{ old('job_role') }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div>
                        <label class="form-label">Package (CTC in LPA) <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="package_lpa" class="form-control" placeholder="e.g. 12.5 LPA / 8.0 LPA" value="{{ old('package_lpa', '7.5 LPA') }}" required>
                    </div>

                    <div>
                        <label class="form-label">Min CGPA Required <span style="color: #dc2626;">*</span></label>
                        <input type="number" step="0.01" min="0" max="10" name="min_cgpa" class="form-control" placeholder="6.50" value="{{ old('min_cgpa', '6.50') }}" required>
                    </div>

                    <div>
                        <label class="form-label">Drive Type</label>
                        <select name="drive_type" class="form-control">
                            <option value="ON_CAMPUS">On-Campus Drive</option>
                            <option value="POOL_CAMPUS">Pool-Campus Drive</option>
                            <option value="OFF_CAMPUS">Off-Campus Direct</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Placement Exam Title <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. TCS NQT 2026 - National Qualifier Aptitude & Coding Round" value="{{ old('title') }}" required>
                </div>
            </div>

            <!-- Section 2: Examination Schedule & Timing -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 15px; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>⏱️</span> Examination Schedule &amp; Window
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div>
                        <label class="form-label">Exam Date <span style="color: #dc2626;">*</span></label>
                        <input type="date" name="exam_date" class="form-control" value="{{ old('exam_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    </div>

                    <div>
                        <label class="form-label">Start Time <span style="color: #dc2626;">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '10:00') }}" required>
                    </div>

                    <div>
                        <label class="form-label">End Time (Window) <span style="color: #dc2626;">*</span></label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '13:00') }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div>
                        <label class="form-label">Duration (Minutes) <span style="color: #dc2626;">*</span></label>
                        <input type="number" name="duration_minutes" class="form-control" placeholder="90" value="{{ old('duration_minutes', '90') }}" required>
                    </div>

                    <div>
                        <label class="form-label">Total Marks <span style="color: #dc2626;">*</span></label>
                        <input type="number" name="total_marks" class="form-control" placeholder="100" value="{{ old('total_marks', '100') }}" required>
                    </div>
                </div>
            </div>

            <!-- Section 3: Eligibility & Instructions -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 15px; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>📋</span> Eligibility Criteria &amp; Instructions
                </h3>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Eligibility Criteria Details</label>
                    <textarea name="eligibility_criteria" rows="2" class="form-control" placeholder="e.g. B.Tech (CSE/IT/ECE), minimum 60% in 10th & 12th, no active backlogs.">{{ old('eligibility_criteria') }}</textarea>
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Candidate Instructions / Guidelines</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="e.g. Strictly lockdown environment. Assessment includes Aptitude MCQs, DSA Coding, and System Design questions.">{{ old('description') }}</textarea>
                </div>

                <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-sm); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <strong style="font-size: 13.5px; color: #0f172a; display: block; margin-bottom: 2px;">Require AI Biometric Face Verification</strong>
                        <span style="font-size: 12px; color: #64748b;">Candidates must complete live facial verification before entering the examination.</span>
                    </div>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="face_verification_required" value="1" checked style="width: 18px; height: 18px; accent-color: #4f46e5;">
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Enabled</span>
                    </label>
                </div>
            </div>

            <!-- Section 4: Student Eligibility & Candidate Scheduling -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div>
                        <h3 style="font-size: 15px; font-weight: 800; color: #6366f1; text-transform: uppercase; letter-spacing: 0.8px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span>🎓</span> Candidate Eligibility &amp; Initial Scheduling
                        </h3>
                        <p style="font-size: 12.5px; color: #64748b; margin-top: 4px; margin-bottom: 0;">
                            Decide who is eligible to take this placement test. Each enrolled student receives a unique 6-digit access code.
                        </p>
                    </div>
                    <span style="font-size: 12px; background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 20px; font-weight: 700; border: 1px solid #c7d2fe;">
                        {{ $students->count() }} Registered Candidates
                    </span>
                </div>

                <!-- 3 Options -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-sm); cursor: pointer;">
                        <input type="radio" name="enroll_mode" value="none" checked onchange="toggleCreateEnrollMode(this.value)" style="margin-top: 3px; accent-color: #4f46e5;">
                        <div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">Do Not Add Students Yet</strong>
                            <span style="font-size: 11.5px; color: #64748b;">Start with 0 candidates. Filter or add eligible students later from the drive dashboard.</span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-sm); cursor: pointer;">
                        <input type="radio" name="enroll_mode" value="branch_filter" onchange="toggleCreateEnrollMode(this.value)" style="margin-top: 3px; accent-color: #4f46e5;">
                        <div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">Filter by Branch &amp; CGPA</strong>
                            <span style="font-size: 11.5px; color: #64748b;">Auto-enroll only candidates meeting branch and minimum CGPA criteria.</span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-sm); cursor: pointer;">
                        <input type="radio" name="enroll_mode" value="manual" onchange="toggleCreateEnrollMode(this.value)" style="margin-top: 3px; accent-color: #4f46e5;">
                        <div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">Manually Pick Students</strong>
                            <span style="font-size: 11.5px; color: #64748b;">Select specific eligible students from the institute roster using checkboxes.</span>
                        </div>
                    </label>
                </div>

                <!-- Sub-panel: Branch & CGPA filter -->
                <div id="panel-branch-filter" style="display: none; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: var(--radius-sm); padding: 16px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label class="form-label">Eligible Branch / Stream</label>
                            <select name="filter_stream" class="form-control">
                                <option value="">All Branches</option>
                                @foreach($streams as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Minimum CGPA Cutoff</label>
                            <input type="number" step="0.01" name="min_cgpa" value="7.00" class="form-control">
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #4338ca; margin-top: 10px; font-weight: 600;">
                        💡 Only students belonging to the chosen branch with CGPA &ge; cutoff will be scheduled with unique access codes.
                    </div>
                </div>

                <!-- Sub-panel: Manual Selection -->
                <div id="panel-manual-select" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); padding: 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                        <input type="text" id="create-student-search" placeholder="🔍 Search students by Name, Roll No, Branch..." onkeyup="filterCreateStudents()" class="form-control" style="max-width: 350px;">
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-secondary" onclick="selectAllCreateStudents(true)" style="padding: 6px 12px; font-size: 12px;">Select All</button>
                            <button type="button" class="btn btn-secondary" onclick="selectAllCreateStudents(false)" style="padding: 6px 12px; font-size: 12px;">Deselect All</button>
                        </div>
                    </div>

                    <div id="create-student-list" style="max-height: 220px; overflow-y: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        @foreach($students as $stu)
                            <label class="student-select-card" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); cursor: pointer;" data-text="{{ strtolower($stu->full_name . ' ' . $stu->student_id . ' ' . ($stu->stream ?? '')) }}">
                                <input type="checkbox" name="student_ids[]" value="{{ $stu->id }}" class="create-stu-cb" style="width: 16px; height: 16px; accent-color: #4f46e5;">
                                <div style="font-size: 12.5px; overflow: hidden;">
                                    <strong style="color: #0f172a; display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">{{ $stu->full_name }}</strong>
                                    <span style="color: #64748b; font-size: 11px;">{{ $stu->student_id ?? $stu->id }} &bull; {{ $stu->stream ?? 'CSE' }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 5: Faculty / Teacher Delegation -->
            @if($teachers->count() > 0)
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 15px; font-weight: 800; color: #d97706; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <span>👥</span> Initial Faculty Delegation (Optional)
                    </h3>
                    <p style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;">
                        Select faculty members who should receive permissions to manage candidates and author questions for this placement drive. You can also assign or edit permissions later.
                    </p>

                    <div style="max-height: 200px; overflow-y: auto; background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-sm); padding: 12px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            @foreach($teachers as $t)
                                <label style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); cursor: pointer;">
                                    <input type="checkbox" name="assigned_teacher_ids[]" value="{{ $t->id }}" style="width: 16px; height: 16px; accent-color: #4f46e5;">
                                    <div style="font-size: 12.5px;">
                                        <strong style="color: #0f172a; display: block;">{{ $t->full_name }}</strong>
                                        <span style="color: #64748b; font-size: 11px;">{{ $t->department ?? 'Faculty' }} &bull; {{ $t->email }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Button -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <a href="{{ route('placement-exams.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 800;">
                    <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i>
                    <span>Create Placement Drive &amp; Open Command Hub</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function toggleCreateEnrollMode(mode) {
    const branchPanel = document.getElementById('panel-branch-filter');
    const manualPanel = document.getElementById('panel-manual-select');
    if (branchPanel) branchPanel.style.display = (mode === 'branch_filter') ? 'block' : 'none';
    if (manualPanel) manualPanel.style.display = (mode === 'manual') ? 'block' : 'none';
}

function filterCreateStudents() {
    const q = (document.getElementById('create-student-search').value || '').toLowerCase();
    const cards = document.querySelectorAll('#create-student-list .student-select-card');
    cards.forEach(card => {
        const txt = card.getAttribute('data-text') || '';
        card.style.display = txt.includes(q) ? 'flex' : 'none';
    });
}

function selectAllCreateStudents(checked) {
    document.querySelectorAll('.create-stu-cb').forEach(cb => {
        if (cb.closest('.student-select-card').style.display !== 'none') {
            cb.checked = checked;
        }
    });
}
</script>
@endsection
