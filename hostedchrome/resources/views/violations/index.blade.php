@extends('layouts.admin')

@section('title', 'Violations & Threat Audit Log')
@section('breadcrumb', 'Violations Audit')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Proctoring Incidents & Threat Audit Log</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
                Comprehensive record of tab switches, audio anomalies, multiple face triggers, and proctor interventions.
            </p>
        </div>
        <a href="{{ route('monitoring.index') }}" class="btn btn-success">
            <i data-lucide="radio" style="width: 16px; height: 16px;"></i>
            <span>Open Live Proctoring Radar</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('violations.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: #94a3b8;"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search candidate, incident details..." value="{{ request('search') }}">
        </div>

        <select name="exam_code" class="form-control" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
            <option value="">All Examinations</option>
            @foreach($exams as $ex)
                <option value="{{ $ex->exam_code }}" {{ $examCode == $ex->exam_code ? 'selected' : '' }}>
                    {{ $ex->exam_code }} - {{ Str::limit($ex->title, 26) }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('search') || $examCode)
            <a href="{{ route('violations.index') }}" class="btn btn-secondary" style="color: #dc2626; border-color: #fecaca;">Clear</a>
        @endif
    </form>
</div>

<!-- Violations Table -->
<div class="glass-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Candidate</th>
                    <th>Exam Code</th>
                    <th>Threat Classification</th>
                    <th>Incident Details</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($violations as $v)
                    <tr>
                        <td style="font-size: 12px; color: #64748b; white-space: nowrap;">
                            {{ \Carbon\Carbon::parse($v->timestamp)->format('d M Y, H:i:s') }}
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">{{ $v->candidate ? $v->candidate->full_name : $v->candidate_id }}</div>
                            <div class="mono" style="font-size: 11px; color: #64748b;">{{ $v->candidate_id }}</div>
                        </td>
                        <td>
                            <span class="mono" style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 2px 8px; border-radius: 6px; border: 1px solid #e0e7ff;">
                                {{ $v->exam_code }}
                            </span>
                        </td>
                        <td>
                            <span class="status-pill danger" style="font-size: 11px;">
                                {{ $v->violation_type }}
                            </span>
                        </td>
                        <td style="font-size: 12.5px; color: #334155; max-width: 320px;">
                            "{{ $v->details }}"
                        </td>
                        <td style="text-align: right;">
                            <form action="{{ route('violations.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Dismiss and delete this violation log entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 11px;">
                                    <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                                    <span>Dismiss</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 48px; color: #64748b;">
                            <i data-lucide="shield-check" style="width: 36px; height: 36px; color: #059669; margin-bottom: 8px;"></i>
                            <p style="font-size: 16px; font-weight: 700; color: #0f172a;">No proctoring violations recorded</p>
                            <p style="font-size: 13px; margin-top: 4px;">Telemetry streams have not detected any anomalies.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $violations->links() }}
</div>
@endsection
