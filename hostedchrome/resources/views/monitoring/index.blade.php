@extends('layouts.admin')

@section('title', 'Live Proctoring Radar')
@section('breadcrumb', 'Live Proctoring & Telemetry Radar')

@section('styles')
<style>
    .radar-pulse-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.4);
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .pulse-dot-live {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: radar-ping 1.6s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    @keyframes radar-ping {
        0% { transform: scale(1); opacity: 1; }
        75%, 100% { transform: scale(2.2); opacity: 0; }
    }

    .monitor-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 24px;
    }

    @media (max-width: 1200px) {
        .monitor-grid {
            grid-template-columns: 1fr;
        }
    }

    .telemetry-feed {
        max-height: 580px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-right: 6px;
    }

    .telemetry-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-color);
        border-left: 4px solid #ef4444;
        border-radius: var(--radius-md);
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        transition: all 0.2s;
    }

    .telemetry-card:hover {
        background: rgba(30, 41, 59, 0.6);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .draft-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-color);
        border-left: 4px solid #6366f1;
        border-radius: var(--radius-md);
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .draft-card:hover {
        background: rgba(30, 41, 59, 0.8);
        border-color: var(--border-highlight);
        transform: translateY(-1px);
    }

    .draft-card.active-selected {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.12);
    }

    .filter-tab-btn {
        padding: 6px 14px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }

    .filter-tab-btn:hover, .filter-tab-btn.active {
        background: rgba(99, 102, 241, 0.2);
        border-color: #6366f1;
        color: #fff;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 200;
        padding: 20px;
    }

    .modal-box {
        background: #0f172a;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 540px;
        padding: 28px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Live Proctoring Command Radar</h1>
            <span class="radar-pulse-badge">
                <span class="pulse-dot-live"></span>
                POLLING ACTIVE (4s)
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <!-- Exam Selector Dropdown -->
            <form action="{{ route('monitoring.index') }}" method="GET" style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Active Exam:</label>
                <select name="exam_code" onchange="this.form.submit()" class="form-control" style="padding: 8px 12px; width: auto; font-weight: 600;">
                    @foreach($exams as $ex)
                        <option value="{{ $ex->exam_code }}" {{ $selectedExamCode == $ex->exam_code ? 'selected' : '' }}>
                            {{ $ex->exam_code }} - {{ Str::limit($ex->title, 28) }} ({{ $ex->status }})
                        </option>
                    @endforeach
                </select>
            </form>

            <button onclick="openLogModal()" class="quick-action-btn danger">
                <i data-lucide="shield-alert" style="width: 16px; height: 16px;"></i>
                <span>Log Incident</span>
            </button>
        </div>
    </div>
</div>

@if($currentExam)
    <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="award" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 15px; font-weight: 700; color: #fff;">{{ $currentExam->title }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Category: <strong style="color: #cbd5e1;">{{ $currentExam->category }}</strong> &bull;
                    Duration: <strong style="color: #cbd5e1;">{{ $currentExam->duration_minutes }} mins</strong> &bull;
                    Max Marks: <strong style="color: #cbd5e1;">{{ $currentExam->total_marks }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <span class="status-pill {{ strtolower($currentExam->status) }}">{{ $currentExam->status }}</span>
            <span style="font-size: 13px; color: var(--text-muted);">
                Auto-Save Heartbeat: <strong style="color: #34d399;">5-sec sync</strong>
            </span>
        </div>
    </div>
@endif

<!-- Live Proctoring Dual-Pane Grid -->
<div class="monitor-grid">
    <!-- Left Pane: Real-Time Violations Stream -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div>
                <div class="card-title">
                    <i data-lucide="shield-alert" style="color: #ef4444; width: 22px; height: 22px;"></i>
                    <span>Live Violation & Threat Stream</span>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                    AI audio/video telemetry, tab focus losses, face detection triggers
                </p>
            </div>
            <span id="violationCountBadge" style="font-size: 12px; font-weight: 700; background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 4px 10px; border-radius: 999px;">
                {{ $violations->count() }} Incidents
            </span>
        </div>

        <!-- Violation Filter Tabs -->
        <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
            <button class="filter-tab-btn active" onclick="filterViolations('ALL', this)">All</button>
            <button class="filter-tab-btn" onclick="filterViolations('TAB_SWITCH', this)">Tab Focus</button>
            <button class="filter-tab-btn" onclick="filterViolations('MULTIPLE_FACES', this)">Multiple Faces</button>
            <button class="filter-tab-btn" onclick="filterViolations('VOICE', this)">Audio/Voice</button>
            <button class="filter-tab-btn" onclick="filterViolations('MANUAL', this)">Proctor Flag</button>
        </div>

        <!-- Telemetry Feed Container -->
        <div class="telemetry-feed" id="violationsFeed">
            @forelse($violations as $v)
                <div class="telemetry-card" data-type="{{ strtoupper($v->violation_type) }}">
                    <div style="display: flex; gap: 12px; flex: 1;">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(239, 68, 68, 0.2); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                            <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 13px; font-weight: 700; color: #fff;">{{ $v->violation_type }}</span>
                                <span class="mono" style="font-size: 11px; color: var(--text-muted);">
                                    {{ \Carbon\Carbon::parse($v->timestamp)->format('H:i:s') }}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: #cbd5e1; margin-top: 2px;">
                                <strong>Candidate:</strong> {{ $v->candidate ? $v->candidate->full_name : $v->candidate_id }} ({{ $v->candidate_id }})
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; background: rgba(0,0,0,0.25); padding: 6px 10px; border-radius: 6px; font-family: monospace;">
                                {{ $v->details }}
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('api.monitoring.dismissViolation', $v->id) }}" method="POST" onsubmit="return confirm('Dismiss this violation log?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="logout-btn" title="Dismiss incident" style="color: #64748b;">
                            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div id="noViolationsState" style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                    <i data-lucide="check-circle-2" style="width: 36px; height: 36px; color: #10b981; margin-bottom: 8px;"></i>
                    <p style="font-size: 14px; font-weight: 600; color: #fff;">No violations detected</p>
                    <p style="font-size: 12px; margin-top: 4px;">Candidate telemetry is streaming clear with no anomalies.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Pane: Real-Time Candidate Drafts (5-Sec Sync) -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div>
                <div class="card-title">
                    <i data-lucide="cpu" style="color: #6366f1; width: 22px; height: 22px;"></i>
                    <span>Real-Time Candidate Drafts</span>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                    5-second auto-save sync & live response inspector
                </p>
            </div>
            <span id="draftsCountBadge" style="font-size: 12px; font-weight: 700; background: rgba(99, 102, 241, 0.2); color: #818cf8; padding: 4px 10px; border-radius: 999px;">
                {{ $drafts->count() }} Live Drafts
            </span>
        </div>

        <div class="telemetry-feed" id="draftsFeed">
            @forelse($drafts as $draft)
                @php
                    $answers = is_array($draft->answers_json) ? $draft->answers_json : (json_decode($draft->answers_json, true) ?? []);
                    $answeredCount = is_array($answers) ? count($answers) : 0;
                @endphp
                <div class="draft-card" onclick="inspectDraft({{ json_encode($draft) }}, {{ json_encode($draft->candidate) }})">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                                {{ substr($draft->candidate->full_name ?? 'C', 0, 1) }}
                            </div>
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #fff;">
                                    {{ $draft->candidate ? $draft->candidate->full_name : $draft->candidate_id }}
                                </div>
                                <div class="mono" style="font-size: 11px; color: var(--text-muted);">
                                    {{ $draft->candidate_id }}
                                </div>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <span class="status-pill active" style="font-size: 10px; padding: 2px 8px;">
                                SYNCED
                            </span>
                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                                {{ \Carbon\Carbon::parse($draft->last_saved_at)->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <div style="background: rgba(0, 0, 0, 0.2); padding: 8px 12px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
                        <span style="color: var(--text-muted);">Questions Answered:</span>
                        <strong style="color: #34d399;">{{ $answeredCount }} Attempted</strong>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                    <i data-lucide="cloud-off" style="width: 36px; height: 36px; margin-bottom: 8px;"></i>
                    <p style="font-size: 14px; font-weight: 600; color: #fff;">No active draft streams</p>
                    <p style="font-size: 12px; margin-top: 4px;">When students answer questions, drafts auto-sync here every 5 seconds.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal: Log Manual Proctor Violation -->
<div class="modal-overlay" id="logViolationModal">
    <div class="modal-box">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="alert-octagon" style="color: #ef4444; width: 20px; height: 20px;"></i>
                <span>Log Proctoring Incident</span>
            </h3>
            <button type="button" onclick="closeLogModal()" class="logout-btn">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form action="{{ route('api.monitoring.logViolation') }}" method="POST">
            @csrf
            <input type="hidden" name="exam_code" value="{{ $selectedExamCode }}">

            <div class="form-group">
                <label class="form-label">Select Candidate</label>
                <select name="candidate_id" class="form-control" required>
                    @foreach($candidateUsers as $cand)
                        <option value="{{ $cand->id }}">
                            {{ $cand->full_name }} ({{ $cand->id }} / {{ $cand->student_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Violation / Warning Type</label>
                <select name="violation_type" class="form-control" required>
                    <option value="SUSPICIOUS_TAB_SWITCH">Suspicious Tab / Window Switch</option>
                    <option value="MULTIPLE_PERSONS_DETECTED">Multiple Persons in Frame</option>
                    <option value="LOOKING_AWAY_EXCESSIVE">Excessive Gaze Deviation</option>
                    <option value="UNAUTHORIZED_AUDIO">Unauthorized Audio / Voice Detected</option>
                    <option value="PROCTOR_MANUAL_WARNING">Proctor Manual Warning</option>
                    <option value="EXAM_INVALIDATION_FLAG">Exam Invalidation Flag</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Incident Details & Proctor Notes</label>
                <textarea name="details" class="form-control" placeholder="Describe the violation, exact timestamp, or reason for proctor intervention..." required></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;">
                <button type="button" onclick="closeLogModal()" class="quick-action-btn secondary">Cancel</button>
                <button type="submit" class="quick-action-btn danger">Log Violation & Flag</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Inspect Candidate Draft in Real-Time -->
<div class="modal-overlay" id="draftInspectModal">
    <div class="modal-box" style="max-width: 680px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <span id="inspectModalAvatar">C</span>
                </div>
                <div>
                    <h3 id="inspectModalName" style="font-size: 17px; font-weight: 700; color: #fff;">Candidate Draft Inspector</h3>
                    <div id="inspectModalSub" style="font-size: 12px; color: var(--text-muted);">Live Heartbeat Answers</div>
                </div>
            </div>
            <button type="button" onclick="closeInspectModal()" class="logout-btn">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; max-height: 400px; overflow-y: auto;">
            <div style="font-size: 13px; font-weight: 700; color: #818cf8; margin-bottom: 12px;">
                In-Flight Responses (Synced via 5s Auto-Save):
            </div>
            <div id="inspectModalAnswers" style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Answers injected via JS -->
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeInspectModal()" class="quick-action-btn secondary">Close Inspector</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const examCode = "{{ $selectedExamCode }}";

    function openLogModal() {
        document.getElementById('logViolationModal').style.display = 'flex';
    }

    function closeLogModal() {
        document.getElementById('logViolationModal').style.display = 'none';
    }

    function openInspectModal() {
        document.getElementById('draftInspectModal').style.display = 'flex';
    }

    function closeInspectModal() {
        document.getElementById('draftInspectModal').style.display = 'none';
    }

    function inspectDraft(draft, candidate) {
        document.getElementById('inspectModalName').innerText = candidate ? candidate.full_name : draft.candidate_id;
        document.getElementById('inspectModalSub').innerText = 'Candidate ID: ' + draft.candidate_id + ' | Last Synced: ' + draft.last_saved_at;
        document.getElementById('inspectModalAvatar').innerText = (candidate && candidate.full_name) ? candidate.full_name.charAt(0) : 'C';

        let answers = draft.answers_json;
        if (typeof answers === 'string') {
            try { answers = JSON.parse(answers); } catch(e) { answers = {}; }
        }

        const container = document.getElementById('inspectModalAnswers');
        container.innerHTML = '';

        if (!answers || Object.keys(answers).length === 0) {
            container.innerHTML = '<div style="color: var(--text-muted); font-size: 13px;">No responses saved yet in draft.</div>';
        } else {
            for (let qKey in answers) {
                const ans = answers[qKey];
                const card = document.createElement('div');
                card.style.background = 'rgba(255, 255, 255, 0.03)';
                card.style.border = '1px solid rgba(255, 255, 255, 0.08)';
                card.style.borderRadius = '8px';
                card.style.padding = '10px 14px';

                let formattedAns = typeof ans === 'object' ? JSON.stringify(ans, null, 2) : String(ans);

                card.innerHTML = `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <strong style="color: #fff; font-size: 13px;">Question Key / Number: ${qKey}</strong>
                    </div>
                    <pre style="color: #38bdf8; font-size: 12px; white-space: pre-wrap; word-break: break-all; margin-top: 4px; font-family: 'JetBrains Mono', monospace;">${formattedAns}</pre>
                `;
                container.appendChild(card);
            }
        }

        openInspectModal();
    }

    function filterViolations(type, btn) {
        document.querySelectorAll('.filter-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const cards = document.querySelectorAll('.telemetry-card');
        cards.forEach(card => {
            if (type === 'ALL') {
                card.style.display = 'flex';
            } else {
                const cardType = card.getAttribute('data-type');
                if (cardType && cardType.includes(type)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    }

    // Auto-polling for real-time violations every 4 seconds
    setInterval(async () => {
        if (!examCode) return;
        try {
            const res = await fetch(`/api/monitoring/violations?exam_code=${encodeURIComponent(examCode)}`);
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('violationCountBadge').innerText = data.count + ' Incidents';
            }
        } catch (e) {
            console.warn('Polling error:', e);
        }
    }, 4000);
</script>
@endsection
