@extends('layouts.admin')

@section('title', 'Principal Profile: ' . $principal->full_name)
@section('breadcrumb', 'Dean: ' . $principal->full_name)

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('principals.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Principals Directory</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Institutional Dean / Principal Profile</h1>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('principals.edit', $principal->id) }}" class="quick-action-btn secondary">
                <i data-lucide="edit" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                <span>Edit Profile & Quota</span>
            </a>
            <form action="{{ route('principals.destroy', $principal->id) }}" method="POST" onsubmit="return confirm('Remove Principal account {{ $principal->full_name }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="quick-action-btn danger">
                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                    <span>Delete</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Principal Header Card -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 244, 255, 0.85));">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <div style="width: 76px; height: 76px; border-radius: 18px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);">
                {{ substr($principal->full_name, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 24px; font-weight: 900; color: #0f172a;">{{ $principal->full_name }}</h2>
                    <span class="mono" style="font-size: 12px; background: rgba(79, 70, 229, 0.08); color: #4f46e5; padding: 3px 8px; border-radius: 4px; font-weight: 700;">
                        {{ $principal->student_id }}
                    </span>
                    <span class="status-pill {{ ($principal->status ?? 'ACTIVE') === 'ACTIVE' ? 'active' : 'danger' }}">
                        {{ $principal->status ?? 'ACTIVE' }}
                    </span>
                </div>
                <div style="font-size: 14.5px; font-weight: 700; color: #334155; margin-top: 4px;">
                    {{ $principal->college_name }} &bull; {{ $principal->designation }}
                </div>
                <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 4px;">
                    Email: <strong style="color: #1e293b;">{{ $principal->email }}</strong> &bull; Phone: <strong style="color: #1e293b;">{{ $principal->phone ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 24px; background: rgba(248, 250, 252, 0.95); padding: 16px 28px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);">
            <div>
                <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Annual Exam Limit</div>
                <div style="font-size: 22px; font-weight: 800; color: #4f46e5; font-family: 'Outfit', sans-serif; margin-top: 2px;">{{ $principal->max_exams_allowed }} / yr</div>
            </div>
            <div>
                <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Student Capacity</div>
                <div style="font-size: 22px; font-weight: 800; color: #059669; font-family: 'Outfit', sans-serif; margin-top: 2px;">{{ $principal->max_students_allowed }}</div>
            </div>
            <div>
                <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Faculty Supervised</div>
                <div style="font-size: 22px; font-weight: 800; color: #d97706; font-family: 'Outfit', sans-serif; margin-top: 2px;">{{ $principal->createdTeachers->count() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Teachers created by this Principal -->
<div class="glass-card">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="users" style="color: #4f46e5; width: 22px; height: 22px;"></i>
            <span>Faculty Supervised by this Dean ({{ $principal->createdTeachers->count() }})</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Teacher Name</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Allocated Student Quota</th>
                    <th>Allocated Exam Quota</th>
                </tr>
            </thead>
            <tbody>
                @forelse($principal->createdTeachers as $t)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">{{ $t->full_name }}</div>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $t->student_id }}</div>
                        </td>
                        <td style="font-weight: 600; color: #334155;">{{ $t->department ?? 'General' }}</td>
                        <td style="color: #334155;">{{ $t->email }}</td>
                        <td><strong style="color: #059669;">{{ $t->max_students_allowed }}</strong> Students</td>
                        <td><strong style="color: #4f46e5;">{{ $t->max_exams_allowed }}</strong> Exams</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 28px; color: var(--text-muted);">
                            No teachers assigned yet under this Principal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
