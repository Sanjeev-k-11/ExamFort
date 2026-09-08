@extends('layouts.admin')

@section('title', 'Appoint Principal / Dean')
@section('breadcrumb', 'Appoint Principal')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('principals.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Principals Directory</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Appoint Institutional Principal / Dean</h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
            Delegate college leadership, authorize teacher creation, and assign annual examination quotas.
        </p>
    </div>

    <div class="glass-card">
        <form action="{{ route('principals.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Select Partner Organization / College *</label>
                <select name="org_id" class="form-control" required>
                    <option value="">-- Choose Organization --</option>
                    @foreach($organizations as $o)
                        <option value="{{ $o->id }}" {{ ($orgId == $o->id || old('org_id') == $o->id) ? 'selected' : '' }}>
                            {{ $o->name }} (Code: {{ $o->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Principal / Dean Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Dr. P. K. Mishra" value="{{ old('full_name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Employee / Dean ID *</label>
                    <input type="text" name="student_id" class="form-control mono" placeholder="PRIN1001" value="{{ old('student_id') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Official Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="dean.academics@nitp.ac.in" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone / Mobile</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 94310 12345" value="{{ old('phone') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Designation Headline</label>
                    <input type="text" name="designation" class="form-control" placeholder="Dean of Academic Affairs & Principal" value="{{ old('designation', 'Principal & Dean of Academic Affairs') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Department / Office</label>
                    <input type="text" name="department" class="form-control" placeholder="Executive Office" value="{{ old('department', 'Office of the Dean') }}">
                </div>
            </div>

            <!-- Annual Exam Quota Assigned by Super Admin -->
            <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin: 20px 0;">
                <h4 style="font-size: 15px; font-weight: 800; color: #4f46e5; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                    <span>Super Admin Annual Exam Limit & Student Quota</span>
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Annual Exam Quota (Max Exams Allowed Per Year) *</label>
                        <input type="number" name="max_exams_allowed" class="form-control" min="1" value="{{ old('max_exams_allowed', 100) }}" required>
                        <small style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Super Admin controls the annual exam quota.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Student Capacity Limit *</label>
                        <input type="number" name="max_students_allowed" class="form-control" min="1" value="{{ old('max_students_allowed', 5000) }}" required>
                        <small style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Max student capacity for this institution.</small>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Login Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Executive Access PIN</label>
                    <input type="text" name="access_code" class="form-control" placeholder="777777" value="{{ old('access_code', '777777') }}">
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('principals.index') }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Appoint Principal</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
