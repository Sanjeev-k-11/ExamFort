@extends('layouts.admin')

@section('title', 'Edit Organization: ' . $organization->name)
@section('breadcrumb', 'Edit Org')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('organizations.show', $organization->id) }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Organization Overview</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Edit Organization & Quotas</h1>
    </div>

    <div class="glass-card">
        <form action="{{ route('organizations.update', $organization->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Organization ID (Readonly)</label>
                    <input type="text" class="form-control mono" value="{{ $organization->id }}" readonly disabled style="opacity: 0.7;">
                </div>

                <div class="form-group">
                    <label class="form-label">Short Code *</label>
                    <input type="text" name="code" class="form-control mono" value="{{ old('code', $organization->code) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Institution Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $organization->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Institution Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="University" {{ $organization->type === 'University' ? 'selected' : '' }}>University</option>
                        <option value="Engineering College" {{ $organization->type === 'Engineering College' ? 'selected' : '' }}>Engineering / Technical College</option>
                        <option value="Examination Board" {{ $organization->type === 'Examination Board' ? 'selected' : '' }}>Examination Board</option>
                        <option value="Institute of Eminence" {{ $organization->type === 'Institute of Eminence' ? 'selected' : '' }}>Institute of Eminence</option>
                        <option value="School / College" {{ $organization->type === 'School / College' ? 'selected' : '' }}>School / Junior College</option>
                    </select>
                </div>
            </div>

            <!-- Annual Quota Allocation Section -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin: 20px 0;">
                <h4 style="font-size: 15px; font-weight: 700; color: #818cf8; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                    <span>Super Admin Institutional Quota Adjustments</span>
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Annual Exam Quota (Per Year) *</label>
                        <input type="number" name="max_exams_allowed" class="form-control" min="1" value="{{ old('max_exams_allowed', $organization->max_exams_allowed) }}" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Student Capacity Cap *</label>
                        <input type="number" name="max_students_allowed" class="form-control" min="1" value="{{ old('max_students_allowed', $organization->max_students_allowed) }}" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Faculty / Teacher Quota *</label>
                        <input type="number" name="max_teachers_allowed" class="form-control" min="1" value="{{ old('max_teachers_allowed', $organization->max_teachers_allowed) }}" required>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Official Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $organization->email) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Official Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $organization->phone) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="ACTIVE" {{ $organization->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="SUSPENDED" {{ $organization->status === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Campus Address</label>
                <textarea name="address" class="form-control">{{ old('address', $organization->address) }}</textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('organizations.show', $organization->id) }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
