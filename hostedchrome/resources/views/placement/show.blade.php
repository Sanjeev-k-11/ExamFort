@extends('layouts.admin')

@section('title', $placementExam->company_name . ' Placement Drive - ExamFort')

@section('styles')
<style>
    .tab-btn {
        background: transparent;
        border: none;
        padding: 12px 20px;
        color: #64748b;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .tab-btn:hover {
        color: #0f172a;
    }
    .tab-btn.active {
        color: #4f46e5;
        border-bottom-color: #4f46e5;
        background: #eef2ff;
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
    }
    .code-badge-box {
        font-family: 'JetBrains Mono', monospace;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 13.5px;
        font-weight: 800;
        letter-spacing: 2px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Actions Row -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <a href="{{ route('placement-exams.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #4f46e5; text-decoration: none; margin-bottom: 8px; font-weight: 600;">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span>Back to Placement Drives</span>
            </a>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: #eef2ff; border: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    🏢
                </div>
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 2px; letter-spacing: -0.02em;">
                        {{ $placementExam->company_name }} &bull; {{ $placementExam->job_role }}
                    </h1>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #64748b;">
                        <span>Exam Code: <strong class="mono" style="color: #4f46e5;">{{ $placementExam->exam_code }}</strong></span>
                        <span>&bull;</span>
                        <span>Package: <strong style="color: #059669;">{{ $placementExam->package_lpa }}</strong></span>
                        <span>&bull;</span>
                        <span>Drive Date: <strong style="color: #0f172a;">{{ $placementExam->exam_date }} ({{ $placementExam->start_time }} - {{ $placementExam->end_time }})</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Download Excel/CSV Button -->
            <a href="{{ route('placement-exams.exportCsv', $placementExam->id) }}" class="btn btn-success" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="file-spreadsheet" style="width: 18px; height: 18px;"></i>
                <span>Download Access Codes (Excel/CSV)</span>
            </a>

            @if($canCreateQuestions)
                <button type="button" class="btn btn-primary" onclick="openAiModal()" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 800; background: linear-gradient(135deg, #4f46e5, #06b6d4);">
                    <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
                    <span>AI Question Generator</span>
                </button>
            @endif

            @if($isPrincipalOrAdmin)
                <a href="{{ route('placement-exams.edit', $placementExam->id) }}" class="btn btn-secondary" title="Edit Drive Details">
                    <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="metrics-grid" style="margin-bottom: 24px;">
        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #0284c7, #6366f1); --accent-color: #0284c7;">
            <div class="metric-icon-box" style="color: #0284c7; background: #eff6ff;">
                <i data-lucide="users" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value">{{ $placementExam->candidates->count() }}</div>
                <div class="metric-label">Candidates Enrolled</div>
            </div>
        </div>

        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #4f46e5;">
            <div class="metric-icon-box" style="color: #4f46e5; background: #eef2ff;">
                <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value">{{ $placementExam->questions->count() }}</div>
                <div class="metric-label">Questions in Bank</div>
            </div>
        </div>

        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #059669;">
            <div class="metric-icon-box" style="color: #059669; background: #ecfdf5;">
                <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value">{{ $placementExam->assignedTeachers->count() }}</div>
                <div class="metric-label">Delegated Faculty</div>
            </div>
        </div>

        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #d97706;">
            <div class="metric-icon-box" style="color: #d97706; background: #fffbeb;">
                <i data-lucide="graduation-cap" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value">&ge; {{ $placementExam->min_cgpa }}</div>
                <div class="metric-label">Min. CGPA Cutoff</div>
            </div>
        </div>
    </div>

    <!-- Main Tabs Navigation Bar -->
    <div style="background: rgba(255, 255, 255, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 0 16px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); overflow-x: auto;">
        <a href="{{ route('placement-exams.show', [$placementExam->id, 'tab' => 'candidates']) }}" class="tab-btn {{ $activeTab === 'candidates' ? 'active' : '' }}">
            <i data-lucide="key" style="width: 16px; height: 16px;"></i>
            <span>Candidates &amp; Access Codes ({{ $placementExam->candidates->count() }})</span>
        </a>

        <a href="{{ route('placement-exams.show', [$placementExam->id, 'tab' => 'teachers']) }}" class="tab-btn {{ $activeTab === 'teachers' ? 'active' : '' }}">
            <i data-lucide="users-2" style="width: 16px; height: 16px;"></i>
            <span>Faculty Delegation ({{ $placementExam->assignedTeachers->count() }})</span>
        </a>

        <a href="{{ route('placement-exams.show', [$placementExam->id, 'tab' => 'questions']) }}" class="tab-btn {{ $activeTab === 'questions' ? 'active' : '' }}">
            <i data-lucide="list-checks" style="width: 16px; height: 16px;"></i>
            <span>Question Bank ({{ $placementExam->questions->count() }})</span>
        </a>

        <a href="{{ route('placement-exams.show', [$placementExam->id, 'tab' => 'results']) }}" class="tab-btn {{ $activeTab === 'results' ? 'active' : '' }}">
            <i data-lucide="award" style="width: 16px; height: 16px; color: #d97706;"></i>
            <span>Results &amp; Merit List</span>
        </a>

        <a href="{{ route('placement-exams.show', [$placementExam->id, 'tab' => 'reschedule']) }}" class="tab-btn {{ $activeTab === 'reschedule' ? 'active' : '' }}">
            <i data-lucide="calendar-clock" style="width: 16px; height: 16px;"></i>
            <span>Rescheduling Hub</span>
        </a>
    </div>

    <!-- Tab Contents Container -->
    <div class="glass-card" style="border-top: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg); padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">

        <!-- ========================================== -->
        <!-- TAB 1: CANDIDATES ROSTER & SECRET CODES    -->
        <!-- ========================================== -->
        @if($activeTab === 'candidates')
            <div>
                <!-- Actions Row -->
                <!-- Top Action & Quick-Add Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 2px;">
                            Enrolled Candidates &amp; Secret 6-Digit Access Codes
                        </h3>
                        <p style="font-size: 12.5px; color: var(--text-secondary); margin: 0;">
                            This exam remains hidden on student profiles. Each candidate must enter their full name and unique 6-digit access code to unlock this exam.
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                        @if($canAddStudents)
                            <button type="button" class="btn btn-primary" onclick="openEnrollModal()" style="font-weight: 700;">
                                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                                <span>Enroll / Filter Students</span>
                            </button>

                            @if($placementExam->candidates->count() > 0)
                                <form action="{{ route('placement-exams.clearAllCandidates', $placementExam->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to CLEAR ALL enrolled candidates from this drive roster? All secret codes will be deleted.');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 9px 12px; font-size: 12.5px;" title="Clear All Candidates">
                                        <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                        <span>Clear All</span>
                                    </button>
                                </form>
                            @endif
                        @endif

                        <a href="{{ route('placement-exams.exportCsv', $placementExam->id) }}" class="btn btn-secondary" style="color: #34d399; border-color: rgba(16, 185, 129, 0.3); font-weight: 700;">
                            <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                            <span>Export Codes CSV</span>
                        </a>
                    </div>
                </div>

                <!-- Quick-Add Single Candidate Bar -->
                @if($canAddStudents)
                    <div style="background: rgba(99, 102, 241, 0.05); border: 1px dashed rgba(99, 102, 241, 0.3); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="font-size: 13px; color: #c7d2fe; display: flex; align-items: center; gap: 8px;">
                            <span>⚡</span>
                            <strong>Quick Add Candidate:</strong>
                            <span style="font-size: 12px; color: var(--text-muted);">Enroll any specific student by Roll / Student ID or Name directly</span>
                        </div>
                        <form action="{{ route('placement-exams.quickAddCandidate', $placementExam->id) }}" method="POST" style="display: flex; align-items: center; gap: 8px; flex: 1; max-width: 460px;">
                            @csrf
                            <input type="text" name="student_query" placeholder="Enter Student ID (e.g. STU101) or Name..." class="form-control" style="padding: 7px 12px; font-size: 12.5px;" required>
                            <button type="submit" class="btn btn-primary" style="padding: 7px 14px; font-size: 12.5px; white-space: nowrap;">
                                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                                <span>Add Student</span>
                            </button>
                        </form>
                    </div>
                @endif

                @if($placementExam->candidates->count() > 0)
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Roll / Student ID</th>
                                    <th>Candidate Name</th>
                                    <th>Branch / Stream</th>
                                    <th>SECRET 6-DIGIT CODE</th>
                                    <th>Scheduled Window</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($placementExam->candidates as $cand)
                                    <tr>
                                        <td>
                                            <strong class="mono" style="color: #818cf8;">{{ $cand->student_id }}</strong>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #fff;">{{ $cand->full_name }}</div>
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ $cand->email }}</div>
                                        </td>
                                        <td>
                                            <span style="font-size: 12px; color: var(--text-secondary);">{{ $cand->stream }}</span>
                                            <div style="font-size: 10.5px; color: #fbbf24;">CGPA: {{ $cand->cgpa }}</div>
                                        </td>
                                        <td>
                                            <div class="code-badge-box">
                                                <span>{{ $cand->access_code }}</span>
                                                <button type="button" onclick="navigator.clipboard.writeText('{{ $cand->access_code }}'); alert('Access code {{ $cand->access_code }} copied!');" style="background: none; border: none; cursor: pointer; color: #34d399; padding: 0; display: flex;" title="Copy Code">
                                                    <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px; color: #fff;">{{ $cand->scheduled_date ?? $placementExam->exam_date }}</div>
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ $cand->scheduled_start_time ?? $placementExam->start_time }} - {{ $cand->scheduled_end_time ?? $placementExam->end_time }}</div>
                                            @if($cand->is_rescheduled)
                                                <span style="display: inline-block; font-size: 10px; font-weight: 800; color: #38bdf8; background: rgba(56, 189, 248, 0.15); padding: 1px 6px; border-radius: 4px; margin-top: 2px;">
                                                    Individually Rescheduled
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-pill {{ $cand->attempt_status === 'COMPLETED' ? 'active' : ($cand->attempt_status === 'STARTED' ? 'upcoming' : 'completed') }}">
                                                {{ $cand->attempt_status }}
                                            </span>
                                            @if($cand->score !== null)
                                                <div style="font-size: 11px; font-weight: 700; color: #34d399; margin-top: 2px;">Score: {{ $cand->score }}</div>
                                            @endif
                                        </td>
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                @if($canReschedule)
                                                    <!-- Reschedule Single Student Modal Trigger -->
                                                    <button type="button" class="btn btn-secondary" onclick="openRescheduleStudentModal('{{ $cand->id }}', '{{ addslashes($cand->full_name) }}', '{{ $cand->scheduled_date ?? $placementExam->exam_date }}', '{{ $cand->scheduled_start_time ?? $placementExam->start_time }}', '{{ $cand->scheduled_end_time ?? $placementExam->end_time }}')" title="Reschedule This Student" style="padding: 6px 10px; font-size: 11.5px; color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);">
                                                        <i data-lucide="clock" style="width: 13px; height: 13px;"></i>
                                                        <span>Reschedule</span>
                                                    </button>
                                                @endif

                                                <!-- Regenerate Code -->
                                                <form action="{{ route('placement-exams.regenerateCandidateCode', [$placementExam->id, $cand->id]) }}" method="POST" onsubmit="return confirm('Generate a new 6-digit access code for {{ $cand->full_name }}?');" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary" title="Regenerate Code" style="padding: 6px 8px; color: #fbbf24;">
                                                        <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                                                    </button>
                                                </form>

                                                @if($canAddStudents)
                                                    <!-- Delete Candidate -->
                                                    <form action="{{ route('placement-exams.removeCandidate', [$placementExam->id, $cand->id]) }}" method="POST" onsubmit="return confirm('Remove {{ $cand->full_name }} from this placement drive?');" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-secondary" title="Remove Student" style="padding: 6px 8px; color: #f87171;">
                                                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 20px; background: rgba(0, 0, 0, 0.2); border: 1.5px dashed var(--border-color); border-radius: var(--radius-md);">
                        <div style="font-size: 40px; margin-bottom: 12px;">🎟️</div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px;">No Candidates Enrolled Yet</h4>
                        <p style="font-size: 12.5px; color: var(--text-secondary); max-width: 440px; margin: 0 auto 16px auto;">
                            Enroll eligible students to automatically generate unique 6-digit secret access codes for each candidate.
                        </p>
                        @if($canAddStudents)
                            <button type="button" class="btn btn-primary" onclick="openEnrollModal()">
                                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                                <span>Enroll Students Now</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 2: FACULTY & TEACHER DELEGATION        -->
        <!-- ========================================== -->
        @if($activeTab === 'teachers')
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 2px;">
                            Faculty Delegation &amp; Granular Permissions
                        </h3>
                        <p style="font-size: 12.5px; color: var(--text-secondary);">
                            The Principal can assign multiple teachers and grant separate permissions to add eligible students and create/author questions.
                        </p>
                    </div>

                    @if($isPrincipalOrAdmin)
                        <button type="button" class="btn btn-primary" onclick="openAssignTeacherModal()" style="font-weight: 700;">
                            <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                            <span>Assign Faculty Member</span>
                        </button>
                    @endif
                </div>

                @if($placementExam->assignedTeachers->count() > 0)
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Faculty Member</th>
                                    <th>Department</th>
                                    <th style="text-align: center;">Enroll Candidates</th>
                                    <th style="text-align: center;">Question Bank</th>
                                    <th style="text-align: center;">Reschedule Exams</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($placementExam->assignedTeachers as $assignment)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: #fff;">{{ $assignment->teacher->full_name ?? 'Faculty Member' }}</div>
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ $assignment->teacher->email ?? '' }}</div>
                                        </td>
                                        <td>
                                            <span style="font-size: 12px; color: var(--text-secondary);">{{ $assignment->teacher->department ?? 'Engineering Faculty' }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('placement-exams.updateTeacherPermission', [$placementExam->id, $assignment->id]) }}" method="POST" id="form-perm-students-{{ $assignment->id }}">
                                                @csrf
                                                <input type="hidden" name="can_create_questions" value="{{ $assignment->can_create_questions ? '1' : '0' }}">
                                                <input type="hidden" name="can_reschedule" value="{{ $assignment->can_reschedule ? '1' : '0' }}">
                                                <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                    <input type="checkbox" name="can_add_students" value="1" {{ $assignment->can_add_students ? 'checked' : '' }} onchange="this.form.submit()" {{ !$isPrincipalOrAdmin ? 'disabled' : '' }} style="accent-color: #10b981; width: 16px; height: 16px;">
                                                    <span style="font-size: 12px; color: {{ $assignment->can_add_students ? '#34d399' : 'var(--text-muted)' }}; font-weight: 600;">
                                                        {{ $assignment->can_add_students ? 'Allowed ✓' : 'Disabled ✗' }}
                                                    </span>
                                                </label>
                                            </form>
                                        </td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('placement-exams.updateTeacherPermission', [$placementExam->id, $assignment->id]) }}" method="POST" id="form-perm-questions-{{ $assignment->id }}">
                                                @csrf
                                                <input type="hidden" name="can_add_students" value="{{ $assignment->can_add_students ? '1' : '0' }}">
                                                <input type="hidden" name="can_reschedule" value="{{ $assignment->can_reschedule ? '1' : '0' }}">
                                                <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                    <input type="checkbox" name="can_create_questions" value="1" {{ $assignment->can_create_questions ? 'checked' : '' }} onchange="this.form.submit()" {{ !$isPrincipalOrAdmin ? 'disabled' : '' }} style="accent-color: #6366f1; width: 16px; height: 16px;">
                                                    <span style="font-size: 12px; color: {{ $assignment->can_create_questions ? '#818cf8' : 'var(--text-muted)' }}; font-weight: 600;">
                                                        {{ $assignment->can_create_questions ? 'Allowed ✓' : 'Disabled ✗' }}
                                                    </span>
                                                </label>
                                            </form>
                                        </td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('placement-exams.updateTeacherPermission', [$placementExam->id, $assignment->id]) }}" method="POST" id="form-perm-resc-{{ $assignment->id }}">
                                                @csrf
                                                <input type="hidden" name="can_add_students" value="{{ $assignment->can_add_students ? '1' : '0' }}">
                                                <input type="hidden" name="can_create_questions" value="{{ $assignment->can_create_questions ? '1' : '0' }}">
                                                <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                    <input type="checkbox" name="can_reschedule" value="1" {{ $assignment->can_reschedule ? 'checked' : '' }} onchange="this.form.submit()" {{ !$isPrincipalOrAdmin ? 'disabled' : '' }} style="accent-color: #38bdf8; width: 16px; height: 16px;">
                                                    <span style="font-size: 12px; color: {{ $assignment->can_reschedule ? '#38bdf8' : 'var(--text-muted)' }}; font-weight: 600;">
                                                        {{ $assignment->can_reschedule ? 'Allowed ✓' : 'Disabled ✗' }}
                                                    </span>
                                                </label>
                                            </form>
                                        </td>
                                        <td style="text-align: right;">
                                            @if($isPrincipalOrAdmin)
                                                <form action="{{ route('placement-exams.removeTeacher', [$placementExam->id, $assignment->id]) }}" method="POST" onsubmit="return confirm('Remove {{ $assignment->teacher->full_name ?? 'this teacher' }} from this placement drive?');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-secondary" title="Remove Assignment" style="padding: 6px 10px; color: #f87171;">
                                                        <i data-lucide="user-x" style="width: 14px; height: 14px;"></i>
                                                        <span>Unassign</span>
                                                    </button>
                                                </form>
                                            @else
                                                <span style="font-size: 11px; color: var(--text-muted);">Assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 20px; background: rgba(0, 0, 0, 0.2); border: 1.5px dashed var(--border-color); border-radius: var(--radius-md);">
                        <div style="font-size: 40px; margin-bottom: 12px;">👥</div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px;">No Faculty Members Assigned Yet</h4>
                        <p style="font-size: 12.5px; color: var(--text-secondary); max-width: 440px; margin: 0 auto 16px auto;">
                            Assign subject teachers or placement coordinators to assist in student enrollment and question authoring.
                        </p>
                        @if($isPrincipalOrAdmin)
                            <button type="button" class="btn btn-primary" onclick="openAssignTeacherModal()">
                                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                                <span>Assign First Faculty Member</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 3: QUESTION BANK & GEMINI AI           -->
        <!-- ========================================== -->
        @if($activeTab === 'questions')
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 2px;">
                            Placement Examination Question Bank
                        </h3>
                        <p style="font-size: 12.5px; color: var(--text-secondary);">
                            Supports MCQs (Aptitude &amp; Technical), Coding challenges with test cases, and Paragraph/Essay questions with AI rubric.
                        </p>
                    </div>

                    @if($canCreateQuestions)
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" class="btn btn-primary" onclick="openAiModal()" style="font-weight: 800; background: linear-gradient(135deg, #6366f1, #06b6d4);">
                                <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                                <span>✨ Gemini AI Generator</span>
                            </button>

                            <button type="button" class="btn btn-secondary" onclick="openAddQuestionModal()" style="font-weight: 700;">
                                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                                <span>Add Manual Question</span>
                            </button>
                        </div>
                    @endif
                </div>

                @if($placementExam->questions->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        @foreach($placementExam->questions as $q)
                            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 800; background: rgba(99, 102, 241, 0.2); color: #818cf8; padding: 3px 8px; border-radius: 4px;">
                                            Q{{ $q->question_number }}
                                        </span>
                                        <span style="font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; background: {{ $q->type === 'CODING' ? 'rgba(56, 189, 248, 0.2)' : ($q->type === 'MCQ' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(245, 158, 11, 0.2)') }}; color: {{ $q->type === 'CODING' ? '#38bdf8' : ($q->type === 'MCQ' ? '#34d399' : '#fbbf24') }};">
                                            {{ $q->type }}
                                        </span>
                                        <strong style="font-size: 14px; color: #fff;">{{ $q->title }}</strong>
                                    </div>

                                    <span style="font-size: 12px; font-weight: 800; color: #34d399;">
                                        {{ $q->max_marks }} Marks
                                    </span>
                                </div>

                                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 12px;">
                                    {{ $q->question_text }}
                                </p>

                                @if($q->type === 'MCQ' && is_array($q->options))
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                                        @foreach($q->options as $opt)
                                            <div style="font-size: 12px; padding: 6px 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid {{ $opt === $q->correct_answer ? '#10b981' : 'var(--border-color)' }}; border-radius: 4px; color: {{ $opt === $q->correct_answer ? '#34d399' : 'var(--text-secondary)' }}; font-weight: {{ $opt === $q->correct_answer ? '700' : 'normal' }};">
                                                {{ $opt }} @if($opt === $q->correct_answer) ✓ (Correct) @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($q->type === 'CODING')
                                    <div style="background: #0a0f1d; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px 14px; font-size: 12px; font-family: 'JetBrains Mono', monospace; color: #38bdf8; margin-bottom: 10px;">
                                        <div><strong>Sample Input:</strong> {{ $q->sample_input ?? 'N/A' }}</div>
                                        <div><strong>Sample Output:</strong> {{ $q->sample_output ?? 'N/A' }}</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 20px; background: rgba(0, 0, 0, 0.2); border: 1.5px dashed var(--border-color); border-radius: var(--radius-md);">
                        <div style="font-size: 40px; margin-bottom: 12px;">📝</div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px;">Question Bank is Empty</h4>
                        <p style="font-size: 12.5px; color: var(--text-secondary); max-width: 440px; margin: 0 auto 16px auto;">
                            Generate questions instantly with Gemini AI (MCQs, Coding, System Design) or add questions manually.
                        </p>
                        @if($canCreateQuestions)
                            <button type="button" class="btn btn-primary" onclick="openAiModal()" style="background: linear-gradient(135deg, #6366f1, #06b6d4);">
                                <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                                <span>✨ Launch Gemini AI Generator</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 4: RESULTS & MERIT LIST (EXCEL EXPORT) -->
        <!-- ========================================== -->
        @if($activeTab === 'results')
            <div>
                <!-- Top Summary & Download Row -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 2px;">
                            Placement Results, Merit List &amp; Shortlisting
                        </h3>
                        <p style="font-size: 12.5px; color: var(--text-secondary);">
                            Live synchronized evaluation scores for MCQs, Coding tests, AI essays, and proctoring integrity trust scores.
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="{{ route('placement-exams.exportResultsCsv', $placementExam->id) }}" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                            <i data-lucide="file-spreadsheet" style="width: 18px; height: 18px;"></i>
                            <span>Download Full Results &amp; Student Details (Excel/CSV)</span>
                        </a>
                    </div>
                </div>

                @php
                    $attemptedCandidates = $placementExam->candidates->filter(fn($c) => $c->attempt_status === 'COMPLETED' || $c->score !== null)->sortByDesc('score');
                    $unattemptedCandidates = $placementExam->candidates->filter(fn($c) => $c->attempt_status !== 'COMPLETED' && $c->score === null);
                    $totalCandidatesCount = $placementExam->candidates->count();
                    $completedCount = $attemptedCandidates->count();
                    $avgScore = $completedCount > 0 ? round($attemptedCandidates->avg('score'), 1) : 0;
                    $highestScore = $completedCount > 0 ? $attemptedCandidates->max('score') : 0;
                    $shortlistedCount = $placementExam->candidates->whereIn('shortlist_status', ['SHORTLISTED', 'INTERVIEW_ROUND_1', 'INTERVIEW_ROUND_2', 'HIRED'])->count();
                @endphp

                <!-- 4 Performance Highlights -->
                <div class="metrics-grid" style="margin-bottom: 20px;">
                    <div class="metric-card" style="padding: 14px 18px;">
                        <div class="metric-value" style="color: #34d399; font-size: 22px;">{{ $completedCount }} / {{ $totalCandidatesCount }}</div>
                        <div class="metric-label">Completed Attempts</div>
                    </div>

                    <div class="metric-card" style="padding: 14px 18px;">
                        <div class="metric-value" style="color: #38bdf8; font-size: 22px;">{{ $avgScore }} / {{ $placementExam->total_marks }}</div>
                        <div class="metric-label">Batch Average Score</div>
                    </div>

                    <div class="metric-card" style="padding: 14px 18px;">
                        <div class="metric-value" style="color: #fbbf24; font-size: 22px;">{{ $highestScore }} / {{ $placementExam->total_marks }}</div>
                        <div class="metric-label">Top Score (Rank 1)</div>
                    </div>

                    <div class="metric-card" style="padding: 14px 18px;">
                        <div class="metric-value" style="color: #818cf8; font-size: 22px;">{{ $shortlistedCount }}</div>
                        <div class="metric-label">Shortlisted for Interview</div>
                    </div>
                </div>

                @if($placementExam->candidates->count() > 0)
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Rank</th>
                                    <th>Candidate Details</th>
                                    <th>Branch / CGPA</th>
                                    <th>Total Score ({{ $placementExam->total_marks }}M)</th>
                                    <th>Score Breakdown</th>
                                    <th>AI Trust Score</th>
                                    <th>Shortlist / Interview Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rank = 1; @endphp
                                @foreach($attemptedCandidates as $cand)
                                    @php
                                        $pct = $placementExam->total_marks > 0 && $cand->score !== null ? round(($cand->score / $placementExam->total_marks) * 100, 1) : 0;
                                    @endphp
                                    <tr style="{{ $cand->shortlist_status === 'SHORTLISTED' || $cand->shortlist_status === 'HIRED' ? 'background: rgba(16, 185, 129, 0.05);' : '' }}">
                                        <td>
                                            @if($rank === 1)
                                                <span style="font-size: 16px;">🥇</span>
                                            @elseif($rank === 2)
                                                <span style="font-size: 16px;">🥈</span>
                                            @elseif($rank === 3)
                                                <span style="font-size: 16px;">🥉</span>
                                            @else
                                                <span class="mono" style="font-weight: 800; color: #818cf8;">#{{ $rank }}</span>
                                            @endif
                                            @php $rank++; @endphp
                                        </td>
                                        <td>
                                            <div style="font-weight: 800; color: #fff; font-size: 13.5px;">{{ $cand->full_name }}</div>
                                            <div style="font-size: 11px; color: #818cf8; font-family: 'JetBrains Mono', monospace;">{{ $cand->student_id }}</div>
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ $cand->email }} &bull; {{ $cand->phone ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px; color: #fff;">{{ $cand->stream }}</div>
                                            <div style="font-size: 11px; color: #fbbf24; font-weight: 700;">CGPA: {{ $cand->cgpa }}</div>
                                            <div style="font-size: 10px; color: var(--text-muted);">{{ $cand->course }}</div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: baseline; gap: 6px;">
                                                <strong style="font-size: 16px; font-weight: 800; color: #34d399;">{{ $cand->score }}</strong>
                                                <span style="font-size: 11px; color: var(--text-muted);">/ {{ $placementExam->total_marks }}</span>
                                                <span style="font-size: 11px; font-weight: 700; color: #38bdf8;">({{ $pct }}%)</span>
                                            </div>
                                            <div style="width: 100px; height: 5px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; margin-top: 4px;">
                                                <div style="width: {{ min(100, $pct) }}%; height: 100%; background: linear-gradient(90deg, #6366f1, #10b981);"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 11px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 2px;">
                                                <span>MCQ: <strong style="color: #fff;">{{ $cand->mcq_score ?? 0 }}M</strong></span>
                                                <span>Coding: <strong style="color: #38bdf8;">{{ $cand->coding_score ?? 0 }}M</strong></span>
                                                @if($cand->essay_score !== null)
                                                    <span>Essay: <strong style="color: #fbbf24;">{{ $cand->essay_score }}M</strong></span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-size: 12px; font-weight: 800; color: {{ ($cand->trust_score ?? 100) >= 80 ? '#34d399' : (($cand->trust_score ?? 100) >= 50 ? '#fbbf24' : '#f87171') }};">
                                                    {{ $cand->trust_score ?? 100 }}%
                                                </span>
                                            </div>
                                            @if(($cand->violations_count ?? 0) > 0)
                                                <span style="font-size: 10px; color: #f87171; font-weight: 700;">
                                                    ⚠️ {{ $cand->violations_count }} Incidents
                                                </span>
                                            @else
                                                <span style="font-size: 10px; color: #34d399;">Clean Session</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('placement-exams.updateCandidateShortlist', [$placementExam->id, $cand->id]) }}" method="POST">
                                                @csrf
                                                <select name="shortlist_status" onchange="this.form.submit()" class="form-control" style="font-size: 11.5px; padding: 6px 10px; border-radius: 6px; font-weight: 700; color: {{ in_array($cand->shortlist_status, ['SHORTLISTED', 'HIRED']) ? '#34d399' : (in_array($cand->shortlist_status, ['INTERVIEW_ROUND_1', 'INTERVIEW_ROUND_2']) ? '#38bdf8' : ($cand->shortlist_status === 'REJECTED' ? '#f87171' : 'var(--text-secondary)')) }};">
                                                    <option value="PENDING" {{ $cand->shortlist_status === 'PENDING' ? 'selected' : '' }}>⏳ Under Review</option>
                                                    <option value="SHORTLISTED" {{ $cand->shortlist_status === 'SHORTLISTED' ? 'selected' : '' }}>✓ Shortlisted</option>
                                                    <option value="INTERVIEW_ROUND_1" {{ $cand->shortlist_status === 'INTERVIEW_ROUND_1' ? 'selected' : '' }}>📞 Round 1 Interview</option>
                                                    <option value="INTERVIEW_ROUND_2" {{ $cand->shortlist_status === 'INTERVIEW_ROUND_2' ? 'selected' : '' }}>💻 Technical Round 2</option>
                                                    <option value="HIRED" {{ $cand->shortlist_status === 'HIRED' ? 'selected' : '' }}>🎉 Selected / Offer</option>
                                                    <option value="REJECTED" {{ $cand->shortlist_status === 'REJECTED' ? 'selected' : '' }}>✗ Not Selected</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($unattemptedCandidates as $cand)
                                    <tr style="opacity: 0.65;">
                                        <td><span class="mono" style="color: var(--text-muted);">-</span></td>
                                        <td>
                                            <div style="font-weight: 700; color: #fff;">{{ $cand->full_name }}</div>
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ $cand->student_id }} &bull; {{ $cand->email }}</div>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px; color: var(--text-secondary);">{{ $cand->stream }} (CGPA: {{ $cand->cgpa }})</div>
                                        </td>
                                        <td>
                                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Not Attempted Yet</span>
                                        </td>
                                        <td><span style="font-size: 11px; color: var(--text-muted);">-</span></td>
                                        <td><span style="font-size: 11px; color: var(--text-muted);">Pending</span></td>
                                        <td>
                                            <span class="status-pill upcoming">PENDING ATTEMPT</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 40px; background: rgba(0,0,0,0.2); border: 1.5px dashed var(--border-color); border-radius: var(--radius-md);">
                        <p style="color: var(--text-muted);">No candidate records found.</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 5: RESCHEDULING HUB                    -->
        <!-- ========================================== -->
        @if($activeTab === 'reschedule')
            <div style="max-width: 760px; margin: 0 auto;">
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 17px; font-weight: 800; color: #fff; margin-bottom: 4px;">
                        Placement Drive Rescheduling Hub
                    </h3>
                    <p style="font-size: 13px; color: var(--text-secondary);">
                        Reschedule the whole placement examination window or reschedule a specific individual student who faced a technical or medical emergency.
                    </p>
                </div>

                @if($canReschedule)
                    <!-- Global Drive Reschedule Card -->
                    <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px;">
                        <h4 style="font-size: 15px; font-weight: 800; color: #34d399; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                            <span>Reschedule Entire Drive (All Candidates)</span>
                        </h4>

                        <form action="{{ route('placement-exams.rescheduleDrive', $placementExam->id) }}" method="POST">
                            @csrf
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                                <div>
                                    <label class="form-label">New Exam Date <span style="color: #f87171;">*</span></label>
                                    <input type="date" name="exam_date" value="{{ date('Y-m-d', strtotime($placementExam->exam_date ?? now())) }}" class="form-control" required style="color-scheme: dark;">
                                </div>
                                <div>
                                    <label class="form-label">New Start Time <span style="color: #f87171;">*</span></label>
                                    <input type="time" name="start_time" value="{{ date('H:i', strtotime($placementExam->start_time ?? '10:00')) }}" class="form-control" required style="color-scheme: dark;">
                                </div>
                                <div>
                                    <label class="form-label">New End Time <span style="color: #f87171;">*</span></label>
                                    <input type="time" name="end_time" value="{{ date('H:i', strtotime($placementExam->end_time ?? '13:00')) }}" class="form-control" required style="color-scheme: dark;">
                                </div>
                            </div>

                            <div style="margin-bottom: 18px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #fff;">
                                    <input type="checkbox" name="apply_to_all_candidates" value="1" checked style="width: 16px; height: 16px; accent-color: #34d399;">
                                    <span>Apply new schedule to all non-customized enrolled candidates</span>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary" style="font-weight: 800; background: linear-gradient(135deg, #10b981, #059669);">
                                <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                                <span>Update Whole Drive Schedule</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: var(--radius-md); padding: 24px; text-align: center; margin-bottom: 24px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🔒</div>
                        <h4 style="font-size: 16px; font-weight: 800; color: #f87171; margin-bottom: 6px;">Rescheduling Access Restricted</h4>
                        <p style="font-size: 13px; color: var(--text-secondary); max-width: 500px; margin: 0 auto;">
                            Only the Principal or faculty members who have been explicitly granted <strong>Reschedule Permission</strong> can modify drive examination windows or reschedule candidates.
                        </p>
                    </div>
                @endif

                <!-- Per-Candidate Reschedule Info -->
                <div style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: var(--radius-md); padding: 18px; display: flex; align-items: flex-start; gap: 14px;">
                    <div style="font-size: 24px;">💡</div>
                    <div>
                        <strong style="font-size: 13.5px; color: #fff; display: block; margin-bottom: 4px;">Looking for Individual Candidate Rescheduling?</strong>
                        <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 8px;">
                            You can reschedule any specific student, reset their exam attempt, or regenerate their secret code directly under the <strong>Candidates &amp; Access Codes</strong> tab by clicking the <strong>Reschedule</strong> button next to their name.
                        </p>
                        <a href="{{ route('placement-exams.show', [$placementExam->id, 'tab' => 'candidates']) }}" style="font-size: 12.5px; color: #818cf8; font-weight: 700; text-decoration: underline;">
                            Go to Candidate Roster &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: ENROLL CANDIDATES MODAL                                          -->
<!-- ========================================================================= -->
<div id="modal-enroll" class="modal-overlay">
    <div class="modal-box" style="max-width: 680px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="font-size: 20px;">🎓</div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: #fff; margin: 0;">Enroll Eligible Students</h3>
                    <p style="font-size: 12px; color: var(--text-secondary); margin: 0;">Unique 6-digit access codes will be auto-generated for each student</p>
                </div>
            </div>
            <button type="button" onclick="closeEnrollModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form action="{{ route('placement-exams.enrollStudents', $placementExam->id) }}" method="POST">
            @csrf

            <!-- Enrollment Method Selector -->
            <div style="margin-bottom: 18px;">
                <label class="form-label">Enrollment Method</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                        <input type="radio" name="enroll_mode" value="cgpa_branch" checked onchange="toggleEnrollMode(this.value)" style="accent-color: #6366f1;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;">By Branch &amp; CGPA Cutoff</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                        <input type="radio" name="enroll_mode" value="manual" onchange="toggleEnrollMode(this.value)" style="accent-color: #6366f1;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;">Select Specific Students</span>
                    </label>
                </div>
            </div>

            <!-- Mode A: Filter by Branch & CGPA -->
            <div id="section-enroll-filter">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label">Branch / Stream</label>
                        <select name="filter_stream" class="form-control">
                            <option value="">All Branches / All Students</option>
                            @foreach($streams as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Minimum CGPA Filter</label>
                        <input type="number" step="0.01" name="min_cgpa" value="{{ $placementExam->min_cgpa }}" class="form-control">
                    </div>
                </div>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 6px; padding: 10px 12px; font-size: 12px; color: #34d399; margin-bottom: 18px;">
                    ✓ All registered students matching criteria will be enrolled and assigned individual 6-digit access codes.
                </div>
            </div>

            <!-- Mode B: Manual Selection -->
            <div id="section-enroll-manual" style="display: none; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px;">
                    <input type="text" id="modal-student-search" placeholder="🔍 Search eligible students..." onkeyup="filterModalStudents()" class="form-control" style="font-size: 12.5px; padding: 6px 12px; max-width: 320px;">
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-secondary" onclick="selectAllModalStudents(true)" style="padding: 5px 10px; font-size: 11.5px;">Select All</button>
                        <button type="button" class="btn btn-secondary" onclick="selectAllModalStudents(false)" style="padding: 5px 10px; font-size: 11.5px;">Deselect All</button>
                    </div>
                </div>

                <div id="modal-student-list" style="max-height: 220px; overflow-y: auto; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 8px;">
                    @forelse($availableStudents as $stu)
                        @if(!in_array($stu->student_id, $enrolledStudentIds))
                            <label class="modal-stu-item" style="display: flex; align-items: center; gap: 10px; padding: 6px 8px; border-bottom: 1px solid rgba(255,255,255,0.04); cursor: pointer;" data-text="{{ strtolower($stu->full_name . ' ' . $stu->student_id . ' ' . ($stu->stream ?? '')) }}">
                                <input type="checkbox" name="student_ids[]" value="{{ $stu->id }}" class="modal-stu-cb" style="width: 16px; height: 16px; accent-color: #6366f1;">
                                <div style="font-size: 12.5px;">
                                    <strong style="color: #fff;">{{ $stu->full_name }}</strong>
                                    <span style="color: var(--text-muted); font-size: 11px;">({{ $stu->student_id }}) &bull; {{ $stu->stream ?? 'CSE' }}</span>
                                </div>
                            </label>
                        @endif
                    @empty
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 12.5px;">
                            All registered students are already enrolled!
                        </div>
                    @endforelse
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeEnrollModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 800;">
                    <span>Generate Codes &amp; Enroll</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: ASSIGN FACULTY MODAL                                             -->
<!-- ========================================================================= -->
<div id="modal-assign-teacher" class="modal-overlay">
    <div class="modal-box" style="max-width: 540px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 style="font-size: 17px; font-weight: 800; color: #fff; margin: 0;">Assign Faculty Member</h3>
            <button type="button" onclick="closeAssignTeacherModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form action="{{ route('placement-exams.assignTeacher', $placementExam->id) }}" method="POST">
            @csrf
            <div style="margin-bottom: 18px;">
                <label class="form-label">Select Faculty Member <span style="color: #f87171;">*</span></label>
                <select name="teacher_id" class="form-control" required>
                    <option value="">-- Choose Faculty Member --</option>
                    @foreach($availableTeachers as $at)
                        <option value="{{ $at->id }}">{{ $at->full_name }} ({{ $at->department ?? 'Faculty' }} - {{ $at->email }})</option>
                    @endforeach
                </select>
            </div>

            <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 20px;">
                <label class="form-label" style="margin-bottom: 10px;">Granted Permissions</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="can_add_students" value="1" checked style="width: 16px; height: 16px; accent-color: #34d399;">
                        <span style="font-size: 13px; color: #fff;">Permission to Add &amp; Manage Eligible Students</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="can_create_questions" value="1" checked style="width: 16px; height: 16px; accent-color: #818cf8;">
                        <span style="font-size: 13px; color: #fff;">Permission to Create &amp; AI-Generate Questions</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="can_reschedule" value="1" style="width: 16px; height: 16px; accent-color: #38bdf8;">
                        <span style="font-size: 13px; color: #38bdf8; font-weight: 700;">Permission to Reschedule Entire Drive &amp; Candidates</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAssignTeacherModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 800;">Assign Faculty</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: RESCHEDULE SINGLE CANDIDATE MODAL                                -->
<!-- ========================================================================= -->
<div id="modal-reschedule-student" class="modal-overlay">
    <div class="modal-box" style="max-width: 540px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: #38bdf8; margin: 0;">Reschedule Candidate Exam</h3>
                <p style="font-size: 12px; color: var(--text-secondary); margin: 0;" id="lbl-reschedule-student-name">Student Name</p>
            </div>
            <button type="button" onclick="closeRescheduleStudentModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form id="form-reschedule-student" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label class="form-label">New Examination Date <span style="color: #f87171;">*</span></label>
                    <input type="date" name="scheduled_date" id="input-resc-date" class="form-control" required style="color-scheme: dark;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="form-label">Start Time <span style="color: #f87171;">*</span></label>
                        <input type="time" name="scheduled_start_time" id="input-resc-start" class="form-control" required style="color-scheme: dark;">
                    </div>
                    <div>
                        <label class="form-label">End Time <span style="color: #f87171;">*</span></label>
                        <input type="time" name="scheduled_end_time" id="input-resc-end" class="form-control" required style="color-scheme: dark;">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label">Reason for Rescheduling</label>
                <input type="text" name="rescheduled_reason" class="form-control" placeholder="e.g. Medical emergency / Network disconnection">
            </div>

            <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 18px; display: flex; flex-direction: column; gap: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; color: #fff;">
                    <input type="checkbox" name="reset_attempt" value="1" checked style="width: 16px; height: 16px; accent-color: #38bdf8;">
                    <span>Reset previous attempt status to PENDING</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; color: #fff;">
                    <input type="checkbox" name="regenerate_code" value="1" style="width: 16px; height: 16px; accent-color: #fbbf24;">
                    <span>Issue a brand new unique 6-digit access code</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeRescheduleStudentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 800;">Save Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: GEMINI AI PLACEMENT QUESTION GENERATOR MODAL                     -->
<!-- ========================================================================= -->
<div id="modal-ai-generator" class="modal-overlay">
    <div class="modal-box" style="max-width: 640px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #06b6d4); display: flex; align-items: center; justify-content: center; color: white;">
                    <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: #fff; margin: 0;">Gemini AI Placement Paper Setter</h3>
                    <p style="font-size: 12px; color: var(--text-secondary); margin: 0;">Tailored for {{ $placementExam->company_name }} ({{ $placementExam->job_role }})</p>
                </div>
            </div>
            <button type="button" onclick="closeAiModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form id="form-ai-gen" onsubmit="handleAiGeneration(event)">
            <div style="margin-bottom: 16px;">
                <label class="form-label">Domain / Syllabus Focus <span style="color: #f87171;">*</span></label>
                <select name="domain_focus" id="ai-domain-focus" class="form-control" required>
                    <option value="Quantitative Aptitude, Logical Reasoning & Verbal Ability">Quantitative Aptitude, Logical Reasoning &amp; Verbal Ability</option>
                    <option value="Data Structures & Algorithms (Arrays, Graphs, Dynamic Programming, Trees)">Data Structures &amp; Algorithms (DSA &amp; Problem Solving)</option>
                    <option value="Core Computer Science (Operating Systems, DBMS, Computer Networks, OOPs)">Core Computer Science (OS, DBMS, Networks, OOPs)</option>
                    <option value="Full-Stack Web Development & System Architecture">Full-Stack Web Development &amp; System Architecture</option>
                    <option value="Company-Specific Technical Interview & Coding Round ({{ $placementExam->company_name }})">Company-Specific Round ({{ $placementExam->company_name }})</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label class="form-label">Difficulty Level</label>
                    <select name="difficulty" class="form-control">
                        <option value="Medium" selected>Medium (Standard Campus)</option>
                        <option value="Hard">Hard (Product Companies)</option>
                        <option value="Competitive">Competitive (Top Tier)</option>
                        <option value="Easy">Easy (Foundation)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">MCQs Count</label>
                    <input type="number" name="mcq_count" value="5" min="0" max="20" class="form-control">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Coding Challenges Count</label>
                    <input type="number" name="coding_count" value="2" min="0" max="5" class="form-control">
                </div>

                <div>
                    <label class="form-label">Descriptive / Essay Count</label>
                    <input type="number" name="paragraph_count" value="1" min="0" max="5" class="form-control">
                </div>
            </div>

            <div id="ai-loading-alert" style="display: none; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 8px; padding: 14px; font-size: 13px; color: #818cf8; margin-bottom: 16px; align-items: center; gap: 10px;">
                <span class="animate-spin" style="font-size: 18px;">✨</span>
                <span>Gemini AI is generating company-specific placement questions &amp; test cases...</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAiModal()">Cancel</button>
                <button type="submit" id="btn-ai-submit" class="btn btn-primary" style="font-weight: 800; background: linear-gradient(135deg, #6366f1, #06b6d4);">
                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                    <span>Generate &amp; Save to Bank</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: ADD MANUAL QUESTION MODAL                                        -->
<!-- ========================================================================= -->
<div id="modal-add-question" class="modal-overlay">
    <div class="modal-box" style="max-width: 650px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 style="font-size: 17px; font-weight: 800; color: #fff; margin: 0;">Add Manual Question</h3>
            <button type="button" onclick="closeAddQuestionModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 20px;">&times;</button>
        </div>

        <form action="{{ route('placement-exams.storeQuestion', $placementExam->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label class="form-label">Question Type <span style="color: #f87171;">*</span></label>
                    <select name="type" id="sel-q-type" class="form-control" onchange="toggleQTypeInputs(this.value)" required>
                        <option value="MCQ">Multiple Choice Question (MCQ)</option>
                        <option value="CODING">Coding Problem (With Test Cases)</option>
                        <option value="PARAGRAPH">Paragraph / Descriptive (AI Rubric)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Marks Weightage <span style="color: #f87171;">*</span></label>
                    <input type="number" step="0.5" name="max_marks" value="2" class="form-control" required>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label">Question Title <span style="color: #f87171;">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Find Longest Substring Without Repeating Characters" required>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label">Question Statement / Problem Description <span style="color: #f87171;">*</span></label>
                <textarea name="question_text" rows="3" class="form-control" placeholder="Enter complete question statement..." required></textarea>
            </div>

            <!-- MCQ Options Section -->
            <div id="section-mcq-inputs">
                <label class="form-label">Options</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <input type="text" name="options[]" class="form-control" placeholder="Option A">
                    <input type="text" name="options[]" class="form-control" placeholder="Option B">
                    <input type="text" name="options[]" class="form-control" placeholder="Option C">
                    <input type="text" name="options[]" class="form-control" placeholder="Option D">
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">Exact Correct Answer</label>
                    <input type="text" name="correct_answer" class="form-control" placeholder="Must match one of the options exactly">
                </div>
            </div>

            <!-- Coding Problem Inputs -->
            <div id="section-coding-inputs" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label">Sample Input</label>
                        <textarea name="sample_input" rows="2" class="form-control" placeholder="e.g. 5&#10;1 2 3 4 5"></textarea>
                    </div>
                    <div>
                        <label class="form-label">Sample Output</label>
                        <textarea name="sample_output" rows="2" class="form-control" placeholder="e.g. 15"></textarea>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">Constraints</label>
                    <input type="text" name="constraints" class="form-control" placeholder="e.g. 1 <= N <= 10^5">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddQuestionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 800;">Save Question</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEnrollModal() { document.getElementById('modal-enroll').style.display = 'flex'; }
    function closeEnrollModal() { document.getElementById('modal-enroll').style.display = 'none'; }

    function openAssignTeacherModal() { document.getElementById('modal-assign-teacher').style.display = 'flex'; }
    function closeAssignTeacherModal() { document.getElementById('modal-assign-teacher').style.display = 'none'; }

    function openAiModal() { document.getElementById('modal-ai-generator').style.display = 'flex'; }
    function closeAiModal() { document.getElementById('modal-ai-generator').style.display = 'none'; }

    function openAddQuestionModal() { document.getElementById('modal-add-question').style.display = 'flex'; }
    function closeAddQuestionModal() { document.getElementById('modal-add-question').style.display = 'none'; }

    function toggleEnrollMode(val) {
        document.getElementById('section-enroll-filter').style.display = val === 'cgpa_branch' ? 'block' : 'none';
        document.getElementById('section-enroll-manual').style.display = val === 'manual' ? 'block' : 'none';
    }

    function filterModalStudents() {
        const q = (document.getElementById('modal-student-search').value || '').toLowerCase();
        const items = document.querySelectorAll('#modal-student-list .modal-stu-item');
        items.forEach(item => {
            const txt = item.getAttribute('data-text') || '';
            item.style.display = txt.includes(q) ? 'flex' : 'none';
        });
    }

    function selectAllModalStudents(checked) {
        document.querySelectorAll('.modal-stu-cb').forEach(cb => {
            if (cb.closest('.modal-stu-item').style.display !== 'none') {
                cb.checked = checked;
            }
        });
    }

    function toggleQTypeInputs(val) {
        document.getElementById('section-mcq-inputs').style.display = val === 'MCQ' ? 'block' : 'none';
        document.getElementById('section-coding-inputs').style.display = val === 'CODING' ? 'block' : 'none';
    }

    function formatToDateValue(dateStr) {
        if (!dateStr) return new Date().toISOString().split('T')[0];
        const d = new Date(dateStr);
        if (!isNaN(d.getTime())) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        return dateStr;
    }

    function formatToTimeValue(timeStr) {
        if (!timeStr) return "10:00";
        if (timeStr.includes(':')) {
            const clean = timeStr.trim();
            const isPM = clean.toUpperCase().includes('PM');
            const isAM = clean.toUpperCase().includes('AM');
            let timeOnly = clean.replace(/AM|PM/gi, '').trim();
            let [h, m] = timeOnly.split(':');
            h = parseInt(h);
            if (isPM && h < 12) h += 12;
            if (isAM && h === 12) h = 0;
            return String(h).padStart(2, '0') + ':' + String(m || '00').padStart(2, '0');
        }
        return timeStr;
    }

    function openRescheduleStudentModal(candId, name, date, start, end) {
        const form = document.getElementById('form-reschedule-student');
        form.action = `/placement-exams/{{ $placementExam->id }}/candidates/${candId}/reschedule`;
        document.getElementById('lbl-reschedule-student-name').textContent = name;
        document.getElementById('input-resc-date').value = formatToDateValue(date);
        document.getElementById('input-resc-start').value = formatToTimeValue(start);
        document.getElementById('input-resc-end').value = formatToTimeValue(end);
        document.getElementById('modal-reschedule-student').style.display = 'flex';
    }
    function closeRescheduleStudentModal() { document.getElementById('modal-reschedule-student').style.display = 'none'; }

    async function handleAiGeneration(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('btn-ai-submit');
        const alertBox = document.getElementById('ai-loading-alert');

        submitBtn.disabled = true;
        alertBox.style.display = 'flex';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const res = await fetch(`/placement-exams/{{ $placementExam->id }}/ai-generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.href = `/placement-exams/{{ $placementExam->id }}?tab=questions`;
            } else {
                alert('AI Generation Error: ' + (data.message || 'Please check Gemini API Key.'));
            }
        } catch (err) {
            alert('Server error while connecting to Gemini AI generator.');
        } finally {
            submitBtn.disabled = false;
            alertBox.style.display = 'none';
        }
    }
</script>
@endsection
