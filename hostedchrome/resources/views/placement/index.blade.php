@extends('layouts.admin')

@section('title', 'Campus Placement Drives & Examinations - ExamFort')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Row -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #06b6d4); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);">
                    💼
                </div>
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -0.5px;">Campus Placement Drives</h1>
                    <p style="font-size: 13px; color: var(--text-secondary);">
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
    <div class="metrics-grid">
        <div class="metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div class="metric-value">{{ $totalDrives }}</div>
                    <div class="metric-label">Total Placement Drives</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="briefcase" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div class="metric-value" style="color: #34d399;">{{ $activeDrives }}</div>
                    <div class="metric-label">Active / Scheduled Drives</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="calendar-check" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div class="metric-value" style="color: #38bdf8;">{{ $totalEnrolled }}</div>
                    <div class="metric-label">Enrolled Candidates (Codes Issued)</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="key" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div class="metric-value" style="color: #fbbf24;">{{ $completedDrives }}</div>
                    <div class="metric-label">Completed Drives</div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="award" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 18px;">
        <form action="{{ route('placement-exams.index') }}" method="GET" style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
            <div style="position: relative; flex: 1; max-width: 400px;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search company, job role, or exam code..." class="form-control" style="padding-left: 36px;">
                <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted);"></i>
            </div>

            <select name="status" class="form-control" style="width: 170px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="SCHEDULED" {{ $status === 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
                <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>Active Now</option>
                <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                <option value="DRAFT" {{ $status === 'DRAFT' ? 'selected' : '' }}>Draft</option>
            </select>

            <button type="submit" class="btn btn-secondary" style="padding: 10px 16px;">
                Filter
            </button>
            @if($search || $status)
                <a href="{{ route('placement-exams.index') }}" class="btn btn-secondary" style="padding: 10px 14px; color: #f87171;">
                    Clear
                </a>
            @endif
        </form>

        <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #34d399;"></span>
            <span>Secret 6-Digit Code Gating Enabled</span>
        </div>
    </div>

    <!-- Drives Grid Cards -->
    @if($placementExams->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(370px, 1fr)); gap: 20px;">
            @foreach($placementExams as $drive)
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                    <div>
                        <!-- Top Row: Company & Status Badge -->
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; color: #818cf8; flex-shrink: 0;">
                                    🏢
                                </div>
                                <div>
                                    <h3 style="font-size: 16px; font-weight: 800; color: #fff; line-height: 1.2; margin-bottom: 2px;">
                                        {{ $drive->company_name }}
                                    </h3>
                                    <span style="font-size: 11px; font-weight: 700; color: #38bdf8; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                        {{ $drive->job_role }}
                                    </span>
                                </div>
                            </div>

                            <span class="status-pill {{ strtolower($drive->status) === 'active' ? 'active' : (strtolower($drive->status) === 'completed' ? 'completed' : 'upcoming') }}">
                                {{ $drive->status }}
                            </span>
                        </div>

                        <!-- Drive Title -->
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; line-height: 1.4;">
                            {{ $drive->title }}
                        </h4>

                        <!-- Meta Chips -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 10px 12px; margin-bottom: 16px; font-size: 12px;">
                            <div>
                                <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Package (CTC)</span>
                                <strong style="color: #34d399; font-size: 13px; font-weight: 800;">{{ $drive->package_lpa }}</strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Min Eligibility</span>
                                <strong style="color: #fbbf24; font-size: 13px; font-weight: 800;">CGPA &ge; {{ $drive->min_cgpa }}</strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Exam Date &amp; Window</span>
                                <strong style="color: #fff;">{{ $drive->exam_date }}</strong>
                            </div>
                            <div>
                                <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Duration / Marks</span>
                                <strong style="color: #fff;">{{ $drive->duration_minutes }}m / {{ $drive->total_marks }}M</strong>
                            </div>
                        </div>

                        <!-- Stats Bar (Candidates & Questions & Teachers) -->
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-top: 1px solid var(--border-color); font-size: 11.5px; color: var(--text-secondary); margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="users" style="width: 14px; height: 14px; color: #818cf8;"></i>
                                <span><strong>{{ $drive->candidates->count() }}</strong> Enrolled</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="help-circle" style="width: 14px; height: 14px; color: #38bdf8;"></i>
                                <span><strong>{{ $drive->questions->count() }}</strong> Questions</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="user-check" style="width: 14px; height: 14px; color: #34d399;"></i>
                                <span><strong>{{ $drive->assignedTeachers->count() }}</strong> Faculty</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="{{ route('placement-exams.show', $drive->id) }}" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12.5px; padding: 10px;">
                            <i data-lucide="settings" style="width: 15px; height: 15px;"></i>
                            <span>Manage Drive &amp; Codes</span>
                        </a>

                        <a href="{{ route('placement-exams.exportCsv', $drive->id) }}" class="btn btn-secondary" title="Download Candidate Access Codes CSV" style="padding: 10px 12px; color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
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
        <div style="background: var(--bg-secondary); border: 1.5px dashed var(--border-color); border-radius: var(--radius-lg); padding: 50px 24px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 14px;">💼</div>
            <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 6px;">No Placement Drives Found</h3>
            <p style="font-size: 13px; color: var(--text-secondary); max-width: 480px; margin: 0 auto 20px auto;">
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
