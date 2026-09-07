@extends('layouts.admin')

@section('title', 'Examination Manager')
@section('breadcrumb', 'Exams Directory')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Assessments & Examinations</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Manage scheduled tests, question banks, active sessions, and results publication.
            </p>
        </div>
        <a href="{{ route('exams.create') }}" class="quick-action-btn">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
            <span>Schedule New Exam</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('exams.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: var(--text-muted);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search exam title, code or category..." value="{{ request('search') }}">
        </div>

        <select name="status" class="form-control" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (Live)</option>
            <option value="UPCOMING" {{ request('status') === 'UPCOMING' ? 'selected' : '' }}>UPCOMING</option>
            <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
        </select>

        <button type="submit" class="quick-action-btn secondary">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('exams.index') }}" class="quick-action-btn secondary" style="color: #f87171;">Reset</a>
        @endif
    </form>
</div>

<!-- Exams Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
    @forelse($exams as $exam)
        <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <span class="mono" style="font-size: 12px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 3px 8px; border-radius: 4px;">{{ $exam->exam_code }}</span>
                        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-top: 8px;">{{ $exam->title }}</h3>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Category: {{ $exam->category }}</div>
                    </div>
                    <form action="{{ route('exams.toggleStatus', $exam->exam_code) }}" method="POST">
                        @csrf
                        <button type="submit" class="status-pill {{ strtolower($exam->status) }}" style="cursor: pointer; border: none;" title="Click to cycle status">
                            {{ $exam->status }}
                        </button>
                    </form>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 16px 0; background: rgba(0, 0, 0, 0.2); padding: 12px; border-radius: 8px; text-align: center;">
                    <div>
                        <div style="font-size: 11px; color: var(--text-muted);">Questions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #fff;">{{ $exam->questions_count }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-muted);">Duration</div>
                        <div style="font-size: 16px; font-weight: 800; color: #818cf8;">{{ $exam->duration_minutes }}m</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-muted);">Submissions</div>
                        <div style="font-size: 16px; font-weight: 800; color: #34d399;">{{ $exam->submissions_count }}</div>
                    </div>
                </div>

                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                    <span>Schedule: <strong>{{ $exam->exam_date }}</strong> ({{ $exam->exam_time }})</span>
                </div>
            </div>

            <div style="display: flex; gap: 8px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn secondary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="radio" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Live Radar</span>
                </a>
                <a href="{{ route('exams.show', $exam->exam_code) }}" class="quick-action-btn" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                    <span>Manage Exam</span>
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 48px; color: var(--text-muted);">
            <i data-lucide="file-x" style="width: 36px; height: 36px; margin-bottom: 8px;"></i>
            <p style="font-size: 15px; font-weight: 600; color: #fff;">No examinations found</p>
            <p style="font-size: 13px; margin-top: 4px;">Click "Schedule New Exam" to create an assessment.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 24px;">
    {{ $exams->links() }}
</div>
@endsection
