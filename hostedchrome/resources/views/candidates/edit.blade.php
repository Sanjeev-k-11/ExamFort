@extends('layouts.admin')

@section('title', 'Edit Student: ' . $user->full_name)
@section('breadcrumb', 'Edit Student')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('candidates.show', $user->id) }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Student Profile</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Edit Student Profile & Credentials</h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
            Update student academic records, stream, exam PIN, or reset login password.
        </p>
    </div>

    <div class="glass-card">
        <form action="{{ route('candidates.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Student Roll / Registration ID *</label>
                    <input type="text" name="student_id" class="form-control mono" value="{{ old('student_id', $user->student_id) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Course</label>
                    <input type="text" name="course" class="form-control" value="{{ old('course', $user->course) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Stream / Branch</label>
                    <input type="text" name="stream" class="form-control" value="{{ old('stream', $user->stream) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Batch Years</label>
                    <input type="text" name="batch_years" class="form-control" value="{{ old('batch_years', $user->batch_years) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">College / Institute Name</label>
                <input type="text" name="college_name" class="form-control" value="{{ old('college_name', $user->college_name) }}" required>
            </div>

            @if(session('auth_user_role') === 'ADMIN')
                <div class="form-group">
                    <label class="form-label">Assigned Managing Teacher</label>
                    <select name="created_by_teacher_id" class="form-control">
                        <option value="">None (Global)</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $user->created_by_teacher_id == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }} ({{ $t->college_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Reset Password (Leave blank to keep unchanged)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Exam Access Code / PIN</label>
                    <input type="text" name="access_code" class="form-control" value="{{ old('access_code', $user->access_code) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Bio & Student Profile Summary</label>
                <textarea name="bio" class="form-control">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('candidates.show', $user->id) }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
