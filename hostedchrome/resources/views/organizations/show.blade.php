@extends('layouts.admin')

@section('title', 'Organization: ' . $organization->name)
@section('breadcrumb', 'Org: ' . $organization->code)

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('organizations.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Organizations Directory</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="mono" style="font-size: 14px; font-weight: 800; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 4px 10px; border-radius: 6px;">{{ $organization->code }}</span>
                <span class="status-pill {{ $organization->status === 'ACTIVE' ? 'active' : 'danger' }}">{{ $organization->status }}</span>
                <span style="font-size: 13px; color: var(--text-muted);">{{ $organization->type }}</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 800; color: #fff; margin-top: 8px;">{{ $organization->name }}</h1>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('principals.create', ['org_id' => $organization->id]) }}" class="quick-action-btn">
                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                <span>Appoint Principal / Dean</span>
            </a>
            <a href="{{ route('organizations.edit', $organization->id) }}" class="quick-action-btn secondary">
                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                <span>Edit Quotas & Details</span>
            </a>
        </div>
    </div>
</div>

<!-- Quota Metrics Cards -->
<div class="metrics-grid" style="margin-bottom: 32px;">
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #818cf8;">
        <div class="metric-icon-box">
            <i data-lucide="calendar" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $organization->exams->count() }} / {{ $organization->max_exams_allowed }}</div>
            <div class="metric-label">Annual Exams Quota (Yearly Cap)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #34d399;">
        <div class="metric-icon-box" style="color: #34d399;">
            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $organization->teachers->count() }} / {{ $organization->max_teachers_allowed }}</div>
            <div class="metric-label">Faculty Accounts Capacity</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #fbbf24;">
        <div class="metric-icon-box" style="color: #fbbf24;">
            <i data-lucide="graduation-cap" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $organization->students->count() }} / {{ $organization->max_students_allowed }}</div>
            <div class="metric-label">Student Capacity Limit</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ec4899, #8b5cf6); --accent-color: #f472b6;">
        <div class="metric-icon-box" style="color: #f472b6;">
            <i data-lucide="crown" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $organization->principals->count() }}</div>
            <div class="metric-label">Deans & Principals Appointed</div>
        </div>
    </div>
</div>

<!-- Appointed Principals & Deans Section -->
<div class="glass-card" style="margin-bottom: 32px;">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="crown" style="color: #fbbf24; width: 22px; height: 22px;"></i>
            <span>Institutional Principals & Deans ({{ $organization->principals->count() }})</span>
        </div>
        <a href="{{ route('principals.create', ['org_id' => $organization->id]) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">+ Appoint Dean</a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Dean / Principal Name</th>
                    <th>Email & Contact</th>
                    <th>Annual Exam Quota</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organization->principals as $p)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #fff;">{{ $p->full_name }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $p->designation ?? 'Dean / Principal' }} &bull; ID: {{ $p->student_id }}</div>
                        </td>
                        <td>
                            <div>{{ $p->email }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $p->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <strong style="color: #818cf8;">{{ $p->max_exams_allowed }} Exams / Year</strong>
                        </td>
                        <td>
                            <span class="status-pill {{ ($p->status ?? 'ACTIVE') === 'ACTIVE' ? 'active' : 'danger' }}">
                                {{ $p->status ?? 'ACTIVE' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('principals.show', $p->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                View & Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            No Principal / Dean appointed for this organization yet. Click "+ Appoint Dean".
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Teachers in this College -->
<div class="glass-card">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="users" style="color: #6366f1; width: 22px; height: 22px;"></i>
            <span>Enrolled Faculty & Proctors ({{ $organization->teachers->count() }})</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Faculty Name</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Allocated Student Quota</th>
                    <th>Allocated Exam Quota</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organization->teachers as $t)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #fff;">{{ $t->full_name }}</div>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $t->student_id }}</div>
                        </td>
                        <td>{{ $t->department ?? 'General' }}</td>
                        <td>{{ $t->email }}</td>
                        <td>{{ $t->max_students_allowed }} Students</td>
                        <td>{{ $t->max_exams_allowed }} Exams</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            No faculty created under this institution yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
