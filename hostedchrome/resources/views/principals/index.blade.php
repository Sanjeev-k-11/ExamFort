@extends('layouts.admin')

@section('title', 'Principals & Deans Directory')
@section('breadcrumb', 'Principals & Deans')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Institutional Principals & Deans</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Manage appointed college heads, annual exam limits, and faculty creation authorizations.
            </p>
        </div>
        <a href="{{ route('principals.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);">
            <i data-lucide="crown" style="width: 16px; height: 16px;"></i>
            <span>Appoint Principal / Dean</span>
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('principals.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: var(--text-muted);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search principal name, college, email..." value="{{ request('search') }}">
        </div>

        <select name="org_id" class="form-control" style="width: auto; min-width: 220px;" onchange="this.form.submit()">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" {{ $orgId == $org->id ? 'selected' : '' }}>
                    {{ $org->code }} - {{ Str::limit($org->name, 26) }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="quick-action-btn secondary">Filter</button>
        @if(request('search') || $orgId)
            <a href="{{ route('principals.index') }}" class="quick-action-btn secondary" style="color: #dc2626;">Clear</a>
        @endif
    </form>
</div>

<!-- Principals Table -->
<div class="glass-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Dean / Principal Name & ID</th>
                    <th>College / Organization</th>
                    <th>Annual Exam Quota (Admin Set)</th>
                    <th>Faculty Under Dean</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($principals as $p)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);">
                                    {{ substr($p->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $p->full_name }}</div>
                                    <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $p->student_id }} &bull; {{ $p->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #1e293b;">{{ $p->college_name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $p->designation ?? 'Principal / Dean' }}</div>
                        </td>
                        <td>
                            <strong style="font-size: 15px; color: #4f46e5; font-family: 'Outfit', sans-serif;">
                                {{ $p->max_exams_allowed }} Exams / Year
                            </strong>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #059669;">{{ $p->createdTeachers->count() }} Teachers</span>
                        </td>
                        <td>
                            <span class="status-pill {{ ($p->status ?? 'ACTIVE') === 'ACTIVE' ? 'active' : 'danger' }}">
                                {{ $p->status ?? 'ACTIVE' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('principals.show', $p->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 5px 10px;">
                                    <span>Profile</span>
                                </a>
                                <a href="{{ route('principals.edit', $p->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 5px 10px;">
                                    <span>Edit</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 48px; color: var(--text-muted);">
                            <i data-lucide="crown" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
                            <p style="font-size: 15px; font-weight: 700; color: #0f172a;">No Principals appointed yet</p>
                            <p style="font-size: 13px; margin-top: 4px;">Click "Appoint Principal / Dean" to delegate college administration.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $principals->links() }}
</div>
@endsection
