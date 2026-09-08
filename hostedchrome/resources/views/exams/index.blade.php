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

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 16px 0; background: rgba(248, 250, 252, 0.9); padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                    <div>
                        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Questions</div>
                        <div style="font-size: 17px; font-weight: 800; color: #0f172a;">{{ $exam->questions_count }}</div>
                    </div>
                    <div style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Duration</div>
                        <div style="font-size: 17px; font-weight: 800; color: #4f46e5;">{{ $exam->duration_minutes }}m</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Submissions</div>
                        <div style="font-size: 17px; font-weight: 800; color: #059669;">{{ $exam->submissions_count }}</div>
                    </div>
                </div>

                <div style="font-size: 12px; color: #475569; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748b;"></i>
                    <span>Schedule: <strong style="color: #0f172a;">{{ $exam->exam_date }}</strong> ({{ $exam->exam_time }})</span>
                </div>
            </div>

            <div style="display: flex; gap: 8px; padding-top: 14px; border-top: 1px solid rgba(226, 232, 240, 0.9);">
                <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="btn btn-secondary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="radio" style="width: 14px; height: 14px; color: #059669;"></i>
                    <span>Live Radar</span>
                </a>
                <a href="{{ route('exams.show', $exam->exam_code) }}" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                    <span>Manage Exam</span>
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
@endsection
