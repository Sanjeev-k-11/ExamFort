@extends('layouts.admin')

@section('title', 'Edit Principal: ' . $principal->full_name)
@section('breadcrumb', 'Edit Principal')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('principals.show', $principal->id) }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Principal Profile</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Edit Principal Credentials & Quota</h1>
    </div>

    <div class="glass-card">
        <form action="{{ route('principals.update', $principal->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Associated Organization / College *</label>
                <select name="org_id" class="form-control" required>
                    @foreach($organizations as $o)
                        <option value="{{ $o->id }}" {{ $principal->org_id == $o->id ? 'selected' : '' }}>
                            {{ $o->name }} (Code: {{ $o->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $principal->full_name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Employee / Dean ID *</label>
                    <input type="text" name="student_id" class="form-control mono" value="{{ old('student_id', $principal->student_id) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $principal->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $principal->phone) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" value="{{ old('designation', $principal->designation) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Department / Office</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $principal->department) }}">
                </div>
            </div>

            <!-- Annual Quota -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin: 20px 0;">
                <h4 style="font-size: 15px; font-weight: 700; color: #818cf8; margin-bottom: 14px;">
                    Annual Exam Quota & Student Limits
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Annual Exam Limit (Yearly) *</label>
                        <input type="number" name="max_exams_allowed" class="form-control" min="1" value="{{ old('max_exams_allowed', $principal->max_exams_allowed) }}" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Student Capacity Limit *</label>
                        <input type="number" name="max_students_allowed" class="form-control" min="1" value="{{ old('max_students_allowed', $principal->max_students_allowed) }}" required>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Reset Password (Optional)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Access PIN</label>
                    <input type="text" name="access_code" class="form-control" value="{{ old('access_code', $principal->access_code) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="ACTIVE" {{ $principal->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="SUSPENDED" {{ $principal->status === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('principals.show', $principal->id) }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
