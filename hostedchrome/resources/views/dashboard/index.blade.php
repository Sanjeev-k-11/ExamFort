@extends('layouts.admin')

@section('title', 'Executive Command Center')
@section('breadcrumb', 'System Command Center')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">System Telemetry & Multi-Tenant Hub</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Super Administrator oversight for partner institutions, Deans, faculty quotas, live proctoring radar, and student assessments.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('monitoring.index') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);">
                <i data-lucide="radio" style="width: 16px; height: 16px;"></i>
                <span>Open Live Radar</span>
            </a>
            <a href="{{ route('organizations.create') }}" class="quick-action-btn secondary">
                <i data-lucide="building-2" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                <span>Register College</span>
            </a>
            <a href="{{ route('principals.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);">
                <i data-lucide="crown" style="width: 16px; height: 16px;"></i>
                <span>Appoint Dean</span>
            </a>
        </div>
    </div>
</div>

<!-- GOOGLE GEMINI AI KEY CONFIGURATION CARD FOR ADMIN -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 249, 255, 0.85)); border: 1px solid rgba(14, 165, 233, 0.3);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5, #0284c7); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);">
                ✨
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a;">Google Gemini AI Integration</h3>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 2px;">
                    Powers AI Exam Question Paper Generation, AI Lesson Studios, Descriptive Essay Evaluation, and Placement Paper Setter.
                </p>
            </div>
        </div>

        <div>
            @if(!empty($currentUser->gemini_api_key))
                <span class="status-pill active" style="font-size: 12px; padding: 5px 14px; background: rgba(16, 185, 129, 0.15); color: #047857; border: 1px solid rgba(16, 185, 129, 0.35);">
                    <i data-lucide="check-circle" style="width: 15px; height: 15px;"></i>
                    <span>AI Engine Configured & Active</span>
                </span>
            @else
                <span class="status-pill danger" style="font-size: 12px; padding: 5px 14px;">
                    <i data-lucide="alert-triangle" style="width: 15px; height: 15px;"></i>
                    <span>Gemini API Key Missing</span>
                </span>
            @endif
        </div>
    </div>

    <form action="{{ route('principals.updateGeminiKey') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        @csrf
        <div style="flex: 1; min-width: 320px; position: relative;">
            <i data-lucide="key" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: #4f46e5;"></i>
            <input type="password" name="gemini_api_key" id="adminGeminiKeyInput" class="form-control mono" style="padding-left: 40px; padding-right: 44px; font-size: 13px;" placeholder="Paste your Google Gemini API Key (e.g. AIzaSy...)" value="{{ $currentUser->gemini_api_key }}">
            <button type="button" onclick="const el = document.getElementById('adminGeminiKeyInput'); el.type = el.type === 'password' ? 'text' : 'password';" style="position: absolute; right: 12px; top: 10px; background: none; border: none; color: var(--text-muted); cursor: pointer;">
                <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
            <i data-lucide="save" style="width: 15px; height: 15px;"></i>
            <span>Save Gemini Key</span>
        </button>
    </form>
</div>

<!-- Primary Governance Metrics Grid -->
<div class="metrics-grid">
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #4f46e5, #3b82f6); --accent-color: #4f46e5;">
        <div class="metric-icon-box">
            <i data-lucide="building-2" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalOrganizations }}</div>
            <div class="metric-label">Partner Organizations ({{ $totalPrincipals }} Deans)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #059669;">
        <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.2); color: #059669;">
            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalTeachers }}</div>
            <div class="metric-label">Faculty Proctors ({{ $totalCandidates }} Candidates)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #d97706;">
        <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.2); color: #d97706;">
            <i data-lucide="file-text" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $totalExams }}</div>
            <div class="metric-label">Total Exams ({{ $activeExamsCount }} Live)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ef4444, #f97316); --accent-color: #dc2626;">
        <div class="metric-icon-box" style="background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.2); color: #dc2626;">
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
                <i data-lucide="building-2" style="color: #4f46e5; width: 22px; height: 22px;"></i>
                <span>Partner Institutions & Quota Limits</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Registered Universities, Colleges, and Board Authorities</p>
        </div>
        <a href="{{ route('organizations.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 4px;">
            <span>View All Organizations</span>
            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        @forelse($organizations as $org)
            <div style="background: rgba(255, 255, 255, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <span class="mono" style="font-size: 11px; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.08); padding: 2px 6px; border-radius: 4px;">{{ $org->code }}</span>
                        <span class="status-pill {{ $org->status === 'ACTIVE' ? 'active' : 'danger' }}">{{ $org->status }}</span>
                    </div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0f172a;">{{ $org->name }}</h4>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $org->type }}</div>
                </div>

                <div style="display: flex; gap: 14px; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); font-size: 11.5px; color: var(--text-secondary); font-weight: 600;">
                    <span>Deans: <strong style="color: #0f172a;">{{ $org->principals_count }}</strong></span>
                    <span>Faculty: <strong style="color: #4f46e5;">{{ $org->teachers_count }}</strong></span>
                    <span>Annual Quota: <strong style="color: #059669;">{{ $org->max_exams_allowed }} / yr</strong></span>
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
                <i data-lucide="mail-question" style="color: #0284c7; width: 22px; height: 22px;"></i>
                <span>Institutional Consultation Leads &amp; Demo Requests ({{ $totalInquiriesCount ?? 0 }})</span>
                @if(isset($pendingInquiriesCount) && $pendingInquiriesCount > 0)
                    <span class="status-pill danger" style="margin-left: 8px;">{{ $pendingInquiriesCount }} PENDING</span>
                @endif
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Public leads and consultation requests submitted from the landing website</p>
        </div>
        <a href="{{ route('inquiries.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 4px;">
            <span>View All Leads &rarr;</span>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
        @forelse($latestInquiries ?? [] as $inq)
            <div style="background: rgba(255, 255, 255, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <span style="font-size: 11.5px; color: #4f46e5; font-weight: 700;">{{ \Carbon\Carbon::parse($inq->created_at)->diffForHumans() }}</span>
                        <span class="status-pill {{ $inq->status === 'PENDING' ? 'danger' : 'active' }}">{{ $inq->status }}</span>
                    </div>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0f172a;">{{ $inq->full_name }}</h4>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">{{ $inq->organization_name ?: 'Institution not provided' }}</div>
                    
                    <div style="font-size: 12.5px; color: #4338ca; margin-top: 8px; font-weight: 700;">
                        {{ $inq->subject }}
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">
                        "{{ Str::limit($inq->message, 85) }}"
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); font-size: 11.5px;">
                    <a href="mailto:{{ $inq->email }}" style="color: #0284c7; text-decoration: none; font-weight: 700;">✉️ {{ $inq->email }}</a>
                    <a href="{{ route('inquiries.index') }}" style="color: #4f46e5; text-decoration: none; font-weight: 700;">Manage &rarr;</a>
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
                <i data-lucide="radio" style="color: #059669; width: 22px; height: 22px;"></i>
                <span>Institutional Examination Papers ({{ $totalExams }})</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Live, upcoming, and completed examination papers</p>
        </div>
        <a href="{{ route('exams.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 4px;">
            <span>View All Exams</span>
            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        @forelse($recentExams as $exam)
            <div style="background: rgba(255, 255, 255, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03); position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span class="mono" style="font-size: 11.5px; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.08); padding: 3px 8px; border-radius: 4px;">{{ $exam->exam_code }}</span>
                        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 8px;">{{ $exam->title }}</h3>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">College: {{ $exam->college_name }}</div>
                    </div>
                    <span class="status-pill {{ strtolower($exam->status) }}">{{ $exam->status }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; background: rgba(248, 250, 252, 0.9); border: 1px solid rgba(226, 232, 240, 0.8); padding: 12px; border-radius: 10px; text-align: center;">
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Questions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $exam->questions_count }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Submissions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #059669; margin-top: 2px;">{{ $exam->submissions_count }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Incidents</div>
                        <div style="font-size: 16px; font-weight: 800; color: #dc2626; margin-top: 2px;">{{ $exam->violations_count }}</div>
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
                <i data-lucide="alert-triangle" style="color: #dc2626; width: 20px; height: 20px;"></i>
                <span>Recent Proctoring Incidents</span>
            </div>
            <a href="{{ route('violations.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700;">View All</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($recentViolations as $violation)
                <div style="display: flex; align-items: flex-start; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div style="display: flex; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(239, 68, 68, 0.12); color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="alert-octagon" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                                {{ $violation->violation_type }}
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                {{ $violation->candidate ? $violation->candidate->full_name : $violation->candidate_id }} &bull; <span class="mono" style="color: #4f46e5;">{{ $violation->exam_code }}</span>
                            </div>
                            <div style="font-size: 12px; color: #334155; margin-top: 4px;">
                                "{{ Str::limit($violation->details, 65) }}"
                            </div>
                        </div>
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted); white-space: nowrap; font-weight: 600;">
                        {{ \Carbon\Carbon::parse($violation->timestamp)->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <i data-lucide="shield-check" style="width: 28px; height: 28px; color: #059669; margin-bottom: 6px;"></i>
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
            <a href="{{ route('submissions.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700;">View All</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($recentSubmissions as $sub)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #0284c7); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);">
                            {{ substr($sub->candidate->full_name ?? 'C', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                                {{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}
                            </div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">
                                Exam: <span class="mono" style="color: #4f46e5;">{{ $sub->exam_code }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 16px; font-weight: 800; color: #059669; font-family: 'Outfit', sans-serif;">
                            {{ number_format($sub->total_score, 1) }} pts
                        </div>
                        <a href="{{ route('submissions.show', $sub->id) }}" style="font-size: 11.5px; color: #4f46e5; font-weight: 700; text-decoration: none;">View Paper &rarr;</a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <i data-lucide="inbox" style="width: 28px; height: 28px; margin-bottom: 6px; color: #94a3b8;"></i>
                    <p>No student submissions recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
