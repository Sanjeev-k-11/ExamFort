@extends('layouts.admin')

@section('title', 'Faculty Command Center')
@section('breadcrumb', 'Teacher Dashboard & Analytics')

@section('styles')
<style>
    .quota-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        position: relative;
    }

    .quota-meter-bar {
        height: 8px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 12px;
    }

    .quota-meter-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.6s ease;
    }
</style>
@endsection

@section('content')
<!-- Teacher Profile & Institution Header Banner -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8)); border: 1px solid var(--border-highlight); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; top: -20px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(99, 102, 241, 0.15), transparent 70%); pointer-events: none;"></div>

    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #6366f1, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; box-shadow: 0 0 25px var(--primary-glow); flex-shrink: 0;">
                {{ substr($currentUser->full_name, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="font-size: 24px; font-weight: 800; color: #fff;">{{ $currentUser->full_name }}</h1>
                    <span class="status-pill active" style="font-size: 11px;">
                        <i data-lucide="award" style="width: 12px; height: 12px;"></i>
                        {{ $currentUser->designation ?? 'Faculty Professor' }}
                    </span>
                    <span class="mono" style="font-size: 11px; background: rgba(255,255,255,0.05); color: #cbd5e1; padding: 2px 8px; border-radius: 4px; border: 1px solid var(--border-color);">
                        ID: {{ $currentUser->student_id }}
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 16px; margin-top: 8px; font-size: 13px; color: var(--text-secondary); flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="building-2" style="width: 15px; height: 15px; color: #818cf8;"></i>
                        <strong>{{ $currentUser->college_name }}</strong>
                    </span>
                    <span>&bull;</span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="book-marked" style="width: 15px; height: 15px; color: #34d399;"></i>
                        {{ $currentUser->department ?? $currentUser->stream }}
                    </span>
                    <span>&bull;</span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="phone" style="width: 15px; height: 15px; color: #fbbf24;"></i>
                        {{ $currentUser->phone ?? 'N/A' }}
                    </span>
                    <span>&bull;</span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="mail" style="width: 15px; height: 15px; color: #38bdf8;"></i>
                        {{ $currentUser->email }}
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if($currentUser->canEnrollStudents())
                <a href="{{ route('candidates.create') }}" class="quick-action-btn">
                    <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                    <span>Register Student</span>
                </a>
            @endif

            @if($currentUser->canCreateExams())
                <a href="{{ route('exams.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                    <i data-lucide="calendar-plus" style="width: 16px; height: 16px;"></i>
                    <span>Schedule New Exam</span>
                </a>
            @endif

            @if($currentUser->canManageLessons() || $currentUser->canManageCourses())
                <a href="{{ route('courses.index') }}" class="quick-action-btn secondary" style="border-color: rgba(99, 102, 241, 0.4); color: #c7d2fe;">
                    <i data-lucide="book-open" style="width: 16px; height: 16px; color: #818cf8;"></i>
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
            <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Student Capacity Quota</div>
            <span class="mono" style="font-size: 13px; font-weight: 700; color: #818cf8;">
                {{ $myStudentsCount }} / {{ $maxStudentsAllowed }}
            </span>
        </div>
        <div class="quota-meter-bar">
            <div class="quota-meter-fill" style="width: {{ $studentQuotaPct }}%; background: linear-gradient(90deg, #6366f1, #3b82f6);"></div>
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px; display: flex; justify-content: space-between;">
            <span>{{ $studentQuotaPct }}% Allocated</span>
            <span style="color: #34d399;">{{ max(0, $maxStudentsAllowed - $myStudentsCount) }} remaining</span>
        </div>
    </div>

    <!-- Exam Conducting Quota -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Exam Scheduling Quota</div>
            <span class="mono" style="font-size: 13px; font-weight: 700; color: #34d399;">
                {{ $myExamsCount }} / {{ $maxExamsAllowed }}
            </span>
        </div>
        <div class="quota-meter-bar">
            <div class="quota-meter-fill" style="width: {{ $examQuotaPct }}%; background: linear-gradient(90deg, #10b981, #06b6d4);"></div>
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px; display: flex; justify-content: space-between;">
            <span>{{ $examQuotaPct }}% Scheduled</span>
            <span style="color: #34d399;">{{ max(0, $maxExamsAllowed - $myExamsCount) }} remaining</span>
        </div>
    </div>

    <!-- Batch Performance Average -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Batch Average Score</div>
            <i data-lucide="trending-up" style="color: #fbbf24; width: 18px; height: 18px;"></i>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: #fff; font-family: 'Outfit', sans-serif; margin-top: 6px;">
            {{ number_format($avgBatchScore, 1) }}%
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
            Across {{ $totalSubmissionsCount > 0 ? $totalSubmissionsCount . ' evaluated submissions' : $myStudentsCount . ' enrolled students' }} (Pass Rate: <strong style="color: #34d399;">{{ $passRate }}%</strong>)
        </div>
    </div>

    <!-- Live Incidents -->
    <div class="quota-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Proctor Incidents</div>
            <i data-lucide="shield-alert" style="color: #ef4444; width: 18px; height: 18px;"></i>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: #f87171; font-family: 'Outfit', sans-serif; margin-top: 6px;">
            {{ $myViolations->count() }} Alert{{ $myViolations->count() == 1 ? '' : 's' }}
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
            <a href="{{ route('monitoring.index') }}" style="color: #818cf8; text-decoration: none;">Launch Realtime Radar &rarr;</a>
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
                    <i data-lucide="calendar" style="color: #6366f1; width: 20px; height: 20px;"></i>
                    <span>My Scheduled & Active Exams</span>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">Exams authored and proctored under your faculty account</p>
            </div>
            <a href="{{ route('exams.create') }}" class="quick-action-btn" style="font-size: 12px; padding: 6px 12px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>New Exam</span>
            </a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($myExams as $exam)
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="mono" style="font-size: 12px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 2px 6px; border-radius: 4px;">{{ $exam->exam_code }}</span>
                            <span class="status-pill {{ strtolower($exam->status) }}" style="font-size: 10px; padding: 2px 6px;">{{ $exam->status }}</span>
                        </div>
                        <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 6px;">{{ $exam->title }}</h4>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                            Date: <strong>{{ $exam->exam_date }}</strong> &bull; Duration: <strong>{{ $exam->duration_minutes }}m</strong> &bull; Total: <strong>{{ $exam->total_marks }} pts</strong>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
                            <i data-lucide="radio" style="width: 14px; height: 14px; color: #10b981;"></i>
                            <span>Radar</span>
                        </a>
                        <a href="{{ route('exams.show', $exam->exam_code) }}" class="quick-action-btn" style="font-size: 12px; padding: 6px 12px;">
                            <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                            <span>Manage</span>
                        </a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="file-plus" style="width: 32px; height: 32px; margin-bottom: 8px;"></i>
                    <p>No exams created yet. Click "Schedule New Exam" to begin.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Top Performing Students in Batch -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div>
                <div class="card-title">
                    <i data-lucide="trophy" style="color: #fbbf24; width: 20px; height: 20px;"></i>
                    <span>Top Performing Students</span>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">Highest scoring candidates in your college batch</p>
            </div>
            <a href="{{ route('candidates.index') }}" style="color: #818cf8; font-size: 12px; text-decoration: none; font-weight: 600;">View Roster &rarr;</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($topPerformers as $index => $cand)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 14px; font-weight: 800; color: {{ $index == 0 ? '#fbbf24' : ($index == 1 ? '#cbd5e1' : ($index == 2 ? '#f97316' : '#818cf8')) }};">
                            #{{ $index + 1 }}
                        </span>
                        <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #06b6d4); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                            {{ substr($cand->full_name, 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #fff;">{{ $cand->full_name }}</div>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $cand->student_id }}</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        @php
                            $scoreVal = (float)$cand->average_score;
                            $scoreColor = $scoreVal >= 75 ? '#34d399' : ($scoreVal >= 50 ? '#fbbf24' : '#f87171');
                        @endphp
                        <div style="font-size: 15px; font-weight: 800; color: {{ $scoreColor }}; font-family: 'Outfit', sans-serif;">
                            {{ number_format($scoreVal, 1) }}%
                        </div>
                        <a href="{{ route('candidates.show', $cand->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">Profile &rarr;</a>
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
            <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                Manage student profiles, credentials, streams, and inspect exam records
            </p>
        </div>
        <a href="{{ route('candidates.create') }}" class="quick-action-btn">
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
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #ec4899); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                                    {{ substr($student->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #fff;">{{ $student->full_name }}</div>
                                    <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $student->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $student->course ?? 'B.Tech' }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $student->stream }}</div>
                        </td>
                        <td>
                            <div>{{ $student->email }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $student->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #fff;">{{ $student->exams_completed }}</span>
                            <span style="font-size: 11px; color: var(--text-muted);"> / {{ $student->exams_enrolled }}</span>
                        </td>
                        <td>
                            @php
                                $stuScore = (float)$student->average_score;
                                $stuColor = $stuScore >= 75 ? '#34d399' : ($stuScore >= 50 ? '#fbbf24' : '#f87171');
                            @endphp
                            <span style="font-weight: 800; color: {{ $stuColor }}; font-family: 'Outfit', sans-serif;">
                                {{ number_format($stuScore, 1) }}%
                            </span>
                        </td>
                        <td>
                            <span class="mono" style="background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                                {{ $student->access_code ?? '123456' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('candidates.show', $student->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                    <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                    <span>Profile</span>
                                </a>
                                <a href="{{ route('candidates.edit', $student->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                    <i data-lucide="edit" style="width: 12px; height: 12px;"></i>
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
