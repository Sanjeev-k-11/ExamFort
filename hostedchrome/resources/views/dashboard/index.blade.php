@extends('layouts.admin')

@section('title', 'Executive Command Center')
@section('breadcrumb', 'System Command Center')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #fff;">System Telemetry & Multi-Tenant Hub</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Super Administrator oversight for partner institutions, Deans, faculty quotas, live proctoring radar, and student assessments.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('monitoring.index') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                <i data-lucide="radio" style="width: 16px; height: 16px;"></i>
                <span>Open Live Radar</span>
            </a>
            <a href="{{ route('organizations.create') }}" class="quick-action-btn secondary">
                <i data-lucide="building-2" style="width: 16px; height: 16px;"></i>
                <span>Register College</span>
            </a>
            <a href="{{ route('principals.create') }}" class="quick-action-btn">
                <i data-lucide="crown" style="width: 16px; height: 16px;"></i>
                <span>Appoint Dean</span>
            </a>
        </div>
    </div>
</div>

<!-- Primary Governance Metrics Grid -->
<div class="metrics-grid">
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #818cf8;">
        <div class="metric-icon-box">
            <i data-lucide="building-2" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalOrganizations }}</div>
            <div class="metric-label">Partner Organizations ({{ $totalPrincipals }} Deans)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #34d399;">
        <div class="metric-icon-box" style="color: #34d399;">
            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalTeachers }}</div>
            <div class="metric-label">Faculty Proctors ({{ $totalCandidates }} Candidates)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #fbbf24;">
        <div class="metric-icon-box" style="color: #fbbf24;">
            <i data-lucide="file-text" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalExams }}</div>
            <div class="metric-label">Total Exams ({{ $activeExamsCount }} Live)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ef4444, #f97316); --accent-color: #f87171;">
        <div class="metric-icon-box" style="color: #f87171;">
            <i data-lucide="shield-alert" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalViolations }}</div>
            <div class="metric-label">Proctoring Threat Flags</div>
        </div>
    </div>
</div>

<!-- Partner Organizations Overview Section -->
<div class="glass-card" style="margin-bottom: 32px;">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="building-2" style="color: #818cf8; width: 22px; height: 22px;"></i>
                <span>Partner Institutions & Quota Limits</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Registered Universities, Colleges, and Board Authorities</p>
        </div>
        <a href="{{ route('organizations.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
            <span>View All Organizations</span>
            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        @forelse($organizations as $org)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <span class="mono" style="font-size: 11px; font-weight: 800; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 2px 6px; border-radius: 4px;">{{ $org->code }}</span>
                        <span class="status-pill {{ $org->status === 'ACTIVE' ? 'active' : 'danger' }}">{{ $org->status }}</span>
                    </div>
                    <h4 style="font-size: 15px; font-weight: 700; color: #fff;">{{ $org->name }}</h4>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $org->type }}</div>
                </div>

                <div style="display: flex; gap: 14px; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); font-size: 11px; color: var(--text-secondary);">
                    <span>Deans: <strong style="color: #fff;">{{ $org->principals_count }}</strong></span>
                    <span>Faculty: <strong style="color: #818cf8;">{{ $org->teachers_count }}</strong></span>
                    <span>Annual Quota: <strong style="color: #34d399;">{{ $org->max_exams_allowed }} / yr</strong></span>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 24px; color: var(--text-muted);">
                <p>No organizations registered. Click "Register College" to add institutions.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Public Consultation Leads & Inquiries Section -->
<div class="glass-card" style="margin-bottom: 32px; border-color: rgba(99, 102, 241, 0.35);">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="mail-question" style="color: #38bdf8; width: 22px; height: 22px;"></i>
                <span>Institutional Consultation Leads &amp; Demo Requests ({{ $totalInquiriesCount ?? 0 }})</span>
                @if(isset($pendingInquiriesCount) && $pendingInquiriesCount > 0)
                    <span class="status-pill danger" style="margin-left: 8px;">{{ $pendingInquiriesCount }} PENDING</span>
                @endif
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Public leads and consultation requests submitted from the landing website</p>
        </div>
        <a href="{{ route('inquiries.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
            <span>View All Leads &rarr;</span>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
        @forelse($latestInquiries ?? [] as $inq)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <span style="font-size: 11px; color: #818cf8; font-weight: 700;">{{ \Carbon\Carbon::parse($inq->created_at)->diffForHumans() }}</span>
                        <span class="status-pill {{ $inq->status === 'PENDING' ? 'danger' : 'active' }}">{{ $inq->status }}</span>
                    </div>
                    <h4 style="font-size: 14.5px; font-weight: 700; color: #fff;">{{ $inq->full_name }}</h4>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">{{ $inq->organization_name ?: 'Institution not provided' }}</div>
                    
                    <div style="font-size: 12px; color: #c7d2fe; margin-top: 8px; font-weight: 600;">
                        {{ $inq->subject }}
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">
                        "{{ Str::limit($inq->message, 85) }}"
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); font-size: 11px;">
                    <a href="mailto:{{ $inq->email }}" style="color: #38bdf8; text-decoration: none; font-weight: 600;">✉️ {{ $inq->email }}</a>
                    <a href="{{ route('inquiries.index') }}" style="color: #818cf8; text-decoration: none; font-weight: 700;">Manage &rarr;</a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 24px; color: var(--text-muted);">
                <p>No new consultation leads yet. Submissions from the public contact page will show here live.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Scheduled & Active Exams -->
<div class="glass-card" style="margin-bottom: 32px;">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="radio" style="color: #10b981; width: 22px; height: 22px;"></i>
                <span>Institutional Examination Papers ({{ $totalExams }})</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Live, upcoming, and completed examination papers</p>
        </div>
        <a href="{{ route('exams.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
            <span>View All Exams</span>
            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        @forelse($recentExams as $exam)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="mono" style="font-size: 12px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 3px 8px; border-radius: 4px;">{{ $exam->exam_code }}</span>
                        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-top: 8px;">{{ $exam->title }}</h3>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">College: {{ $exam->college_name }}</div>
                    </div>
                    <span class="status-pill {{ strtolower($exam->status) }}">{{ $exam->status }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; background: rgba(0, 0, 0, 0.2); padding: 12px; border-radius: 8px; text-align: center;">
                    <div>
                        <div style="font-size: 11px; color: var(--text-muted);">Questions</div>
                        <div style="font-size: 16px; font-weight: 700; color: #fff;">{{ $exam->questions_count }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-muted);">Submissions</div>
                        <div style="font-size: 16px; font-weight: 700; color: #34d399;">{{ $exam->submissions_count }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-muted);">Incidents</div>
                        <div style="font-size: 16px; font-weight: 700; color: #f87171;">{{ $exam->violations_count }}</div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                        <i data-lucide="radio" style="width: 14px; height: 14px;"></i>
                        <span>Live Radar</span>
                    </a>
                    <a href="{{ route('exams.show', $exam->exam_code) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 8px 12px;">
                        <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                    </a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 32px; color: var(--text-muted);">
                <p>No exams created yet.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Dual Columns: Live Incidents & Recent Submissions -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px;">
    <!-- Recent Incident Telemetry -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div class="card-title">
                <i data-lucide="alert-triangle" style="color: #ef4444; width: 20px; height: 20px;"></i>
                <span>Recent Proctoring Incidents</span>
            </div>
            <a href="{{ route('violations.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600;">View All</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($recentViolations as $violation)
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 12px 14px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div style="display: flex; gap: 12px;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(239, 68, 68, 0.15); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="alert-octagon" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #fff;">
                                {{ $violation->violation_type }}
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                {{ $violation->candidate ? $violation->candidate->full_name : $violation->candidate_id }} &bull; <span class="mono">{{ $violation->exam_code }}</span>
                            </div>
                            <div style="font-size: 11px; color: #cbd5e1; margin-top: 4px;">
                                "{{ Str::limit($violation->details, 65) }}"
                            </div>
                        </div>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">
                        {{ \Carbon\Carbon::parse($violation->timestamp)->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <i data-lucide="shield-check" style="width: 28px; height: 28px; color: #10b981; margin-bottom: 6px;"></i>
                    <p>No proctoring violations recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Candidate Submissions -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div class="card-title">
                <i data-lucide="check-square" style="color: #3b82f6; width: 20px; height: 20px;"></i>
                <span>Recent Submissions</span>
            </div>
            <a href="{{ route('submissions.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600;">View All</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($recentSubmissions as $sub)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #06b6d4); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                            {{ substr($sub->candidate->full_name ?? 'C', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #fff;">
                                {{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                Exam: <span class="mono">{{ $sub->exam_code }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 15px; font-weight: 800; color: #34d399; font-family: 'Outfit', sans-serif;">
                            {{ number_format($sub->total_score, 1) }} pts
                        </div>
                        <a href="{{ route('submissions.show', $sub->id) }}" style="font-size: 11px; color: #818cf8; text-decoration: none;">View Paper &rarr;</a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <i data-lucide="inbox" style="width: 28px; height: 28px; margin-bottom: 6px;"></i>
                    <p>No student submissions recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
