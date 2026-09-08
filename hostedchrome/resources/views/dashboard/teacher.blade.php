@extends('layouts.admin')

@section('title', 'Faculty Command Center')
@section('breadcrumb', 'Teacher Dashboard & Analytics')

@section('styles')
<style>
    .quota-card {
        background: var(--bg-card);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid var(--border-glass);
        outline: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: var(--radius-lg);
        padding: 22px 24px;
        position: relative;
        box-shadow: var(--glass-shadow);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .quota-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--glass-shadow-hover);
        border-color: rgba(99, 102, 241, 0.35);
    }

    .quota-meter-bar {
        height: 8px;
        background: rgba(226, 232, 240, 0.8);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 14px;
    }

    .quota-meter-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>
@endsection

@section('content')
<!-- Teacher Profile & Institution Header Banner -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 244, 255, 0.85)); border-left: 5px solid #4f46e5; box-shadow: 0 12px 36px -8px rgba(79, 70, 229, 0.12); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; top: -20px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(99, 102, 241, 0.12), transparent 70%); pointer-events: none;"></div>

    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 76px; height: 76px; border-radius: 18px; background: linear-gradient(135deg, #4f46e5, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35); flex-shrink: 0;">
                {{ substr($currentUser->full_name, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">{{ $currentUser->full_name }}</h1>
                    <span class="status-pill active" style="font-size: 11px;">
                        <i data-lucide="award" style="width: 12px; height: 12px;"></i>
                        {{ $currentUser->designation ?? 'Faculty Professor' }}
                    </span>
                    <span class="mono" style="font-size: 11px; background: rgba(79, 70, 229, 0.08); color: #4338ca; padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(79, 70, 229, 0.2); font-weight: 700;">
                        ID: {{ $currentUser->student_id }}
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 16px; margin-top: 8px; font-size: 13px; color: var(--text-secondary); flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="building-2" style="width: 15px; height: 15px; color: #4f46e5;"></i>
                        <strong style="color: #1e293b;">{{ $currentUser->college_name }}</strong>
                    </span>
                    <span>&bull;</span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="book-marked" style="width: 15px; height: 15px; color: #059669;"></i>
                        <strong style="color: #1e293b;">{{ $currentUser->department ?? $currentUser->stream }}</strong>
                    </span>
                    <span>&bull;</span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="phone" style="width: 15px; height: 15px; color: #d97706;"></i>
                        <span>{{ $currentUser->phone ?? 'N/A' }}</span>
                    </span>
                    <span>&bull;</span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="mail" style="width: 15px; height: 15px; color: #0284c7;"></i>
                        <span>{{ $currentUser->email }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if($currentUser->canEnrollStudents())
                <a href="{{ route('candidates.create') }}" class="quick-action-btn secondary">
                    <i data-lucide="user-plus" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                    <span>Register Student</span>
                </a>
            @endif

            @if($currentUser->canCreateExams())
                <a href="{{ route('exams.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);">
                    <i data-lucide="calendar-plus" style="width: 16px; height: 16px;"></i>
                    <span>Schedule Exam</span>
                </a>
            @endif

            @if($currentUser->canManageLessons() || $currentUser->canManageCourses())
                <a href="{{ route('courses.index') }}" class="quick-action-btn secondary">
                    <i data-lucide="book-open" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                    <span>Curriculum &amp; Courses</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Quota & Telemetry Metric Cards -->
<div class="metrics-grid">
    <!-- Student Limit Quota -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">Student Capacity Quota</div>
            <span class="mono" style="font-size: 13px; font-weight: 800; color: #4f46e5;">
                {{ $myStudentsCount }} / {{ $maxStudentsAllowed }}
            </span>
        </div>
        <div class="quota-meter-bar">
            <div class="quota-meter-fill" style="width: {{ $studentQuotaPct }}%; background: linear-gradient(90deg, #4f46e5, #3b82f6);"></div>
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 10px; display: flex; justify-content: space-between; font-weight: 600;">
            <span>{{ $studentQuotaPct }}% Allocated</span>
            <span style="color: #059669;">{{ max(0, $maxStudentsAllowed - $myStudentsCount) }} remaining</span>
        </div>
    </div>

    <!-- Exam Conducting Quota -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">Exam Scheduling Quota</div>
            <span class="mono" style="font-size: 13px; font-weight: 800; color: #059669;">
                {{ $myExamsCount }} / {{ $maxExamsAllowed }}
            </span>
        </div>
        <div class="quota-meter-bar">
            <div class="quota-meter-fill" style="width: {{ $examQuotaPct }}%; background: linear-gradient(90deg, #10b981, #06b6d4);"></div>
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 10px; display: flex; justify-content: space-between; font-weight: 600;">
            <span>{{ $examQuotaPct }}% Scheduled</span>
            <span style="color: #059669;">{{ max(0, $maxExamsAllowed - $myExamsCount) }} remaining</span>
        </div>
    </div>

    <!-- Batch Performance Average -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">Batch Average Score</div>
            <i data-lucide="trending-up" style="color: #d97706; width: 18px; height: 18px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: #0f172a; font-family: 'Outfit', sans-serif; margin-top: 6px;">
            {{ number_format($avgBatchScore, 1) }}%
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">
            Across {{ $totalSubmissionsCount > 0 ? $totalSubmissionsCount . ' evaluated submissions' : $myStudentsCount . ' enrolled students' }} (Pass Rate: <strong style="color: #059669;">{{ $passRate }}%</strong>)
        </div>
    </div>

    <!-- Live Incidents -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">Proctor Incidents</div>
            <i data-lucide="shield-alert" style="color: #dc2626; width: 18px; height: 18px;"></i>
        </div>
        <div style="font-size: 28px; font-weight: 800; color: #dc2626; font-family: 'Outfit', sans-serif; margin-top: 6px;">
            {{ $myViolations->count() }} Alert{{ $myViolations->count() == 1 ? '' : 's' }}
        </div>
        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">
            <a href="{{ route('monitoring.index') }}" style="color: #4f46e5; font-weight: 700; text-decoration: none;">Launch Realtime Radar &rarr;</a>
        </div>
    </div>
</div>

<!-- Dual Columns: Teacher's Exams & Teacher's Student Roster -->
<div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 24px; margin-bottom: 32px;">
    <!-- Teacher's Exams -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div>
                <div class="card-title">
                    <i data-lucide="calendar" style="color: #4f46e5; width: 20px; height: 20px;"></i>
                    <span>My Scheduled & Active Exams</span>
                </div>
                <p style="color: var(--text-muted); font-size: 12.5px; margin-top: 2px;">Exams authored and proctored under your faculty account</p>
            </div>
            <a href="{{ route('exams.create') }}" class="quick-action-btn" style="font-size: 12px; padding: 7px 14px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>New Exam</span>
            </a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($myExams as $exam)
                <div style="background: rgba(255, 255, 255, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="mono" style="font-size: 11.5px; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.08); padding: 2px 6px; border-radius: 4px;">{{ $exam->exam_code }}</span>
                            <span class="status-pill {{ strtolower($exam->status) }}" style="font-size: 10.5px; padding: 2px 8px;">{{ $exam->status }}</span>
                        </div>
                        <h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-top: 6px;">{{ $exam->title }}</h4>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                            Date: <strong style="color: #334155;">{{ $exam->exam_date }}</strong> &bull; Duration: <strong style="color: #334155;">{{ $exam->duration_minutes }}m</strong> &bull; Total: <strong style="color: #334155;">{{ $exam->total_marks }} pts</strong>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn secondary" style="font-size: 11.5px; padding: 6px 12px;">
                            <i data-lucide="radio" style="width: 13px; height: 13px; color: #059669;"></i>
                            <span>Radar</span>
                        </a>
                        <a href="{{ route('exams.show', $exam->exam_code) }}" class="quick-action-btn" style="font-size: 11.5px; padding: 6px 12px;">
                            <i data-lucide="settings" style="width: 13px; height: 13px;"></i>
                            <span>Manage</span>
                        </a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="file-plus" style="width: 32px; height: 32px; margin-bottom: 8px; color: #94a3b8;"></i>
                    <p>No exams created yet. Click "Schedule Exam" to begin.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Top Performing Students in Batch -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div>
                <div class="card-title">
                    <i data-lucide="trophy" style="color: #d97706; width: 20px; height: 20px;"></i>
                    <span>Top Performing Students</span>
                </div>
                <p style="color: var(--text-muted); font-size: 12.5px; margin-top: 2px;">Highest scoring candidates in your college batch</p>
            </div>
            <a href="{{ route('candidates.index') }}" style="color: #4f46e5; font-size: 12.5px; text-decoration: none; font-weight: 700;">View Roster &rarr;</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($topPerformers as $index => $cand)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: rgba(248, 250, 252, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 14px; font-weight: 900; color: {{ $index == 0 ? '#d97706' : ($index == 1 ? '#64748b' : ($index == 2 ? '#ea580c' : '#4f46e5')) }};">
                            #{{ $index + 1 }}
                        </span>
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #0284c7); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);">
                            {{ substr($cand->full_name, 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $cand->full_name }}</div>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $cand->student_id }}</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        @php
                            $scoreVal = (float)$cand->average_score;
                            $scoreColor = $scoreVal >= 75 ? '#059669' : ($scoreVal >= 50 ? '#d97706' : '#dc2626');
                        @endphp
                        <div style="font-size: 16px; font-weight: 800; color: {{ $scoreColor }}; font-family: 'Outfit', sans-serif;">
                            {{ number_format($scoreVal, 1) }}%
                        </div>
                        <a href="{{ route('candidates.show', $cand->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 10px;">Profile &rarr;</a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <p>No student scores recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Student Roster Quick Table -->
<div class="glass-card">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="users" style="color: #3b82f6; width: 22px; height: 22px;"></i>
                <span>Enrolled Student Roster ({{ $myStudentsCount }} Students)</span>
            </div>
            <p style="color: var(--text-muted); font-size: 12.5px; margin-top: 2px;">
                Manage student profiles, credentials, streams, and inspect exam records
            </p>
        </div>
        <a href="{{ route('candidates.create') }}" class="quick-action-btn" style="font-size: 12px; padding: 8px 14px;">
            <i data-lucide="user-plus" style="width: 14px; height: 14px;"></i>
            <span>Add Student</span>
        </a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Student Name & ID</th>
                    <th>Course / Stream</th>
                    <th>Email & Phone</th>
                    <th>Exams Completed</th>
                    <th>Avg Score</th>
                    <th>Access Code</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myStudents as $student)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #ec4899); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);">
                                    {{ substr($student->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $student->full_name }}</div>
                                    <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $student->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e293b;">{{ $student->course ?? 'B.Tech' }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $student->stream }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155;">{{ $student->email }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $student->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 800; color: #0f172a;">{{ $student->exams_completed }}</span>
                            <span style="font-size: 12px; color: var(--text-muted);"> / {{ $student->exams_enrolled }}</span>
                        </td>
                        <td>
                            @php
                                $stuScore = (float)$student->average_score;
                                $stuColor = $stuScore >= 75 ? '#059669' : ($stuScore >= 50 ? '#d97706' : '#dc2626');
                            @endphp
                            <span style="font-weight: 800; color: {{ $stuColor }}; font-family: 'Outfit', sans-serif;">
                                {{ number_format($stuScore, 1) }}%
                            </span>
                        </td>
                        <td>
                            <span class="mono" style="background: rgba(79, 70, 229, 0.08); color: #4338ca; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700;">
                                {{ $student->access_code ?? '123456' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('candidates.show', $student->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 5px 10px;">
                                    <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                                    <span>Profile</span>
                                </a>
                                <a href="{{ route('candidates.edit', $student->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 5px 10px;">
                                    <i data-lucide="edit" style="width: 13px; height: 13px;"></i>
                                    <span>Edit</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            No students enrolled yet. Click "Add Student" to register candidates within your allocated quota.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
