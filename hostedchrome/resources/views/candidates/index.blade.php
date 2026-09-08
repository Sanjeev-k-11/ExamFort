@extends('layouts.admin')

@section('title', 'Student & Candidate Directory')
@section('breadcrumb', 'Students & Candidates')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Student & Candidate Roster</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Manage enrolled students, academic streams, multi-subject faculty assignments, and performance analytics.
            </p>
        </div>
        <a href="{{ route('candidates.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);">
            <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
            <span>Register Student</span>
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('candidates.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: var(--text-muted);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search by student name, roll number, college, email..." value="{{ request('search') }}">
        </div>

        <button type="submit" class="quick-action-btn secondary">Filter</button>
        @if(request('search'))
            <a href="{{ route('candidates.index') }}" class="quick-action-btn secondary" style="color: #dc2626;">Clear</a>
        @endif
    </form>
</div>

<!-- Candidates Table -->
<div class="glass-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Student Name & ID</th>
                    <th>College & Batch</th>
                    <th>Course & Stream</th>
                    <th>Assigned Subject Faculty</th>
                    <th>Average Score</th>
                    <th>Exams Done</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #ec4899); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);">
                                    {{ substr($user->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $user->full_name }}</div>
                                    <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $user->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 13.5px; color: #1e293b; font-weight: 600;">{{ $user->college_name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">Batch: {{ $user->batch_years }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155;">{{ $user->course }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $user->stream }}</div>
                        </td>
                        <td>
                            @if($user->subjectTeachers->count() > 0)
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    @foreach($user->subjectTeachers as $map)
                                        <div style="font-size: 11.5px; display: inline-flex; align-items: center; gap: 4px;">
                                            <span style="color: #4f46e5; font-weight: 700;">{{ $map->subject_name }}:</span>
                                            <span style="color: #334155; font-weight: 600;">{{ $map->teacher->full_name ?? 'Faculty' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size: 11.5px; color: var(--text-muted);">No subject faculty mapped</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $scoreVal = (float)$user->average_score;
                                $scoreColor = $scoreVal >= 75 ? '#059669' : ($scoreVal >= 50 ? '#d97706' : '#dc2626');
                            @endphp
                            <strong style="font-size: 16px; color: {{ $scoreColor }}; font-family: 'Outfit', sans-serif;">
                                {{ number_format($scoreVal, 1) }}%
                            </strong>
                        </td>
                        <td>
                            <span style="font-weight: 800; color: #0f172a;">{{ $user->exams_completed }}</span>
                            <span style="font-size: 11.5px; color: var(--text-muted);"> / {{ $user->exams_enrolled }}</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('candidates.show', $user->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 5px 10px;">
                                    <i data-lucide="eye" style="width: 13px; height: 13px; color: #4f46e5;"></i>
                                    <span>Profile & Faculty</span>
                                </a>
                                <a href="{{ route('candidates.edit', $user->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 5px 10px;">
                                    <i data-lucide="edit" style="width: 13px; height: 13px;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                            No student records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
