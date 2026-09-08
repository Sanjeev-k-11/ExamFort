@extends('layouts.admin')

@section('title', 'Campus Placement Drives & Examinations - ExamFort')
@section('breadcrumb', 'Placement Drives')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Row -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.35);">
                    💼
                </div>
                <div>
                    <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Campus Placement Drives</h1>
                    <p style="font-size: 13.5px; color: #64748b;">
                        Create, delegate, and manage secret-code-gated company recruitment examinations for eligible students.
                    </p>
                </div>
            </div>
        </div>

        @if($isPrincipalOrAdmin || $isTeacher)
            <a href="{{ route('placement-exams.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                <span>Create Placement Drive</span>
            </a>
        @endif
    </div>

    <!-- 4 Stats Cards -->
    <div class="metrics-grid" style="margin-bottom: 24px;">
        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #4f46e5;">
            <div class="metric-icon-box" style="color: #4f46e5; background: #eef2ff;">
                <i data-lucide="briefcase" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value">{{ $totalDrives }}</div>
                <div class="metric-label">Total Placement Drives</div>
            </div>
        </div>

        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #059669;">
            <div class="metric-icon-box" style="color: #059669; background: #ecfdf5;">
                <i data-lucide="calendar-check" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value" style="color: #059669;">{{ $activeDrives }}</div>
                <div class="metric-label">Active / Scheduled Drives</div>
            </div>
        </div>

        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #0284c7, #6366f1); --accent-color: #0284c7;">
            <div class="metric-icon-box" style="color: #0284c7; background: #eff6ff;">
                <i data-lucide="key" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value" style="color: #0284c7;">{{ $totalEnrolled }}</div>
                <div class="metric-label">Enrolled (Codes Issued)</div>
            </div>
        </div>

        <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #d97706;">
            <div class="metric-icon-box" style="color: #d97706; background: #fffbeb;">
                <i data-lucide="award" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="metric-info">
                <div class="metric-value" style="color: #d97706;">{{ $completedDrives }}</div>
                <div class="metric-label">Completed Drives</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 16px 20px; margin-bottom: 24px;">
        <form action="{{ route('placement-exams.index') }}" method="GET" style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; max-width: 400px;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search company, job role, or exam code..." class="form-control" style="padding-left: 38px;">
                <i data-lucide="search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8;"></i>
            </div>

            <select name="status" class="form-control" style="width: 170px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="SCHEDULED" {{ $status === 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
                <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>Active Now</option>
                <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                <option value="DRAFT" {{ $status === 'DRAFT' ? 'selected' : '' }}>Draft</option>
            </select>

            <button type="submit" class="btn btn-secondary">
                Filter
            </button>
            @if($search || $status)
                <a href="{{ route('placement-exams.index') }}" class="btn btn-secondary" style="color: #dc2626; border-color: #fecaca;">
                    Clear
                </a>
            @endif
        </form>

        <div style="font-size: 12.5px; color: #64748b; display: flex; align-items: center; gap: 8px; font-weight: 600;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #059669;"></span>
            <span>Secret 6-Digit Code Gating Active</span>
        </div>
    </div>

    <!-- Drives Grid Cards -->
    @if($placementExams->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(370px, 1fr)); gap: 20px;">
            @foreach($placementExams as $drive)
                <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                    <div>
                        <!-- Top Row: Company & Status Badge -->
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: #eef2ff; border: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; color: #4f46e5; flex-shrink: 0;">
                                    🏢
                                </div>
                                <div>
                                    <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; line-height: 1.2; margin-bottom: 2px;">
                                        {{ $drive->company_name }}
                                    </h3>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #eff6ff; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 6px; display: inline-block;">
                                        {{ $drive->job_role }}
                                    </span>
                                </div>
                            </div>

                            <span class="status-pill {{ strtolower($drive->status) === 'active' ? 'active' : (strtolower($drive->status) === 'completed' ? 'completed' : 'upcoming') }}">
                                {{ $drive->status }}
                            </span>
                        </div>

                        <!-- Drive Title -->
                        <h4 style="font-size: 14.5px; font-weight: 700; color: #1e293b; margin-bottom: 12px; line-height: 1.4;">
                            {{ $drive->title }}
                        </h4>

                        <!-- Meta Chips -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px;">
                            <div>
                                <span style="color: #64748b; display: block; font-size: 11px; font-weight: 600;">Package (CTC)</span>
                                <strong style="color: #059669; font-size: 13.5px; font-weight: 800;">{{ $drive->package_lpa }}</strong>
                            </div>
                            <div>
                                <span style="color: #64748b; display: block; font-size: 11px; font-weight: 600;">Min Eligibility</span>
                                <strong style="color: #d97706; font-size: 13.5px; font-weight: 800;">CGPA &ge; {{ $drive->min_cgpa }}</strong>
                            </div>
                            <div style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 4px;">
                                <span style="color: #64748b; display: block; font-size: 11px; font-weight: 600;">Exam Date</span>
                                <strong style="color: #0f172a;">{{ $drive->exam_date }}</strong>
                            </div>
                            <div style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 4px;">
                                <span style="color: #64748b; display: block; font-size: 11px; font-weight: 600;">Duration / Marks</span>
                                <strong style="color: #0f172a;">{{ $drive->duration_minutes }}m / {{ $drive->total_marks }}M</strong>
                            </div>
                        </div>

                        <!-- Stats Bar (Candidates & Questions & Teachers) -->
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-top: 1px solid #e2e8f0; font-size: 12px; color: #475569; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="users" style="width: 14px; height: 14px; color: #4f46e5;"></i>
                                <span><strong style="color: #0f172a;">{{ $drive->candidates->count() }}</strong> Enrolled</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="help-circle" style="width: 14px; height: 14px; color: #0284c7;"></i>
                                <span><strong style="color: #0f172a;">{{ $drive->questions->count() }}</strong> Questions</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="user-check" style="width: 14px; height: 14px; color: #059669;"></i>
                                <span><strong style="color: #0f172a;">{{ $drive->assignedTeachers->count() }}</strong> Faculty</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="{{ route('placement-exams.show', $drive->id) }}" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12.5px; padding: 10px;">
                            <i data-lucide="settings" style="width: 15px; height: 15px;"></i>
                            <span>Manage Drive &amp; Codes</span>
                        </a>

                        <a href="{{ route('placement-exams.exportCsv', $drive->id) }}" class="btn btn-secondary" title="Download Candidate Access Codes CSV" style="padding: 10px 12px; color: #059669; border-color: #a7f3d0;">
                            <i data-lucide="download" style="width: 15px; height: 15px;"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 24px;">
            {{ $placementExams->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="glass-card" style="padding: 50px 24px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 14px;">💼</div>
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">No Placement Drives Found</h3>
            <p style="font-size: 13.5px; color: #64748b; max-width: 480px; margin: 0 auto 20px auto;">
                Create your first corporate placement examination, assign faculty permissions, enroll eligible candidates with auto-generated unique 6-digit access codes, and generate questions with Gemini AI.
            </p>
            @if($isPrincipalOrAdmin || $isTeacher)
                <a href="{{ route('placement-exams.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                    <span>Create First Placement Drive</span>
                </a>
            @endif
        </div>
    @endif

</div>
@endsection
