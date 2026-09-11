@extends('layouts.admin')

@section('title', 'Edit Exam: ' . $exam->exam_code)
@section('breadcrumb', 'Edit Exam')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('exams.show', $exam->exam_code) }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Exam Overview</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Edit Examination Parameters</h1>
        <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Update assessment schedule, marks distribution, and status configuration</p>
    </div>

    <div class="glass-card">
        <form action="{{ route('exams.update', $exam->exam_code) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Exam Code (Readonly)</label>
                    <input type="text" class="form-control mono" value="{{ $exam->exam_code }}" readonly disabled style="background: #f1f5f9; color: #64748b;">
                </div>

                <div class="form-group">
                    <label class="form-label">Category / Stream *</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $exam->category) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Examination Title *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $exam->title) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Instructions</label>
                <textarea name="description" class="form-control">{{ old('description', $exam->description) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" class="form-control" min="1" max="600" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Marks *</label>
                    <input type="number" name="total_marks" class="form-control" min="1" value="{{ old('total_marks', $exam->total_marks) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="ACTIVE" {{ $exam->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (Live Now)</option>
                        <option value="UPCOMING" {{ $exam->status === 'UPCOMING' ? 'selected' : '' }}>UPCOMING (Scheduled)</option>
                        <option value="COMPLETED" {{ $exam->status === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Archived)</option>
                    </select>
                </div>
            </div>

            <!-- Exam Date & Time Interactive Pickers -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 20px;">
                <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <span>📅</span>
                    <span>Examination Schedule & Timing</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Date Selection -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Select Exam Date *</span>
                            <span id="date-preview-badge" style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #ede9fe; padding: 2px 8px; border-radius: 6px;">{{ old('exam_date', $exam->exam_date) }}</span>
                        </label>
                        <input type="date" id="picker_exam_date" class="form-control" value="{{ date('Y-m-d', strtotime(old('exam_date', $exam->exam_date ?? now()))) }}" required oninput="syncExamDate()">
                        <input type="hidden" name="exam_date" id="hidden_exam_date" value="{{ old('exam_date', $exam->exam_date) }}">
                        
                        <!-- Quick Date Presets -->
                        <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                            <button type="button" class="btn-preset" onclick="setQuickDate(0)">Today</button>
                            <button type="button" class="btn-preset" onclick="setQuickDate(1)">Tomorrow</button>
                            <button type="button" class="btn-preset" onclick="setQuickDate(7)">+1 Week</button>
                            <button type="button" class="btn-preset" onclick="setQuickDate(30)">+1 Month</button>
                        </div>
                    </div>

                    <!-- Time Window Selection -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Exam Time Window *</span>
                            <span id="time-preview-badge" style="font-size: 11px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">{{ old('exam_time', $exam->exam_time ?? '10:00 AM - 12:00 PM') }}</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="time" id="picker_time_start" class="form-control" value="10:00" required oninput="syncExamTime()">
                            <span style="color: #94a3b8; font-weight: 700;">to</span>
                            <input type="time" id="picker_time_end" class="form-control" value="12:00" required oninput="syncExamTime()">
                        </div>
                        <input type="hidden" name="exam_time" id="hidden_exam_time" value="{{ old('exam_time', $exam->exam_time ?? '10:00 AM - 12:00 PM') }}">
                        
                        <!-- Quick Time Slot Presets -->
                        <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                            <button type="button" class="btn-preset" onclick="setQuickTime('10:00', '12:00')">Morning (10-12)</button>
                            <button type="button" class="btn-preset" onclick="setQuickTime('14:00', '16:00')">Afternoon (2-4)</button>
                            <button type="button" class="btn-preset" onclick="setQuickTime('17:00', '19:00')">Evening (5-7)</button>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .btn-preset {
                    background: #ffffff;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                    padding: 3px 8px;
                    font-size: 11px;
                    font-weight: 600;
                    color: #475569;
                    cursor: pointer;
                    transition: all 0.15s;
                }
                .btn-preset:hover {
                    background: #ede9fe;
                    color: #4f46e5;
                    border-color: #c7d2fe;
                }
            </style>

            <script>
                function formatReadableDate(dateObj) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const month = months[dateObj.getMonth()];
                    const year = dateObj.getFullYear();
                    return `${day} ${month} ${year}`;
                }

                function formatTime12(time24Str) {
                    if (!time24Str) return '10:00 AM';
                    const parts = time24Str.split(':');
                    let h = parseInt(parts[0], 10);
                    const m = parts[1] ? parts[1] : '00';
                    const meridian = h >= 12 ? 'PM' : 'AM';
                    h = h % 12;
                    if (h === 0) h = 12;
                    const hStr = String(h).padStart(2, '0');
                    return `${hStr}:${m} ${meridian}`;
                }

                function syncExamDate() {
                    const dateVal = document.getElementById('picker_exam_date').value;
                    if (!dateVal) return;
                    const parts = dateVal.split('-');
                    const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                    const formatted = formatReadableDate(d);
                    document.getElementById('hidden_exam_date').value = formatted;
                    document.getElementById('date-preview-badge').textContent = formatted;
                }

                function syncExamTime() {
                    const startVal = document.getElementById('picker_time_start').value;
                    const endVal = document.getElementById('picker_time_end').value;
                    const formatted = `${formatTime12(startVal)} - ${formatTime12(endVal)}`;
                    document.getElementById('hidden_exam_time').value = formatted;
                    document.getElementById('time-preview-badge').textContent = formatted;

                    // Calculate duration in minutes
                    if (startVal && endVal) {
                        const sParts = startVal.split(':').map(Number);
                        const eParts = endVal.split(':').map(Number);
                        let diffMins = (eParts[0] * 60 + eParts[1]) - (sParts[0] * 60 + sParts[1]);
                        if (diffMins > 0) {
                            const durationInput = document.querySelector('input[name="duration_minutes"]');
                            if (durationInput) durationInput.value = diffMins;
                        }
                    }
                }

                function setQuickDate(daysFromNow) {
                    const d = new Date();
                    d.setDate(d.getDate() + daysFromNow);
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    document.getElementById('picker_exam_date').value = `${yyyy}-${mm}-${dd}`;
                    syncExamDate();
                }

                function setQuickTime(startTime, endTime) {
                    document.getElementById('picker_time_start').value = startTime;
                    document.getElementById('picker_time_end').value = endTime;
                    syncExamTime();
                }

                // Initial parse from existing time window if present
                (function parseInitialTime() {
                    const existingTime = "{{ $exam->exam_time }}";
                    if (existingTime && existingTime.includes('-')) {
                        const parts = existingTime.split('-');
                        function to24(tStr, def) {
                            const m = tStr.trim().match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
                            if (!m) return def;
                            let h = parseInt(m[1], 10);
                            const min = m[2];
                            const mer = m[3] ? m[3].toUpperCase() : '';
                            if (mer === 'PM' && h < 12) h += 12;
                            if (mer === 'AM' && h === 12) h = 0;
                            return `${String(h).padStart(2, '0')}:${min}`;
                        }
                        document.getElementById('picker_time_start').value = to24(parts[0], '10:00');
                        document.getElementById('picker_time_end').value = to24(parts[1], '12:00');
                    }
                })();

                // Initial sync on load
                syncExamDate();
                syncExamTime();
            </script>

            <div class="form-group" style="margin-top: 10px; background: rgba(248, 250, 252, 0.8); padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                    <input type="checkbox" name="is_results_published" value="1" {{ $exam->is_results_published ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #4f46e5;">
                    <span style="font-size: 13.5px; font-weight: 600; color: #0f172a;">Publish Results to Candidates</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('exams.show', $exam->exam_code) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
