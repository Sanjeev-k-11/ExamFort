@extends('layouts.admin')

@section('title', 'Register New Student')
@section('breadcrumb', 'Register Student')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('candidates.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Student Roster</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Register New Candidate / Student</h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
            Add student credentials, college stream, and examination access codes.
        </p>
    </div>

    <div class="glass-card">
        <form action="{{ route('candidates.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Rohan Sharma" value="{{ old('full_name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Student Roll / Registration ID *</label>
                    <input type="text" name="student_id" class="form-control" placeholder="STU-2026-CS104" value="{{ old('student_id') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">System Candidate Key (Unique ID) *</label>
                    <input type="text" name="id" class="form-control" placeholder="CAND_{{ time() }}" value="{{ old('id', 'CAND_' . time()) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="rohan.sharma@example.com" value="{{ old('email') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 98765 11223" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Course</label>
                    <input type="text" name="course" class="form-control" placeholder="B.Tech" value="{{ old('course', 'B.Tech') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Stream / Branch</label>
                    <input type="text" name="stream" class="form-control" placeholder="Computer Science & Engineering" value="{{ old('stream', 'Computer Science & Engineering') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">College / University Name</label>
                    <input type="text" name="college_name" class="form-control" value="{{ old('college_name', $currentUser->college_name ?? 'NIT Patna') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Batch Years</label>
                    <input type="text" name="batch_years" class="form-control" placeholder="2022 - 2026" value="{{ old('batch_years', '2022 - 2026') }}">
                </div>
            </div>

            @if(session('auth_user_role') === 'ADMIN')
                <div class="form-group">
                    <label class="form-label">Assign to Managing Teacher / Proctor</label>
                    <select name="created_by_teacher_id" class="form-control">
                        <option value="">None (Global Unassigned)</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">
                                {{ $t->full_name }} ({{ $t->college_name }} - {{ $t->department }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Exam Access Code / PIN</label>
                    <input type="text" name="access_code" class="form-control" placeholder="123456" value="{{ old('access_code', '123456') }}">
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('candidates.index') }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Register Candidate</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
