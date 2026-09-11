@extends('layouts.admin')

@section('title', 'Examination Manager')
@section('breadcrumb', 'Exams Directory')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Assessments & Examinations</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
                Manage scheduled tests, question banks, active sessions, and results publication.
            </p>
        </div>
        <a href="{{ route('exams.create') }}" class="btn btn-primary">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
            <span>Schedule New Exam</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('exams.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: #94a3b8;"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search exam title, code or category..." value="{{ request('search') }}">
        </div>

        <select name="status" class="form-control" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (Live)</option>
            <option value="UPCOMING" {{ request('status') === 'UPCOMING' ? 'selected' : '' }}>UPCOMING</option>
            <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('exams.index') }}" class="btn btn-secondary" style="color: #dc2626; border-color: #fecaca;">Reset</a>
        @endif
    </form>
</div>

<!-- Exams Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
    @forelse($exams as $exam)
        <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <span class="mono" style="font-size: 12px; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px; border: 1px solid #e0e7ff;">{{ $exam->exam_code }}</span>
                        <h3 style="font-size: 17px; font-weight: 700; color: #0f172a; margin-top: 8px;">{{ $exam->title }}</h3>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Category: {{ $exam->category }}</div>
                    </div>
                    <form action="{{ route('exams.toggleStatus', $exam->exam_code) }}" method="POST">
                        @csrf
                        <button type="submit" class="status-pill {{ strtolower($exam->status) }}" style="cursor: pointer; border: none;" title="Click to cycle status">
                            {{ $exam->status }}
                        </button>
                    </form>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin: 16px 0; background: rgba(248, 250, 252, 0.95); padding: 8px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                    <a href="{{ route('exams.show', $exam->exam_code) }}#questions-section" style="text-decoration: none; color: inherit; display: block; padding: 6px 4px; border-radius: 8px; transition: all 0.2s ease;" onmouseover="this.style.background='#eef2ff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='transparent'; this.style.transform='none';" title="View Question Bank">
                        <div style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;">Questions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $exam->questions_count }}</div>
                    </a>
                    <a href="{{ route('exams.show', $exam->exam_code) }}" style="text-decoration: none; color: inherit; display: block; padding: 6px 4px; border-radius: 8px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; transition: all 0.2s ease;" onmouseover="this.style.background='#ecfdf5'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='transparent'; this.style.transform='none';" title="View Exam Overview">
                        <div style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;">Duration</div>
                        <div style="font-size: 16px; font-weight: 800; color: #4f46e5; margin-top: 2px;">{{ $exam->duration_minutes }}m</div>
                    </a>
                    <a href="{{ route('exams.show', $exam->exam_code) }}#submissions-section" style="text-decoration: none; color: inherit; display: block; padding: 6px 4px; border-radius: 8px; transition: all 0.2s ease;" onmouseover="this.style.background='#fffbeb'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='transparent'; this.style.transform='none';" title="View Submissions & Grading">
                        <div style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;">Submissions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #059669; margin-top: 2px;">{{ $exam->submissions_count }}</div>
                    </a>
                </div>

                <div style="font-size: 12px; color: #475569; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748b;"></i>
                    <span>Schedule: <strong style="color: #0f172a;">{{ $exam->exam_date }}</strong> ({{ $exam->exam_time }})</span>
                </div>
            </div>

            <div style="display: flex; gap: 6px; padding-top: 14px; border-top: 1px solid rgba(226, 232, 240, 0.9);">
                <button type="button" class="btn btn-secondary" onclick="openIndexRescheduleModal('{{ $exam->exam_code }}', '{{ addslashes($exam->title) }}', '{{ $exam->exam_date }}', '{{ $exam->exam_time }}', {{ $exam->duration_minutes }}, '{{ $exam->status }}')" style="padding: 8px 10px; font-size: 12px; background: #ffffff; border-color: #cbd5e1;" title="Reschedule Date & Time">
                    <i data-lucide="calendar-clock" style="width: 14px; height: 14px; color: #4f46e5;"></i>
                    <span>Reschedule</span>
                </button>
                <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="btn btn-secondary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="radio" style="width: 14px; height: 14px; color: #059669;"></i>
                    <span>Radar</span>
                </a>
                <a href="{{ route('exams.show', $exam->exam_code) }}" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                    <span>Manage</span>
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 48px; color: #64748b; background: rgba(255, 255, 255, 0.85); border-radius: 16px; border: 1px dashed #cbd5e1;">
            <i data-lucide="file-x" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
            <p style="font-size: 16px; font-weight: 700; color: #0f172a;">No examinations found</p>
            <p style="font-size: 13px; margin-top: 4px;">Click "Schedule New Exam" to create an assessment.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 24px;">
    {{ $exams->links() }}
</div>

<!-- Dynamic Reschedule Modal Dialog for Exam Directory -->
<div id="indexRescheduleModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; width: 100%; max-width: 540px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
        <!-- Modal Header -->
        <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    📅
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">Reschedule Examination</h3>
                    <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Exam Code: <span id="idx_modal_code_display" class="mono" style="font-weight: 700; color: #4f46e5;"></span></p>
                </div>
            </div>
            <button type="button" onclick="closeIndexRescheduleModal()" style="background: transparent; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px; border-radius: 6px;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
        </div>

        <!-- Modal Form -->
        <form id="idx_reschedule_form" action="" method="POST" style="padding: 24px;">
            @csrf

            <!-- Date Selection -->
            <div style="margin-bottom: 18px;">
                <label style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    <span>Select New Exam Date *</span>
                    <span id="idx-modal-date-badge" style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #ede9fe; padding: 2px 8px; border-radius: 6px;"></span>
                </label>
                <input type="date" id="idx_modal_picker_date" class="form-control" required oninput="syncIdxModalDate()" style="padding: 10px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; width: 100%;">
                <input type="hidden" name="exam_date" id="idx_modal_hidden_date" value="">
                
                <!-- Quick Date Presets -->
                <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickDate(0)">Today</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickDate(1)">Tomorrow</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickDate(2)">+2 Days</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickDate(7)">+1 Week</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickDate(14)">+2 Weeks</button>
                </div>
            </div>

            <!-- Time Slot Selection -->
            <div style="margin-bottom: 18px;">
                <label style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    <span>Exam Time Slot *</span>
                    <span id="idx-modal-time-badge" style="font-size: 11px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;"></span>
                </label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="time" id="idx_modal_picker_start" class="form-control" value="10:00" required oninput="syncIdxModalTime()" style="padding: 10px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; flex: 1;">
                    <span style="color: #94a3b8; font-weight: 800; font-size: 12px; text-transform: uppercase;">TO</span>
                    <input type="time" id="idx_modal_picker_end" class="form-control" value="12:00" required oninput="syncIdxModalTime()" style="padding: 10px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; flex: 1;">
                </div>
                <input type="hidden" name="exam_time" id="idx_modal_hidden_time" value="">

                <!-- Quick Time Slot Presets -->
                <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickTime('09:00', '11:00')">Morning (9 - 11 AM)</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickTime('10:00', '13:00')">Morning (10 AM - 1 PM)</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickTime('14:00', '17:00')">Afternoon (2 - 5 PM)</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setIdxModalQuickTime('18:00', '21:00')">Evening (6 - 9 PM)</button>
                </div>
            </div>

            <!-- Duration and Schedule Status -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px;">
                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Duration (Minutes)</label>
                    <input type="number" name="duration_minutes" id="idx_modal_duration_input" value="120" min="1" max="600" class="form-control" style="padding: 9px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; width: 100%;">
                </div>
                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Exam Status</label>
                    <select name="status" id="idx_modal_status_select" class="form-control" style="padding: 9px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; width: 100%;">
                        <option value="UPCOMING">UPCOMING (Scheduled)</option>
                        <option value="ACTIVE">ACTIVE (Live Now)</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeIndexRescheduleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700; background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Confirm Reschedule</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes modalPop {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .btn-reschedule-preset {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 9px;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-reschedule-preset:hover {
        background: #ede9fe;
        color: #4f46e5;
        border-color: #c7d2fe;
    }
</style>

<script>
    function openIndexRescheduleModal(examCode, title, examDate, examTime, duration, status) {
        const m = document.getElementById('indexRescheduleModal');
        if (!m) return;
        
        document.getElementById('idx_modal_code_display').textContent = examCode;
        document.getElementById('idx_reschedule_form').action = '/exams/' + encodeURIComponent(examCode) + '/reschedule';
        
        let dVal = examDate;
        try {
            const parsed = new Date(examDate);
            if (!isNaN(parsed.getTime())) {
                dVal = parsed.toISOString().split('T')[0];
            }
        } catch (_) {}
        
        document.getElementById('idx_modal_picker_date').value = dVal || new Date().toISOString().split('T')[0];
        document.getElementById('idx_modal_duration_input').value = duration || 120;
        
        const sel = document.getElementById('idx_modal_status_select');
        if (sel) sel.value = (status === 'COMPLETED' ? 'UPCOMING' : (status || 'UPCOMING'));

        if (examTime && examTime.includes('-')) {
            const parts = examTime.split('-');
            function to24(tStr, def) {
                const match = tStr.trim().match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
                if (!match) return def;
                let h = parseInt(match[1], 10);
                const min = match[2];
                const mer = match[3] ? match[3].toUpperCase() : '';
                if (mer === 'PM' && h < 12) h += 12;
                if (mer === 'AM' && h === 12) h = 0;
                return `${String(h).padStart(2, '0')}:${min}`;
            }
            document.getElementById('idx_modal_picker_start').value = to24(parts[0], '10:00');
            document.getElementById('idx_modal_picker_end').value = to24(parts[1], '12:00');
        } else {
            document.getElementById('idx_modal_picker_start').value = '10:00';
            document.getElementById('idx_modal_picker_end').value = '12:00';
        }

        m.style.display = 'flex';
        syncIdxModalDate();
        syncIdxModalTime();
        if (window.lucide) lucide.createIcons();
    }

    function closeIndexRescheduleModal() {
        const m = document.getElementById('indexRescheduleModal');
        if (m) m.style.display = 'none';
    }

    function syncIdxModalDate() {
        const dateVal = document.getElementById('idx_modal_picker_date').value;
        if (!dateVal) return;
        const parts = dateVal.split('-');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        const formatted = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
        document.getElementById('idx_modal_hidden_date').value = formatted;
        document.getElementById('idx-modal-date-badge').textContent = formatted;
    }

    function syncIdxModalTime() {
        const startVal = document.getElementById('idx_modal_picker_start').value;
        const endVal = document.getElementById('idx_modal_picker_end').value;
        if (!startVal || !endVal) return;
        
        function format12(time24) {
            const p = time24.split(':');
            let h = parseInt(p[0], 10);
            const m = p[1] || '00';
            const mer = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            if (h === 0) h = 12;
            return `${String(h).padStart(2, '0')}:${m} ${meridian = mer}`;
        }

        const formatted = `${format12(startVal)} - ${format12(endVal)}`;
        document.getElementById('idx_modal_hidden_time').value = formatted;
        document.getElementById('idx-modal-time-badge').textContent = formatted;

        const sParts = startVal.split(':').map(Number);
        const eParts = endVal.split(':').map(Number);
        let diffMins = (eParts[0] * 60 + eParts[1]) - (sParts[0] * 60 + sParts[1]);
        if (diffMins > 0) {
            document.getElementById('idx_modal_duration_input').value = diffMins;
        }
    }

    function setIdxModalQuickDate(daysFromNow) {
        const d = new Date();
        d.setDate(d.getDate() + daysFromNow);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('idx_modal_picker_date').value = `${yyyy}-${mm}-${dd}`;
        syncIdxModalDate();
    }

    function setIdxModalQuickTime(startTime, endTime) {
        document.getElementById('idx_modal_picker_start').value = startTime;
        document.getElementById('idx_modal_picker_end').value = endTime;
        syncIdxModalTime();
    }
</script>
@endsection
