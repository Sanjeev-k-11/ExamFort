@extends('layouts.admin')

@section('title', 'Register New Organization')
@section('breadcrumb', 'Register Organization')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('organizations.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Organizations</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Register Partner Organization / College</h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
            Configure institutional profile, address, and allocate annual examination and student quotas.
        </p>
    </div>

    <div class="glass-card">
        <form action="{{ route('organizations.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Organization Unique ID *</label>
                    <input type="text" name="id" class="form-control mono" placeholder="ORG_IITD" value="{{ old('id') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Short Code *</label>
                    <input type="text" name="code" class="form-control mono" placeholder="IITD" value="{{ old('code') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Institution / College Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Indian Institute of Technology Delhi" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Institution Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="University">University</option>
                        <option value="Engineering College" selected>Engineering / Technical College</option>
                        <option value="Examination Board">Examination Board / Authority</option>
                        <option value="Institute of Eminence">Institute of Eminence / National Importance</option>
                        <option value="School / College">School / Junior College</option>
                    </select>
                </div>
            </div>

            <!-- Annual Quota Allocation Section -->
            <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin: 20px 0;">
                <h4 style="font-size: 15px; font-weight: 800; color: #4f46e5; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                    <span>Super Admin Institutional Quota Allocations</span>
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Annual Exam Quota (Per Year) *</label>
                        <input type="number" name="max_exams_allowed" class="form-control" min="1" value="{{ old('max_exams_allowed', 100) }}" required>
                        <small style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Max exams Dean/Principal can conduct/yr.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Student Capacity Cap *</label>
                        <input type="number" name="max_students_allowed" class="form-control" min="1" value="{{ old('max_students_allowed', 5000) }}" required>
                        <small style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Total candidate accounts allowed.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Faculty / Teacher Quota *</label>
                        <input type="number" name="max_teachers_allowed" class="form-control" min="1" value="{{ old('max_teachers_allowed', 50) }}" required>
                        <small style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: block;">Max teachers Principal can create.</small>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Official Email</label>
                    <input type="email" name="email" class="form-control" placeholder="contact@iitd.ac.in" value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Official Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 11 2659 7135" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Official Website URL</label>
                    <input type="url" name="website" class="form-control" placeholder="https://home.iitd.ac.in" value="{{ old('website') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Campus Physical Address</label>
                <textarea name="address" class="form-control" placeholder="Hauz Khas, New Delhi 110016">{{ old('address') }}</textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('organizations.index') }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Register Organization</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
