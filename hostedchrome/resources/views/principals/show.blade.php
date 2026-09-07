@extends('layouts.admin')

@section('title', 'Principal Profile: ' . $principal->full_name)
@section('breadcrumb', 'Dean: ' . $principal->full_name)

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('principals.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Principals Directory</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Institutional Dean / Principal Profile</h1>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('principals.edit', $principal->id) }}" class="quick-action-btn secondary">
                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
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
<div class="glass-card" style="margin-bottom: 28px;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <div style="width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #fbbf24, #d97706); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800;">
                {{ substr($principal->full_name, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="font-size: 22px; font-weight: 800; color: #fff;">{{ $principal->full_name }}</h2>
                    <span class="mono" style="font-size: 12px; background: rgba(99, 102, 241, 0.2); color: #818cf8; padding: 2px 8px; border-radius: 4px;">
                        {{ $principal->student_id }}
                    </span>
                    <span class="status-pill {{ ($principal->status ?? 'ACTIVE') === 'ACTIVE' ? 'active' : 'danger' }}">
                        {{ $principal->status ?? 'ACTIVE' }}
                    </span>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #cbd5e1; margin-top: 4px;">
                    {{ $principal->college_name }} &bull; {{ $principal->designation }}
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Email: {{ $principal->email }} &bull; Phone: {{ $principal->phone ?? 'N/A' }}
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 24px; background: rgba(0,0,0,0.25); padding: 14px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Annual Exam Limit</div>
                <div style="font-size: 20px; font-weight: 800; color: #818cf8; font-family: 'Outfit', sans-serif;">{{ $principal->max_exams_allowed }} / yr</div>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Student Capacity</div>
                <div style="font-size: 20px; font-weight: 800; color: #34d399; font-family: 'Outfit', sans-serif;">{{ $principal->max_students_allowed }}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Faculty Supervised</div>
                <div style="font-size: 20px; font-weight: 800; color: #fbbf24; font-family: 'Outfit', sans-serif;">{{ $principal->createdTeachers->count() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Teachers created by this Principal -->
<div class="glass-card">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="users" style="color: #6366f1; width: 22px; height: 22px;"></i>
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
                            No teachers assigned yet under this Principal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
