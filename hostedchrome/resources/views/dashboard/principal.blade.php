@extends('layouts.admin')

@section('title', 'Principal & Dean Command Center')
@section('breadcrumb', 'Dean Command Center')

@section('content')
<!-- Dean / Principal Institutional Banner -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(30, 27, 75, 0.8), rgba(15, 23, 42, 0.9)); border-left: 4px solid #818cf8;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <div style="width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #4338ca, #6366f1); color: white; display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: 800; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);">
                🏛️
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h1 style="font-size: 24px; font-weight: 800; color: #fff;">{{ $principal->full_name }}</h1>
                    <span class="status-pill active" style="font-size: 11px; background: rgba(99, 102, 241, 0.2); color: #818cf8; border-color: rgba(99, 102, 241, 0.4);">
                        INSTITUTION DEAN & PRINCIPAL
                    </span>
                </div>
                <div style="font-size: 15px; font-weight: 700; color: #cbd5e1; margin-top: 4px;">
                    {{ $principal->college_name }}
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Department: {{ $principal->department ?? 'Academic Administration' }} &bull; ID: <code class="mono" style="color: #818cf8;">{{ $principal->student_id }}</code> &bull; Contact: {{ $principal->phone ?? 'N/A' }}
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('ai.generator') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); box-shadow: 0 4px 14px rgba(6, 182, 212, 0.4);">
                <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                <span>✨ AI Exam Generator</span>
            </a>
            <a href="{{ route('exams.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #6366f1, #3b82f6);">
                <i data-lucide="calendar-plus" style="width: 16px; height: 16px;"></i>
                <span>Schedule College Exam</span>
            </a>
            <a href="{{ route('teachers.create') }}" class="quick-action-btn secondary">
                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                <span>Add Teacher / Faculty</span>
            </a>
            <a href="{{ route('candidates.create') }}" class="quick-action-btn secondary">
                <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i>
                <span>Enroll Student</span>
            </a>
        </div>
    </div>
</div>

<!-- GOOGLE GEMINI AI KEY CONFIGURATION CARD FOR PRINCIPAL -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.85)); border: 1px solid rgba(99, 102, 241, 0.35);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #4f46e5, #06b6d4); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                ✨
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff;">Google Gemini AI Integration</h3>
                <p style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                    Powers AI automated descriptive essay grading, smart rubric scoring, and proctoring threat intelligence.
                </p>
            </div>
        </div>

        <div>
            @if(!empty($principal->gemini_api_key))
                <span class="status-pill active" style="font-size: 12px; padding: 4px 12px;">
                    <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                    <span>AI Engine Configured & Active</span>
                </span>
            @else
                <span class="status-pill danger" style="font-size: 12px; padding: 4px 12px;">
                    <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                    <span>Gemini API Key Missing</span>
                </span>
            @endif
        </div>
    </div>

    <form action="{{ route('principals.updateGeminiKey') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        @csrf
        <div style="flex: 1; min-width: 320px; position: relative;">
            <i data-lucide="key" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: #818cf8;"></i>
            <input type="password" name="gemini_api_key" id="geminiKeyInput" class="form-control mono" style="padding-left: 40px; padding-right: 44px; font-size: 13px;" placeholder="Paste your Google Gemini API Key (e.g. AIzaSy...)" value="{{ $principal->gemini_api_key }}">
            <button type="button" onclick="toggleKeyVisibility()" style="position: absolute; right: 12px; top: 10px; background: none; border: none; color: var(--text-muted); cursor: pointer;">
                <i data-lucide="eye" id="toggleKeyIcon" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669);">
            <i data-lucide="save" style="width: 15px; height: 15px;"></i>
            <span>Save Gemini Key</span>
        </button>
    </form>
</div>

<!-- Quota & Institutional Metrics Bar -->
<div class="metrics-grid" style="margin-bottom: 32px;">
    <!-- 1. Annual Exam Quota (Assigned by Super Admin) -->
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #818cf8;">
        <div class="metric-icon-box">
            <i data-lucide="calendar" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info" style="flex: 1;">
            @php
                $examsUsed = $exams->count();
                $maxExams = $principal->max_exams_allowed ?? 100;
                $examPct = min(100, round(($examsUsed / max(1, $maxExams)) * 100));
            @endphp
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div class="metric-value">{{ $examsUsed }} <span style="font-size: 14px; color: var(--text-muted); font-weight: normal;">/ {{ $maxExams }}</span></div>
                <span style="font-size: 12px; font-weight: 700; color: {{ $examPct > 80 ? '#f87171' : '#34d399' }};">{{ $examPct }}% Used</span>
            </div>
            <div class="metric-label">Annual Exam Quota (Set by Admin)</div>
            <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; margin-top: 8px; overflow: hidden;">
                <div style="width: {{ $examPct }}%; height: 100%; background: {{ $examPct > 80 ? '#ef4444' : 'var(--accent-gradient)' }}; border-radius: 3px;"></div>
            </div>
        </div>
    </div>

    <!-- 2. Faculty / Teachers Headcount -->
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #34d399;">
        <div class="metric-icon-box" style="color: #34d399;">
            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $teachers->count() }}</div>
            <div class="metric-label">Faculty & Proctors in College</div>
        </div>
    </div>

    <!-- 3. Total Students Enrolled -->
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #fbbf24;">
        <div class="metric-icon-box" style="color: #fbbf24;">
            <i data-lucide="graduation-cap" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $studentsCount }}</div>
            <div class="metric-label">Total Students Enrolled</div>
        </div>
    </div>

    <!-- 4. Total Submissions & Violations -->
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ef4444, #f97316); --accent-color: #f87171;">
        <div class="metric-icon-box" style="color: #f87171;">
            <i data-lucide="shield-alert" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalSubmissions }}</div>
            <div class="metric-label">Graded Papers ({{ $totalViolations }} Flags)</div>
        </div>
    </div>
</div>

<!-- College Faculty & Teachers Roster -->
<div class="glass-card" style="margin-bottom: 32px;">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="users" style="color: #6366f1; width: 22px; height: 22px;"></i>
                <span>College Faculty & Proctor Accounts ({{ $teachers->count() }})</span>
            </div>
            <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                Manage teachers, assign student quotas, and monitor departmental examination workloads.
            </p>
        </div>
        <a href="{{ route('teachers.create') }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>Add New Faculty</span>
        </a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Faculty Name & ID</th>
                    <th>Department & Designation</th>
                    <th>Email & Contact</th>
                    <th>Student Capacity Quota</th>
                    <th>Exams Quota</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $t)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                                    {{ substr($t->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #fff;">{{ $t->full_name }}</div>
                                    <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $t->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #cbd5e1;">{{ $t->department ?? 'General' }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $t->designation ?? 'Faculty' }}</div>
                        </td>
                        <td>
                            <div>{{ $t->email }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $t->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <strong style="color: #fff;">{{ $t->students_count }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);"> / {{ $t->max_students_allowed ?? 100 }}</span>
                        </td>
                        <td>
                            <strong style="color: #818cf8;">{{ $t->created_exams_count }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);"> / {{ $t->max_exams_allowed ?? 10 }}</span>
                        </td>
                        <td>
                            <span class="status-pill {{ ($t->status ?? 'ACTIVE') === 'ACTIVE' ? 'active' : 'danger' }}">
                                {{ $t->status ?? 'ACTIVE' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('teachers.show', $t->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                    <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                </a>
                                <a href="{{ route('teachers.edit', $t->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                    <i data-lucide="edit" style="width: 12px; height: 12px;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            No faculty accounts registered under your college. Click "Add New Faculty" to register professors.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- College Examination Papers Section -->
<div class="glass-card">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="file-text" style="color: #10b981; width: 22px; height: 22px;"></i>
                <span>College Examination Schedule & Papers ({{ $exams->count() }})</span>
            </div>
            <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                Scheduled, active, and completed examination sessions conducted under your institutional quota
            </p>
        </div>
        <a href="{{ route('exams.create') }}" class="quick-action-btn" style="font-size: 12px; padding: 6px 12px;">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>Schedule Exam</span>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
        @forelse($exams as $exam)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <div>
                        <span class="mono" style="font-size: 11px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 2px 6px; border-radius: 4px;">{{ $exam->exam_code }}</span>
                        <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 6px;">{{ $exam->title }}</h4>
                    </div>
                    <span class="status-pill {{ strtolower($exam->status) }}">{{ $exam->status }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 14px 0; background: rgba(0, 0, 0, 0.2); padding: 10px; border-radius: 6px; text-align: center; font-size: 11px;">
                    <div>
                        <div style="color: var(--text-muted);">Questions</div>
                        <div style="font-size: 14px; font-weight: 700; color: #fff;">{{ $exam->questions_count }}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted);">Submissions</div>
                        <div style="font-size: 14px; font-weight: 700; color: #34d399;">{{ $exam->submissions_count }}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted);">Violations</div>
                        <div style="font-size: 14px; font-weight: 700; color: #f87171;">{{ $exam->violations_count }}</div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn secondary" style="flex: 1; justify-content: center; font-size: 11px; padding: 6px;">
                        <i data-lucide="radio" style="width: 12px; height: 12px; color: #10b981;"></i>
                        <span>Live Radar</span>
                    </a>
                    <a href="{{ route('exams.show', $exam->exam_code) }}" class="quick-action-btn" style="flex: 1; justify-content: center; font-size: 11px; padding: 6px;">
                        <i data-lucide="settings" style="width: 12px; height: 12px;"></i>
                        <span>Manage Exam</span>
                    </a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 32px; color: var(--text-muted);">
                No examination papers scheduled yet. Click "Schedule Exam" to conduct assessments.
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleKeyVisibility() {
        const input = document.getElementById('geminiKeyInput');
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }
</script>
@endsection
